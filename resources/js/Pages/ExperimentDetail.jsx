import React, { useState } from "react";
import { useParams, useNavigate } from "react-router-dom";
import { useAuth } from "../Contexts/AuthContext";
import { useExperiments } from "../Contexts/ExperimentsContext";
import Modal from "../Components/Modal";
import { ArrowLeft, Lock } from "lucide-react";
import clsx from "clsx";

function ExperimentDetail() {
    const { id } = useParams();
    const navigate = useNavigate();
    const { isAuthenticated } = useAuth();
    const { experiments } = useExperiments();
    const [showRequestModal, setShowRequestModal] = useState(false);
    const [requestMessage, setRequestMessage] = useState("");
    const [requestType, setRequestType] = useState(null);

    // Trouver l'expérience dans le contexte
    const experiment = experiments.find((exp) => exp.id.toString() === id);

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
                // Vous pouvez ajouter une notification de succès ici
            } else {
                throw new Error("Erreur lors de la demande");
            }
        } catch (error) {
            console.error("Erreur:", error);
            // Vous pouvez ajouter une notification d'erreur ici
        }
    };

    return (
        <div className="bg-white px-6 py-12">
            <div className="mx-auto max-w-3xl pb-12">
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
                    <div className="mt-4 flex items-center gap-4">
                        <div className="flex items-center gap-2">
                            <span className="text-sm text-gray-500">Par</span>
                            <span className="text-sm font-medium text-gray-900">
                                {experiment.creator_name}
                            </span>
                        </div>
                        <div className="flex items-center gap-2">
                            <span className="text-sm text-gray-500">
                                {experiment.completed_sessions_count}{" "}
                                {t("experiment.detail.completed_session")}
                            </span>
                        </div>
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

                    <div className="mt-10 space-y-4">
                        {!isAuthenticated && (
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
                        )}

                        <div className="flex gap-4 justify-end">
                            <button
                                onClick={() => handleRequestAccess("results")}
                                disabled={!isAuthenticated}
                                className={clsx(
                                    "rounded-md px-4 py-2 text-sm font-semibold text-white inline-flex items-center",
                                    isAuthenticated
                                        ? "bg-blue-600 hover:bg-blue-500"
                                        : "bg-slate-500 cursor-not-allowed"
                                )}
                            >
                                {!isAuthenticated && (
                                    <Lock className="h-4 w-4 mr-2" />
                                )}
                                {t("experiment.detail.call_to_result_access")}
                            </button>
                            <button
                                onClick={() => handleRequestAccess("access")}
                                disabled={!isAuthenticated}
                                className={clsx(
                                    "rounded-md px-4 py-2 text-sm font-semibold text-white inline-flex items-center",
                                    isAuthenticated
                                        ? "bg-green-600 hover:bg-green-500"
                                        : "bg-slate-500 cursor-not-allowed"
                                )}
                            >
                                {!isAuthenticated && (
                                    <Lock className="h-4 w-4 mr-2" />
                                )}
                                {t(
                                    "experiment.detail.call_to_experiment_access"
                                )}
                            </button>
                        </div>
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
                title={
                    requestType === "results"
                        ? t("experiment.detail.call_to_result_access")
                        : t("experiment.detail.call_to_experiment_access")
                }
                footer={
                    <>
                        <button
                            className="px-4 py-2 text-sm text-gray-600"
                            onClick={() => {
                                setShowRequestModal(false);
                                setRequestMessage("");
                            }}
                        >
                            {t("experiment.detail.cancel")}
                        </button>
                        <button
                            className={clsx(
                                "px-4 py-2 text-sm text-white rounded-md",
                                requestMessage.length >= 10
                                    ? "bg-indigo-600 hover:bg-indigo-500"
                                    : "bg-gray-400 cursor-not-allowed"
                            )}
                            disabled={requestMessage.length < 10}
                            onClick={handleSubmitRequest}
                        >
                            {t("experiment.detail.submit")}
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
                            placeholder={
                                requestType === "results"
                                    ? t("experiment.detail.placeholderResult")
                                    : t(
                                          "experiment.detail.placeholderExperiment"
                                      )
                            }
                        />
                        {requestMessage.length < 10 && (
                            <p className="mt-1 text-sm text-red-500">
                                {t("experiment.detail.requirement")}
                            </p>
                        )}
                    </label>
                </div>
            </Modal>
        </div>
    );
}

export default ExperimentDetail;
