import React, { useState, useEffect } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { useAuth } from "../Contexts/AuthContext";
import { useExperiments } from "../Contexts/ExperimentsContext";
import { useTranslation } from "../Contexts/LanguageContext";
import { ArrowLeft, ChartBarIcon, CheckCircleIcon } from "lucide-react";
import Modal from "../Components/Modal";
import Notification from "../Components/Notification";
import clsx from "clsx";
import ReactMarkdown from "react-markdown";
import rehypeRaw from "rehype-raw";

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

    const experiment = experiments.find((exp) => exp.id.toString() === id);
    const isCreator = user && experiment?.created_by === user.id;

    useEffect(() => {
        const checkExistingAccess = async () => {
            try {
                const response = await fetch(
                    `/api/experiment/access-status/${id}`
                );
                const data = await response.json();
                setExistingAccess(data);
            } catch (error) {
                console.error("Error checking access:", error);
            }
        };

        if (isAuthenticated && (!isCreator || existingAccess)) {
            checkExistingAccess();
        }
    }, [id, isAuthenticated, isCreator]);

    const isButtonDisabled = (type) => {
        if (!isAuthenticated) return true;
        if (user?.isSecondary) return true;
        if (!existingAccess) return false;
        if (existingAccess.hasFullAccess) return true;
        if (type === "results" && existingAccess.hasResultsAccess) return true;
        return false;
    };

    if (!experiment) {
        return (
            <div className="min-h-screen bg-white px-6 py-24">
                <div className="text-center">
                    <p className="text-lg font-semibold text-red-600">
                        {t("experimentDetail.no_experiment")}
                    </p>
                    <button
                        onClick={() => navigate("/experiments")}
                        className="mt-4 inline-flex items-center text-indigo-600 hover:text-indigo-500"
                    >
                        <ArrowLeft className="h-5 w-5 mr-2" />
                        {t("experimentDetail.return_to_list")}
                    </button>
                </div>
            </div>
        );
    }

    const handleRequestAccess = (type) => {
        if (!isAuthenticated) return;
        setRequestType(type);
        setShowRequestModal(true);
    };

    const handleSubmitRequest = async () => {
        if (!requestMessage.trim() || !requestType) return;

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
                    body: JSON.stringify({ message: requestMessage }),
                }
            );

            if (response.ok) {
                setShowRequestModal(false);
                setRequestMessage("");
                setShowNotification(true);
                setTimeout(() => setShowNotification(false), 5000);
            }
        } catch (error) {
            console.error("Error:", error);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div className="bg-white px-6 py-12 mt-32 mb-10">
            <div className="max-w-3xl mx-auto">
                <button
                    onClick={() => navigate("/experiments")}
                    className="mb-8 inline-flex items-center text-indigo-600 hover:text-indigo-500"
                >
                    <ArrowLeft className="h-5 w-5 mr-2" />
                    {t("experimentDetail.return_to_list")}
                </button>

                <div className="space-y-8">
                    {/* En-tête avec le nombre de sessions et les badges d'accès */}
                    <div className="flex items-center justify-between pb-4 border-b">
                        <div className="flex items-center gap-4">
                            <span className="text-sm text-gray-500">
                                {experiment.completed_sessions_count}{" "}
                                {t(
                                    `experimentList.sessions.${
                                        experiment.completed_sessions_count > 1
                                            ? "plural"
                                            : "singular"
                                    }`
                                )}
                            </span>
                            {isAuthenticated &&
                                existingAccess?.hasFullAccess &&
                                !user?.isSecondary && (
                                    <span className="inline-flex items-center rounded-full bg-green-50 px-2 py-1 text-xs font-medium text-green-700">
                                        <CheckCircleIcon className="h-3 w-3 mr-1" />
                                        {t("experimentDetail.full_access")}
                                    </span>
                                )}
                            {isAuthenticated &&
                                existingAccess?.hasResultsAccess &&
                                !existingAccess?.hasFullAccess &&
                                !user?.isSecondary && (
                                    <span className="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700">
                                        <ChartBarIcon className="h-3 w-3 mr-1" />
                                        {t("experimentDetail.results_access")}
                                    </span>
                                )}
                        </div>

                        {/* Type d'expérience */}
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
                            {t(
                                `experimentDetail.types.${experiment.type}.label`
                            )}
                        </span>
                    </div>

                    {/* Description */}
                    <div>
                        <h2 className="text-xl font-semibold text-gray-900 mb-4">
                            {t("experimentDetail.description")}
                        </h2>
                        <ReactMarkdown
                            rehypePlugins={[rehypeRaw]}
                            className="prose"
                        >
                            {experiment.description}
                        </ReactMarkdown>
                    </div>

                    {/* Boutons d'action */}
                    <div className="pt-6 border-t">
                        {!isAuthenticated ? (
                            <div className="rounded-md bg-blue-50 p-4">
                                <p className="text-sm text-blue-700">
                                    {t("experimentDetail.isNotAuthenticated")}
                                    <a
                                        href="/admin/login"
                                        className="ml-1 font-medium underline hover:text-blue-800"
                                    >
                                        {t("experimentDetail.login")}
                                    </a>
                                </p>
                            </div>
                        ) : (
                            !isCreator && (
                                <div className="flex justify-end gap-4">
                                    {user?.isSecondary ? (
                                        <div className="rounded-md bg-red-50 p-4">
                                            <p className="text-sm text-red-700">
                                                {t(
                                                    "experimentDetail.isSecondary"
                                                )}
                                            </p>
                                        </div>
                                    ) : (
                                        <>
                                            <button
                                                onClick={() =>
                                                    handleRequestAccess(
                                                        "results"
                                                    )
                                                }
                                                disabled={isButtonDisabled(
                                                    "results"
                                                )}
                                                className={clsx(
                                                    "rounded-md px-4 py-2 text-sm font-semibold text-white",
                                                    !isButtonDisabled("results")
                                                        ? "bg-blue-600 hover:bg-blue-500"
                                                        : "bg-slate-500 cursor-not-allowed"
                                                )}
                                            >
                                                {existingAccess?.hasResultsAccess ||
                                                existingAccess?.hasFullAccess
                                                    ? t(
                                                          "experimentDetail.you_have_access_results"
                                                      )
                                                    : t(
                                                          "experimentDetail.call_to_result_access"
                                                      )}
                                            </button>

                                            <button
                                                onClick={() =>
                                                    handleRequestAccess(
                                                        "access"
                                                    )
                                                }
                                                disabled={isButtonDisabled(
                                                    "access"
                                                )}
                                                className={clsx(
                                                    "rounded-md px-4 py-2 text-sm font-semibold text-white",
                                                    !isButtonDisabled("access")
                                                        ? "bg-green-600 hover:bg-green-500"
                                                        : "bg-slate-500 cursor-not-allowed"
                                                )}
                                            >
                                                {existingAccess?.hasFullAccess
                                                    ? t(
                                                          "experimentDetail.you_have_access_experiment"
                                                      )
                                                    : t(
                                                          "experimentDetail.call_to_experiment_access"
                                                      )}
                                            </button>

                                            <button
                                                onClick={() =>
                                                    handleRequestAccess(
                                                        "duplicate"
                                                    )
                                                }
                                                disabled={isButtonDisabled(
                                                    "duplicate"
                                                )}
                                                className={clsx(
                                                    "rounded-md px-4 py-2 text-sm font-semibold text-white",
                                                    !isButtonDisabled(
                                                        "duplicate"
                                                    )
                                                        ? "bg-purple-600 hover:bg-purple-500"
                                                        : "bg-slate-500 cursor-not-allowed"
                                                )}
                                            >
                                                {t(
                                                    "experimentDetail.call_to_duplicate"
                                                )}
                                            </button>
                                        </>
                                    )}
                                </div>
                            )
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
                title={(() => {
                    switch (requestType) {
                        case "results":
                            return t("experimentDetail.call_to_result_access");
                        case "duplicate":
                            return t("experimentDetail.call_to_duplicate");
                        default:
                            return t(
                                "experimentDetail.call_to_experiment_access"
                            );
                    }
                })()}
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
                            {t("experimentDetail.cancel")}
                        </button>
                        <button
                            className={clsx(
                                "px-4 py-2 text-sm text-white rounded-md",
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
                                    {t("experimentDetail.send_in_progress")}
                                </>
                            ) : (
                                t("experimentDetail.submit")
                            )}
                        </button>
                    </>
                }
            >
                <div className="space-y-4">
                    <label className="block">
                        <span className="text-gray-700">
                            {t("experimentDetail.message")}
                        </span>
                        <textarea
                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            rows="4"
                            value={requestMessage}
                            onChange={(e) => setRequestMessage(e.target.value)}
                            placeholder={(() => {
                                switch (requestType) {
                                    case "results":
                                        return t(
                                            "experimentDetail.placeholderResult"
                                        );
                                    case "duplicate":
                                        return t(
                                            "experimentDetail.placeholderDuplicate"
                                        );
                                    default:
                                        return t(
                                            "experimentDetail.placeholderExperiment"
                                        );
                                }
                            })()}
                        />
                        {requestMessage.length < 10 && (
                            <p className="mt-1 text-sm text-red-500">
                                {t("experimentDetail.requirement")}
                            </p>
                        )}
                    </label>
                </div>
            </Modal>

            {/* Notification */}
            <Notification
                show={showNotification}
                setShow={setShowNotification}
                message={t("experimentDetail.send_success")}
                description={t("experimentDetail.send_description")}
            />
        </div>
    );
}

export default ExperimentDetail;
