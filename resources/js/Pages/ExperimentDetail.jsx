import React, { useEffect, useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { useAuth } from "../Contexts/AuthContext";
import { useExperiments } from "../Contexts/ExperimentsContext";
import Modal from "../Components/Modal";
import {
    ArrowLeft,
    Lock,
    Copy,
    ChartBarIcon,
    CheckCircleIcon,
} from "lucide-react";
import clsx from "clsx";
import { useTranslation } from "../Contexts/LanguageContext";
import Notification from "../Components/Notification";

function ExperimentDetail() {
    const { id } = useParams();
    const { t } = useTranslation();
    const navigate = useNavigate();
    const { isAuthenticated, user } = useAuth();
    const { experiments } = useExperiments();
    const [showRequestModal, setShowRequestModal] = useState(false);
    const [requestMessage, setRequestMessage] = useState("");
    const [requestType, setRequestType] = useState(null);
    const [existingAccess, setExistingAccess] = useState(null);
    const [isLoading, setIsLoading] = useState(false);
    const [showNotification, setShowNotification] = useState(false);

    // Trouver l'expérience dans le contexte
    const experiment = experiments.find((exp) => exp.id.toString() === id);
    const isCreator = user && experiment.created_by === user.id;

    useEffect(() => {
        const checkExistingAccess = async () => {
            try {
                const response = await fetch(
                    `/api/experiment/access-status/${id}`
                );
                const data = await response.json();
                setExistingAccess(data);
                console.log("Access status:", data);
            } catch (error) {
                console.error("Error checking access:", error);
            }
        };

        // On vérifie si l'utilisateur est authentifié mais n'est pas le créateur
        // OU s'il a déjà des accès
        if (isAuthenticated && (!isCreator || existingAccess)) {
            checkExistingAccess();
        }
    }, [id, isAuthenticated, isCreator]);

    // Fonction pour vérifier si un bouton doit être désactivé
    const isButtonDisabled = (type) => {
        if (!isAuthenticated) return true;
        if (user?.isSecondary) return true;
        if (!existingAccess) return false;

        // Si l'utilisateur a déjà un accès complet, désactiver tous les boutons
        if (existingAccess.hasFullAccess) return true;

        // Si l'utilisateur a déjà accès aux résultats, désactiver seulement le bouton results
        if (type === "results" && existingAccess.hasResultsAccess) return true;

        return false;
    };

    if (!experiment) {
        return (
            <div className="min-h-screen bg-white px-6 py-24">
                <div className="max-w-3xl mx-auto">
                    <div className="text-center">
                        <p className="text-lg font-semibold text-red-600">
                            {t("experiment.detail.no_experiment")}
                        </p>
                        <button
                            onClick={() => navigate("/experiments")}
                            className="mt-4 inline-flex items-center text-indigo-600 hover:text-indigo-500"
                        >
                            <ArrowLeft className="h-5 w-5 mr-2" />
                            {t("experiment.detail.return_to_list")}
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    const handleRequestAccess = (type) => {
        if (!isAuthenticated) {
            return;
        }
        setRequestType(type);
        setShowRequestModal(true);
    };

    const handleSubmitRequest = async () => {
        if (!requestMessage.trim() || !requestType) {
            return;
        }

        try {
            setIsLoading(true);
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

            const response = await fetch(
                `/api/experiment/request-${requestType}/${id}`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    credentials: "include",
                    body: JSON.stringify({
                        message: requestMessage,
                    }),
                }
            );

            if (response.ok) {
                setShowRequestModal(false);
                setRequestMessage("");
                setShowNotification(true);
                // Fermer la notification après 5 secondes
                setTimeout(() => setShowNotification(false), 5000);
            } else {
                throw new Error("Erreur lors de la demande");
            }
        } catch (error) {
            console.error("Erreur:", error);
        } finally {
            setIsLoading(false);
        }
    };

    const getRequestModalTitle = () => {
        switch (requestType) {
            case "results":
                return t("experiment.detail.call_to_result_access");
            case "duplicate":
                return t("experiment.detail.call_to_duplicate");
            default:
                return t("experiment.detail.call_to_experiment_access");
        }
    };

    const getRequestPlaceholder = () => {
        switch (requestType) {
            case "results":
                return t("experiment.detail.placeholderResult");
            case "duplicate":
                return t("experiment.detail.placeholderDuplicate");
            default:
                return t("experiment.detail.placeholderExperiment");
        }
    };

    return (
        <div className="bg-white px-6 py-12">
            <div className="mx-auto max-w-5xl pb-12">
                <button
                    onClick={() => navigate("/experiments")}
                    className="mb-6 inline-flex items-center text-indigo-600 hover:text-indigo-500"
                >
                    <ArrowLeft className="h-5 w-5 mr-2" />
                    {t("experiment.detail.return_to_list")}
                </button>

                <div className="border-b border-gray-200 pb-10">
                    <h1 className="text-4xl font-bold text-gray-900">
                        {experiment.name}
                    </h1>
                    <div className="mt-4 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div className="flex items-center gap-2">
                            <span className="text-sm text-gray-500">
                                {t("experiment.detail.by")}
                            </span>
                            <span className="text-sm font-medium text-gray-900">
                                {experiment.creator_name}
                                {isCreator && (
                                    <span className="ml-2 inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                        {t("experiment.detail.you")}
                                    </span>
                                )}
                            </span>
                        </div>
                        {experiment.original_creator_name && (
                            <div className="flex items-center gap-2">
                                <span className="text-sm text-gray-500">
                                    {t("experiment.detail.original_creator")}
                                </span>
                                <span className="text-sm font-medium text-indigo-600">
                                    {experiment.original_creator_name}
                                </span>
                            </div>
                        )}
                        <div className="flex items-center gap-2">
                            <span className="text-sm text-gray-500">
                                {experiment.completed_sessions_count}{" "}
                                {t("experiment.detail.completed_session")}
                            </span>
                        </div>
                        {isAuthenticated &&
                            existingAccess?.hasFullAccess &&
                            !user?.isSecondary && (
                                <span className="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                    <CheckCircleIcon className="h-3 w-3 mr-1" />
                                    Accès complet
                                </span>
                            )}
                        {isAuthenticated &&
                            existingAccess?.hasResultsAccess &&
                            !existingAccess?.hasFullAccess &&
                            !user?.isSecondary && (
                                <span className="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                    <ChartBarIcon className="h-3 w-3 mr-1" />
                                    Accès aux résultats
                                </span>
                            )}
                    </div>
                </div>

                <div className="mt-8 space-y-8">
                    <section>
                        <h2 className="text-2xl font-bold text-gray-900">
                            {t("experiment.detail.description")}
                        </h2>
                        <p className="mt-4 text-gray-600">
                            {experiment.description}
                        </p>
                    </section>

                    <section>
                        <h2 className="text-2xl font-bold text-gray-900">
                            {t("experiment.detail.type")}
                        </h2>
                        <div className="mt-4">
                            <span
                                className={clsx(
                                    "inline-flex items-center rounded-full px-3 py-1 text-sm font-medium",
                                    {
                                        "bg-blue-100 text-blue-700":
                                            experiment.type === "image",
                                        "bg-green-100 text-green-700":
                                            experiment.type === "sound",
                                        "bg-purple-100 text-purple-700":
                                            experiment.type === "image_sound",
                                    }
                                )}
                            >
                                {experiment.type === "image" &&
                                    t("experiment.detail.type_image")}
                                {experiment.type === "sound" &&
                                    t("experiment.detail.type_sound")}
                                {experiment.type === "image_sound" &&
                                    t("experiment.detail.type_image_sound")}
                            </span>
                        </div>
                    </section>

                    {experiment.instruction && (
                        <section>
                            <h2 className="text-2xl font-bold text-gray-900">
                                {t("experiment.detail.instructions")}
                            </h2>
                            <div
                                className="mt-4 prose prose-blue max-w-none"
                                dangerouslySetInnerHTML={{
                                    __html: experiment.instruction,
                                }}
                            />
                        </section>
                    )}

                    {experiment.media && (
                        <>
                            {/* Section Images */}
                            {experiment.media.some(
                                (media) => !media.match(/\.(mp3|wav)$/)
                            ) && (
                                <section>
                                    <h2 className="text-2xl font-bold text-gray-900">
                                        {t("experiment.detail.images")}
                                    </h2>
                                    <div className="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4">
                                        {experiment.media
                                            .filter(
                                                (media) =>
                                                    !media.match(/\.(mp3|wav)$/)
                                            )
                                            .map((media, index) => (
                                                <div
                                                    key={index}
                                                    className="relative aspect-square group"
                                                >
                                                    <img
                                                        src={media}
                                                        alt={`Media ${
                                                            index + 1
                                                        }`}
                                                        className="rounded-lg object-cover w-full h-full"
                                                        draggable="false"
                                                        style={{
                                                            WebkitUserSelect:
                                                                "none",
                                                            userSelect: "none",
                                                            pointerEvents:
                                                                "none",
                                                        }}
                                                    />
                                                    <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-opacity flex items-end rounded-lg">
                                                        <div className="p-2 w-full text-white opacity-0 group-hover:opacity-100 transition-opacity">
                                                            <p className="text-sm truncate">
                                                                {media
                                                                    .split("/")
                                                                    .pop()}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                    </div>
                                </section>
                            )}

                            {/* Section Sons */}
                            {experiment.media.some((media) =>
                                media.match(/\.(mp3|wav)$/)
                            ) && (
                                <section className="mt-8">
                                    <h2 className="text-2xl font-bold text-gray-900">
                                        {t("experiment.detail.sounds")}
                                    </h2>
                                    <div className="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {experiment.media
                                            .filter((media) =>
                                                media.match(/\.(mp3|wav)$/)
                                            )
                                            .map((media, index) => (
                                                <div
                                                    key={index}
                                                    className="p-4 border rounded-lg bg-gray-50"
                                                >
                                                    <div className="text-sm text-gray-500 mb-2">
                                                        {media.split("/").pop()}
                                                    </div>
                                                    <audio
                                                        controls
                                                        controlsList="nodownload"
                                                        className="w-full"
                                                    >
                                                        <source
                                                            src={media}
                                                            type="audio/mpeg"
                                                        />
                                                    </audio>
                                                </div>
                                            ))}
                                    </div>
                                </section>
                            )}
                        </>
                    )}

                    {experiment.documents &&
                        experiment.documents.length > 0 && (
                            <section>
                                <h2 className="text-2xl font-bold text-gray-900">
                                    {t("experiment.detail.documents")}
                                </h2>
                                <div className="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {experiment.documents.map((doc, index) => (
                                        <a
                                            key={index}
                                            href={doc}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="flex items-center p-4 border rounded-lg hover:bg-gray-50 transition-colors"
                                        >
                                            <span className="flex-1 truncate">
                                                {doc.split("/").pop()}
                                            </span>
                                            <ArrowLeft className="h-4 w-4 transform rotate-[-135deg]" />
                                        </a>
                                    ))}
                                </div>
                            </section>
                        )}

                    <div className="mt-10 space-y-4">
                        {!isAuthenticated ? (
                            <div className="rounded-md bg-red-50 p-4">
                                <p className="text-sm text-red-700">
                                    {t("experiment.detail.isNotAuthenticated")}
                                    <a
                                        href="/admin/login"
                                        className="ml-1 font-medium underline hover:text-red-600"
                                    >
                                        {t("experiment.detail.login")}
                                    </a>
                                </p>
                            </div>
                        ) : isCreator ? (
                            <></>
                        ) : (
                            <div className="flex gap-4 justify-end">
                                {user?.isSecondary ? (
                                    <div className="rounded-md bg-red-50 p-4">
                                        <p className="text-sm text-red-700">
                                            <a
                                                href="/admin/login"
                                                className="ml-1 font-medium underline hover:text-red-600"
                                            >
                                                Les comptes secondaires ne
                                                peuvent pas faire de demandes
                                                d'accès
                                            </a>
                                        </p>
                                    </div>
                                ) : (
                                    <>
                                        <button
                                            onClick={() =>
                                                handleRequestAccess("results")
                                            }
                                            disabled={isButtonDisabled(
                                                "results"
                                            )}
                                            className={clsx(
                                                "rounded-md px-4 py-2 text-sm font-semibold text-white inline-flex items-center",
                                                !isButtonDisabled("results")
                                                    ? "bg-blue-600 hover:bg-blue-500"
                                                    : "bg-slate-500 cursor-not-allowed"
                                            )}
                                        >
                                            {existingAccess?.hasResultsAccess ||
                                            existingAccess?.hasFullAccess
                                                ? "Vous avez déjà accès aux résultats"
                                                : t(
                                                      "experiment.detail.call_to_result_access"
                                                  )}
                                        </button>

                                        <button
                                            onClick={() =>
                                                handleRequestAccess("access")
                                            }
                                            disabled={isButtonDisabled(
                                                "access"
                                            )}
                                            className={clsx(
                                                "rounded-md px-4 py-2 text-sm font-semibold text-white inline-flex items-center",
                                                !isButtonDisabled("access")
                                                    ? "bg-green-600 hover:bg-green-500"
                                                    : "bg-slate-500 cursor-not-allowed"
                                            )}
                                        >
                                            {existingAccess?.hasFullAccess
                                                ? "Vous avez déjà un accès complet"
                                                : t(
                                                      "experiment.detail.call_to_experiment_access"
                                                  )}
                                        </button>

                                        <button
                                            onClick={() =>
                                                handleRequestAccess("duplicate")
                                            }
                                            disabled={isButtonDisabled(
                                                "duplicate"
                                            )}
                                            className={clsx(
                                                "rounded-md px-4 py-2 text-sm font-semibold text-white inline-flex items-center",
                                                isAuthenticated
                                                    ? "bg-purple-600 hover:bg-purple-500"
                                                    : "bg-slate-500 cursor-not-allowed"
                                            )}
                                        >
                                            {t(
                                                "experiment.detail.call_to_duplicate"
                                            )}
                                        </button>
                                    </>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Modal de demande */}
            <Modal
                isOpen={showRequestModal}
                onClose={() => {
                    setShowRequestModal(false);
                    setRequestMessage("");
                }}
                title={getRequestModalTitle()}
                footer={
                    <>
                        <button
                            className="px-4 py-2 text-sm text-gray-600"
                            onClick={() => {
                                setShowRequestModal(false);
                                setRequestMessage("");
                            }}
                            disabled={isLoading}
                        >
                            {t("experiment.detail.cancel")}
                        </button>
                        <button
                            className={clsx(
                                "px-4 py-2 text-sm text-white rounded-md inline-flex items-center",
                                requestMessage.length >= 10 && !isLoading
                                    ? "bg-indigo-600 hover:bg-indigo-500"
                                    : "bg-gray-400 cursor-not-allowed"
                            )}
                            disabled={requestMessage.length < 10 || isLoading}
                            onClick={handleSubmitRequest}
                        >
                            {isLoading ? (
                                <>
                                    <svg
                                        className="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <circle
                                            className="opacity-25"
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            strokeWidth="4"
                                        ></circle>
                                        <path
                                            className="opacity-75"
                                            fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                        ></path>
                                    </svg>
                                    Envoi en cours...
                                </>
                            ) : (
                                t("experiment.detail.submit")
                            )}
                        </button>
                    </>
                }
            >
                <div className="space-y-4">
                    <label className="block">
                        <span className="text-gray-700">
                            {t("experiment.detail.message")}
                        </span>
                        <textarea
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="4"
                            value={requestMessage}
                            onChange={(e) => setRequestMessage(e.target.value)}
                            placeholder={getRequestPlaceholder()}
                        />
                        {requestMessage.length < 10 && (
                            <p className="mt-1 text-sm text-red-500">
                                {t("experiment.detail.requirement")}
                            </p>
                        )}
                    </label>
                </div>
            </Modal>
            {/* Notification */}
            <Notification
                show={showNotification}
                setShow={setShowNotification}
                message="Demande envoyée avec succès"
                description="Vous recevrez une réponse par email"
            />
        </div>
    );
}

export default ExperimentDetail;
