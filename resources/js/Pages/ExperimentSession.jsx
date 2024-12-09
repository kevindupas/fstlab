// ExperimentSession.jsx
import React, { useCallback, useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import KonvaComponent from "../Components/KonvaComponent";
import SidePanelResults from "../Components/SidePanelResults";
import Toolbar from "../Components/Toolbar";
import DeviceOrientationCheck from "../Utils/DeviceOrientationCheck";
import { useTranslation } from "../Contexts/LanguageContext";

function ExperimentSession() {
    const { t } = useTranslation();
    const { sessionId } = useParams();
    const navigate = useNavigate();

    // États de base
    const [experiment, setExperiment] = useState(null);
    const [error, setError] = useState("");
    const [media, setMedia] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [showLeaveModal, setShowLeaveModal] = useState(false);
    const [startTime, setStartTime] = useState(Date.now());
    const [elapsedTime, setElapsedTime] = useState(0);
    const [actionsLog, setActionsLog] = useState([]);
    const [currentMediaItems, setCurrentMediaItems] = useState([]);
    const [editingGroupIndex, setEditingGroupIndex] = useState(null);

    // États pour les résultats
    const [isFinished, setIsFinished] = useState(false);
    const [groups, setGroups] = useState([]);

    const handleGroupsChange = (updatedGroups) => {
        setGroups(updatedGroups);
    };

    const handleEditModeChange = (groupIndex) => {
        setEditingGroupIndex(groupIndex);
    };

    // Vérification initiale et chargement
    useEffect(() => {
        const checkSession = async () => {
            const existingSession = localStorage.getItem("session");
            const isRegistered =
                localStorage.getItem("isRegistered") === "true";

            if (!existingSession || !isRegistered) {
                navigate(`/login/${sessionId}`);
                return;
            }

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
            } catch (error) {
                console.error("Error:", error);
                setError(t("experimentSession.session.error.generic"));
            } finally {
                setIsLoading(false);
            }
        };

        checkSession();
    }, [sessionId, navigate]);

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
                    console.log("Début du nettoyage");

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
                    console.log(
                        "Après nettoyage, contenu localStorage:",
                        localStorage
                    );

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
        const threshold = 200;
        const clustersMap = [];

        const getDistance = (pos1, pos2) => {
            const dx = pos1.x - pos2.x;
            const dy = pos1.y - pos2.y;
            return Math.sqrt(dx * dx + dy * dy);
        };

        currentMediaItems.forEach((item) => {
            let foundCluster = false;
            for (let cluster of clustersMap) {
                if (cluster.some((g) => getDistance(g, item) < threshold)) {
                    cluster.push(item);
                    foundCluster = true;
                    break;
                }
            }
            if (!foundCluster) {
                clustersMap.push([item]);
            }
        });

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
            name: `Groupe ${index + 1}`,
            color: clusterColors[index % clusterColors.length],
            elements: cluster,
        }));

        setGroups(preparedGroups);
        setIsFinished(true);
    }, [currentMediaItems, startTime]);

    const updateActionsLog = useCallback((newAction) => {
        setActionsLog((prevLog) => [...prevLog, newAction]);
    }, []);

    const updateMediaItems = useCallback((items) => {
        setCurrentMediaItems(items);
    }, []);

    const handleSubmitResults = async (data) => {
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
                    }),
                }
            );

            if (response.ok) {
                localStorage.removeItem("isRegistered");
                localStorage.removeItem("participantNumber");
                localStorage.removeItem("session");
                navigate("/");
            }
        } catch (error) {
            console.error("Error sending results:", error);
        }
    };

    const handleMediaGroupChange = (mediaId, newGroupIndex) => {
        setGroups((prevGroups) => {
            // Crée une copie des groupes existants
            const newGroups = prevGroups.map((group) => ({
                ...group,
                elements: [...group.elements], // Copie profonde des éléments
            }));

            // Trouve le média dans son groupe actuel
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

            // Si on a trouvé le média, on le déplace
            if (mediaToMove && oldGroupIndex !== -1) {
                // Retire le média de son ancien groupe
                newGroups[oldGroupIndex].elements = newGroups[
                    oldGroupIndex
                ].elements.filter((elem) => elem.id !== mediaId);

                // Ajoute le média au nouveau groupe
                newGroups[newGroupIndex].elements.push(mediaToMove);
            } else {
                // Si le média n'était dans aucun groupe, on le cherche dans les médias courants
                const mediaFromCurrent = currentMediaItems.find(
                    (item) => item.id === mediaId
                );
                if (mediaFromCurrent && newGroups[newGroupIndex]) {
                    newGroups[newGroupIndex].elements.push({
                        id: mediaFromCurrent.id,
                        url: mediaFromCurrent.url,
                        type: mediaFromCurrent.type,
                    });
                }
            }

            return newGroups;
        });
    };

    return (
        <DeviceOrientationCheck>
            {isLoading ? (
                <div className="flex items-center justify-center min-h-screen">
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
                <div className="flex h-screen flex-col">
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
                                onAction={updateActionsLog}
                                onMediaItemsChange={updateMediaItems}
                                isFinished={isFinished}
                                groups={groups}
                                editingGroupIndex={editingGroupIndex}
                                onMediaGroupChange={handleMediaGroupChange}
                            />
                        </div>

                        <div className="shrink-0 border-t border-gray-200 lg:w-[450px] lg:border-l lg:border-t-0">
                            <SidePanelResults
                                isOpen={isFinished}
                                groups={groups}
                                onGroupsChange={handleGroupsChange}
                                onEditModeChange={handleEditModeChange}
                                onSubmit={handleSubmitResults}
                                elapsedTime={elapsedTime}
                                actionsLog={actionsLog}
                                sessionId={sessionId}
                            />
                        </div>
                    </div>

                    <Toolbar
                        onRestart={handleRestart}
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
                                <p className="text-gray-600 mb-6">
                                    Vous pouvez soit sauvegarder votre session
                                    pour continuer plus tard, soit la supprimer
                                    définitivement.
                                </p>
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
                                        Abandonner
                                    </button>
                                    <button
                                        onClick={() => setShowLeaveModal(false)}
                                        className="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition-colors"
                                    >
                                        Annuler
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
