// ExperimentSession.jsx
import React, { useCallback, useEffect, useState } from "react";
import { useNavigate, useParams, useLocation } from "react-router-dom";
import KonvaComponent from "../Components/KonvaComponent";
import SidePanelResults from "../Components/SidePanelResults";
import Toolbar from "../Components/Toolbar";
import DeviceOrientationCheck from "../Utils/DeviceOrientationCheck";
import { useTranslation } from "../Contexts/LanguageContext";
import { useExperimentStatus } from "../Contexts/ExperimentStatusContext.jsx";
import { TestModeModal } from "../Components/TestModeModal.jsx";
import { getSystemInfo } from "../Utils/getSystemInfo.js";
import clsx from "clsx";

function ExperimentSession() {
    const { t } = useTranslation();
    const { sessionId } = useParams();
    const navigate = useNavigate();
    const { checkExperimentStatus } = useExperimentStatus();
    const [showTestModeModal, setShowTestModeModal] = useState(true);
    const location = useLocation();
    const isTestMode = location.state?.isTest;
    const [isTablet, setIsTablet] = useState(false);
    const systemData = getSystemInfo();

    // États de base
    const [experiment, setExperiment] = useState(null);
    const [error, setError] = useState("");
    const [media, setMedia] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [showLeaveModal, setShowLeaveModal] = useState(false);
    const [startTime, setStartTime] = useState(Date.now());
    const [elapsedTime, setElapsedTime] = useState(0);
    const [actionsLog, setActionsLog] = useState([]);
    const [mediaInteractions, setMediaInteractions] = useState({});
    const [currentMediaItems, setCurrentMediaItems] = useState([]);
    const [editingGroupIndex, setEditingGroupIndex] = useState(null);
    const [canvasSize, setCanvasSize] = useState(null);

    // États pour les résultats
    const [isFinished, setIsFinished] = useState(false);
    const [groups, setGroups] = useState([]);

    useEffect(() => {
        if (systemData.device_type === "tablet") {
            setIsTablet(true);
            console.log(isTablet);
        }
    }, []);

    const handleGroupsChange = (updatedGroups) => {
        // Log l'ajout ou la suppression de groupe
        const groupDiff = updatedGroups.length - groups.length;

        if (groupDiff > 0) {
            // Un groupe a été ajouté
            updateActionsLog({
                type: "group_created",
                group_name: updatedGroups[updatedGroups.length - 1].name,
                group_color: updatedGroups[updatedGroups.length - 1].color,
                time: Date.now(),
            });
        } else if (groupDiff < 0) {
            // Un groupe a été supprimé
            updateActionsLog({
                type: "group_deleted",
                time: Date.now(),
            });
        }

        setGroups(updatedGroups);
    };

    const handleEditModeChange = (groupIndex) => {
        setEditingGroupIndex(groupIndex);
    };

    const handleInteractionsUpdate = (interactions) => {
        setMediaInteractions(interactions);
    };

    const handleCanvasSizeChange = useCallback((newSize) => {
        setCanvasSize(newSize);
    }, []);

    // Vérification initiale et chargement
    useEffect(() => {
        const checkSession = async () => {
            if (isTestMode) {
                // En mode test, on utilise directement les données passées dans location.state
                setExperiment({ experiment: location.state.experiment });
                setMedia(location.state.media);
                setIsLoading(false);
                return;
            }
            // Vérifie d'abord que l'utilisateur est connecté
            const existingSession = localStorage.getItem("session");
            const isRegistered =
                localStorage.getItem("isRegistered") === "true";

            if (!existingSession || !isRegistered) {
                navigate(`/login/${sessionId}`);
                return;
            }

            try {
                // Vérifie le statut de l'expérience
                const isExperimentAvailable = await checkExperimentStatus(
                    sessionId
                );

                if (!isExperimentAvailable) {
                    // Si l'expérience n'est pas disponible, le modal sera géré par le contexte
                    // et la redirection vers la home page sera automatique
                    return;
                }

                // Si l'expérience est disponible, charge les données
                const response = await fetch(
                    `/api/experiment/session/${sessionId}`
                );
                const data = await response.json();

                if (!data.experiment) {
                    setError(t("experimentSession.session.error.notFound"));
                    return;
                }

                // Si tout est ok, met à jour les états
                setExperiment(data);
                setMedia(data.media);
            } catch (error) {
                console.error("Error:", error);
                setError(t("experimentSession.session.error.generic"));
            } finally {
                setIsLoading(false);
            }
        };

        checkSession();
    }, [sessionId, navigate, checkExperimentStatus, t]);

    useEffect(() => {
        setStartTime(Date.now());
    }, []);

    const handleExit = useCallback(
        async (keepSession = false) => {
            if (!keepSession) {
                try {
                    const storedSession = JSON.parse(
                        localStorage.getItem("session")
                    );

                    if (storedSession?.id) {
                        await fetch(
                            `/api/experiment/session/${storedSession.id}`,
                            {
                                method: "DELETE",
                                headers: {
                                    "Content-Type": "application/json",
                                    Accept: "application/json",
                                    "X-Requested-With": "XMLHttpRequest",
                                },
                            }
                        );
                    }

                    localStorage.clear();

                    navigate("/");
                } catch (error) {
                    console.error("Error during cleanup:", error);
                    // Même en cas d'erreur, on force le nettoyage
                    localStorage.clear();
                    navigate("/");
                }
            } else {
                navigate("/");
            }
        },
        [navigate]
    );

    const handleRestart = async () => {
        setIsLoading(true);
        try {
            const response = await fetch(
                `/api/experiment/session/${sessionId}`
            );
            const data = await response.json();

            if (!data.experiment) {
                setError(t("experimentSession.session.error.notFound"));
                return;
            }

            setExperiment(data);
            setMedia(data.media);
            setIsFinished(false);
            setGroups([]);
            setActionsLog([]);
            setElapsedTime(Date.now());
        } catch (error) {
            console.error("Error:", error);
            setError(t("experimentSession.session.error.reload"));
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        if (isFinished) return;

        const timer = setInterval(() => {
            const msElapsed = Date.now() - startTime;
            setElapsedTime(msElapsed);
        }, 1000);

        return () => clearInterval(timer);
    }, [startTime, isFinished]);

    const handleTerminate = useCallback(() => {
        // Fonction utilitaire pour calculer la distance entre deux points
        const getDistance = (pos1, pos2) => {
            const dx = pos1.x - pos2.x;
            const dy = pos1.y - pos2.y;
            return Math.sqrt(dx * dx + dy * dy);
        };

        // Calcule la distance moyenne entre les éléments les plus proches
        let totalMinDistance = 0;
        let minDistanceCount = 0;

        currentMediaItems.forEach((item1, i) => {
            const distances = currentMediaItems
                .slice(i + 1)
                .map((item2) => getDistance(item1, item2))
                .filter((d) => d > 0);

            if (distances.length > 0) {
                totalMinDistance += Math.min(...distances);
                minDistanceCount++;
            }
        });

        // Le threshold est basé sur la distance moyenne entre les plus proches voisins
        const averageMinDistance = totalMinDistance / minDistanceCount;
        const threshold = averageMinDistance * 1.5; // Facteur ajustable

        console.log("Average min distance:", averageMinDistance);
        console.log("Threshold:", threshold);

        const clustersMap = [];

        currentMediaItems.forEach((item) => {
            let addedToCluster = false;

            // Vérifie d'abord les clusters existants
            for (let cluster of clustersMap) {
                // Si l'item est proche d'au moins un élément du cluster
                if (
                    cluster.some(
                        (element) => getDistance(element, item) < threshold
                    )
                ) {
                    cluster.push({
                        ...item,
                        interactions: mediaInteractions[item.url] || 0,
                    });
                    addedToCluster = true;
                    break;
                }
            }

            // Si l'item n'a été ajouté à aucun cluster existant
            if (!addedToCluster) {
                clustersMap.push([
                    {
                        ...item,
                        interactions: mediaInteractions[item.url] || 0,
                    },
                ]);
            }
        });

        // Fusion des clusters qui ont des éléments proches
        let mergeOccurred;
        do {
            mergeOccurred = false;
            for (let i = 0; i < clustersMap.length; i++) {
                for (let j = i + 1; j < clustersMap.length; j++) {
                    // Vérifie si deux clusters ont des éléments proches
                    if (
                        clustersMap[i].some((item1) =>
                            clustersMap[j].some(
                                (item2) => getDistance(item1, item2) < threshold
                            )
                        )
                    ) {
                        // Fusionne les clusters
                        clustersMap[i].push(...clustersMap[j]);
                        clustersMap.splice(j, 1);
                        mergeOccurred = true;
                        break;
                    }
                }
                if (mergeOccurred) break;
            }
        } while (mergeOccurred);

        const clusterColors = [
            "#FF0000",
            "#00FF00",
            "#0000FF",
            "#FFFF00",
            "#FF00FF",
            "#00FFFF",
            "#FFA500",
        ];

        const preparedGroups = clustersMap.map((cluster, index) => ({
            name: `C${index + 1}`,
            color: clusterColors[index % clusterColors.length],
            elements: cluster,
        }));

        setGroups(preparedGroups);
        setIsFinished(true);
    }, [currentMediaItems, mediaInteractions]);

    const updateActionsLog = useCallback((newAction) => {
        setActionsLog((prevLog) => [...prevLog, newAction]);
    }, []);

    const updateMediaItems = useCallback((items) => {
        setCurrentMediaItems(items);
    }, []);

    const handleSubmitResults = async (data) => {
        if (isTestMode) {
            navigate("/thank-you", { state: { isTest: true } });
            return;
        }

        try {
            const sessionData = JSON.parse(localStorage.getItem("session"));
            const response = await fetch(
                `/api/experiment/save/${sessionData?.id}`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: JSON.stringify({
                        group_data: data.groups,
                        actions_log: actionsLog,
                        duration: data.elapsedTime,
                        feedback: data.feedback,
                        errors_log: data.errors || [],
                        canvas_size: canvasSize,
                    }),
                }
            );

            if (response.ok) {
                localStorage.removeItem("isRegistered");
                localStorage.removeItem("participantNumber");
                localStorage.removeItem("session");
                navigate("/thank-you");
            }
        } catch (error) {
            console.error("Error sending results:", error);
        }
    };

    const handleMediaGroupChange = (mediaId, newGroupIndex) => {
        let oldGroupName = null;
        groups.forEach((group, index) => {
            if (group.elements.some((elem) => elem.id === mediaId)) {
                oldGroupName = group.name;
            }
        });

        setGroups((prevGroups) => {
            const newGroups = prevGroups.map((group) => ({
                ...group,
                elements: [...group.elements],
            }));

            // Trouve le média et le déplace
            let mediaToMove = null;
            let oldGroupIndex = -1;

            newGroups.forEach((group, index) => {
                const foundMedia = group.elements.find(
                    (elem) => elem.id === mediaId
                );
                if (foundMedia) {
                    mediaToMove = foundMedia;
                    oldGroupIndex = index;
                }
            });

            if (mediaToMove && oldGroupIndex !== -1) {
                // Retire le média de son ancien groupe
                newGroups[oldGroupIndex].elements = newGroups[
                    oldGroupIndex
                ].elements.filter((elem) => elem.id !== mediaId);

                // Ajoute le média au nouveau groupe
                newGroups[newGroupIndex].elements.push(mediaToMove);

                // Log le déplacement
                updateActionsLog({
                    type: "item_moved_between_groups",
                    item_id: mediaId,
                    from_group: oldGroupName,
                    to_group: newGroups[newGroupIndex].name,
                    time: Date.now(),
                });
            } else {
                const mediaFromCurrent = currentMediaItems.find(
                    (item) => item.id === mediaId
                );
                if (mediaFromCurrent && newGroups[newGroupIndex]) {
                    newGroups[newGroupIndex].elements.push({
                        id: mediaFromCurrent.id,
                        url: mediaFromCurrent.url,
                        type: mediaFromCurrent.type,
                    });

                    // Log l'ajout initial à un groupe
                    updateActionsLog({
                        type: "item_added_to_group",
                        item_id: mediaId,
                        group_name: newGroups[newGroupIndex].name,
                        time: Date.now(),
                    });
                }
            }

            return newGroups;
        });
    };

    return (
        <DeviceOrientationCheck>
            {isTestMode && (
                <>
                    <TestModeModal
                        isOpen={showTestModeModal}
                        onClose={() => setShowTestModeModal(false)}
                    />
                </>
            )}
            {isLoading ? (
                <div className="flex items-center justify-center min-h-screen scroll-container">
                    <div className="text-center">
                        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto" />
                        <p className="mt-4 text-gray-700">
                            {t("experimentSession.session.loading")}
                        </p>
                    </div>
                </div>
            ) : error ? (
                <div className="flex items-center justify-center min-h-screen">
                    <div className="text-center">
                        <p className="text-red-500 font-semibold text-lg">
                            {error}
                        </p>
                        <button
                            onClick={() => navigate("/")}
                            className="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
                        >
                            {t("experimentSession.session.return_to_home")}
                        </button>
                    </div>
                </div>
            ) : (
                <div className="flex h-screen w-screen flex-col overflow-hidden">
                    <div className="mx-auto w-full grow lg:flex">
                        <div className="flex-1 xl:flex">
                            <KonvaComponent
                                media={media}
                                buttonColor={
                                    experiment?.experiment?.button_color ||
                                    "#3B82F6"
                                }
                                size={
                                    experiment?.experiment?.button_size || 100
                                }
                                checkIsTablet={isTablet}
                                onAction={updateActionsLog}
                                onCanvasSizeChange={handleCanvasSizeChange}
                                onMediaItemsChange={updateMediaItems}
                                onInteractionsUpdate={handleInteractionsUpdate}
                                isFinished={isFinished}
                                groups={groups}
                                editingGroupIndex={editingGroupIndex}
                                onMediaGroupChange={handleMediaGroupChange}
                            />
                        </div>

                        <div
                            className={clsx(
                                "shrink-0 border-t border-gray-200 lg:border-l lg:border-t-0",
                                isTablet
                                    ? "w-full lg:w-[350px]"
                                    : "w-full lg:w-[450px]"
                            )}
                        >
                            <SidePanelResults
                                isOpen={isFinished}
                                groups={groups}
                                onGroupsChange={handleGroupsChange}
                                onEditModeChange={handleEditModeChange}
                                onSubmit={handleSubmitResults}
                                elapsedTime={elapsedTime}
                                instruction={
                                    experiment?.experiment?.instruction
                                }
                                actionsLog={actionsLog}
                                sessionId={sessionId}
                            />
                        </div>
                    </div>

                    <Toolbar
                        onRestart={handleRestart}
                        isTestMode={isTestMode}
                        checkIsTablet={isTablet}
                        onLeave={() => setShowLeaveModal(true)}
                        onTerminate={handleTerminate}
                        isFinished={isFinished}
                    />
                    {/* Modal de confirmation de sortie */}
                    {showLeaveModal && (
                        <div className="fixed inset-0 bg-black bg-opacity-50 z-[9999] flex items-center justify-center">
                            <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                                <h2 className="text-xl font-bold text-gray-900 mb-4">
                                    {t(
                                        "experimentSession.session.quit_session"
                                    )}
                                </h2>
                                {isTestMode ? (
                                    <p className="text-gray-600 mb-6">
                                        {t(
                                            "experimentSession.session.quit_session_message_test"
                                        )}
                                    </p>
                                ) : (
                                    <p className="text-gray-600 mb-6">
                                        {t(
                                            "experimentSession.session.quit_session_message"
                                        )}
                                    </p>
                                )}
                                <div className="flex flex-col gap-3">
                                    <button
                                        onClick={async () => {
                                            // S'assurer que handleExit est bien exécuté avant la fermeture du modal
                                            await handleExit(false);
                                            setShowLeaveModal(false);
                                        }}
                                        className="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 transition-colors flex items-center justify-center gap-2"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            className="h-5 w-5"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V6h5a2 2 0 012 2v7a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h5v5.586l-1.293-1.293zM9 4a1 1 0 012 0v2H9V4z" />
                                        </svg>
                                        {t("experimentSession.session.give_up")}
                                    </button>
                                    <button
                                        onClick={() => setShowLeaveModal(false)}
                                        className="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition-colors"
                                    >
                                        {t("experimentSession.session.cancel")}
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            )}
        </DeviceOrientationCheck>
    );
}

export default ExperimentSession;
