import {
    CheckCircleIcon,
    LockClosedIcon,
    UserCircleIcon,
} from "@heroicons/react/24/outline";
import React, { useState } from "react";
import Header from "../Components/Header";
import Modal from "../Components/Modal";
import { useAuth } from "../Contexts/AuthContext";
import { useExperiments } from "../Contexts/ExperimentsContext";

function ExperimentList() {
    const { user, isAuthenticated } = useAuth();
    const { experiments, isLoading, error } = useExperiments();

    const [showLoginModal, setShowLoginModal] = useState(false);
    const [showRequestModal, setShowRequestModal] = useState(false);
    const [currentRequest, setCurrentRequest] = useState(null);
    const [requestMessage, setRequestMessage] = useState("");
    const [requestStatus, setRequestStatus] = useState({
        show: false,
        message: "",
        error: false,
    });

    const handleAccessRequest = async (experimentId, type) => {
        if (!isAuthenticated) {
            setShowLoginModal(true);
            return;
        }

        setCurrentRequest({ experimentId, type });
        setShowRequestModal(true);
    };

    const submitRequest = async () => {
        if (!currentRequest || !requestMessage.trim()) {
            return;
        }

        try {
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

            const response = await fetch(
                `/api/experiment/request-${currentRequest.type}/${currentRequest.experimentId}`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                    },
                    credentials: "include", // Important pour les cookies de session
                    body: JSON.stringify({
                        message: requestMessage,
                        // Si vous avez d'autres champs à envoyer, ajoutez-les ici
                    }),
                }
            );

            // Loggez la réponse pour debug
            const data = await response.json();
            console.log("Réponse du serveur:", data);

            if (response.ok) {
                showNotification(
                    `Votre demande d'accès ${
                        currentRequest.type === "results"
                            ? "aux résultats"
                            : "à l'expérimentation"
                    } a été envoyée avec succès.`,
                    false
                );
                closeModals();
            } else {
                throw new Error(data.message || "Erreur lors de la demande");
            }
        } catch (error) {
            console.error("Erreur détaillée:", error);
            showNotification(
                error.message ||
                    "Une erreur s'est produite lors de l'envoi de la demande.",
                true
            );
        }
    };

    const showNotification = (message, isError) => {
        setRequestStatus({
            show: true,
            message,
            error: isError,
        });

        setTimeout(() => {
            setRequestStatus({ show: false, message: "", error: false });
        }, 3000);
    };

    const closeModals = () => {
        setShowLoginModal(false);
        setShowRequestModal(false);
        setRequestMessage("");
        setCurrentRequest(null);
    };

    if (isLoading) {
        return (
            <div className="min-h-screen bg-white px-6 py-24 sm:py-32 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <p className="text-lg font-semibold text-indigo-600">
                        Chargement...
                    </p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="min-h-screen bg-white px-6 py-24 sm:py-32 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <p className="text-lg font-semibold text-red-600">
                        {error}
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="bg-white">
            <Header />
            <div className="relative isolate pt-14">
                <div className="py-24 sm:py-32">
                    <div className="mx-auto max-w-7xl px-6 lg:px-8">
                        <div className="mx-auto max-w-2xl text-center">
                            <h2 className="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                                Liste des Expérimentations
                            </h2>
                            <p className="mt-2 text-lg leading-8 text-gray-600">
                                Découvrez toutes les expériences disponibles sur
                                notre plateforme
                            </p>
                        </div>

                        <div className="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-12 lg:mx-0 lg:max-w-none lg:grid-cols-2">
                            {experiments.map((experiment) => (
                                <article
                                    key={experiment.name}
                                    className="flex flex-col items-start rounded-2xl bg-white p-6 shadow-lg ring-1 ring-gray-200 hover:ring-gray-300 transition-all duration-200"
                                >
                                    <div className="flex items-center gap-x-4 text-xs w-full">
                                        <div className="flex items-center gap-2 text-gray-500">
                                            <CheckCircleIcon className="h-5 w-5 text-green-500" />
                                            <span>
                                                {
                                                    experiment.completed_sessions_count
                                                }{" "}
                                                sessions complétées
                                            </span>
                                        </div>
                                    </div>
                                    <div className="relative mt-4 flex items-center gap-x-4">
                                        <UserCircleIcon className="h-10 w-10 text-gray-600" />
                                        <div className="text-sm leading-6">
                                            <p className="font-semibold text-gray-900">
                                                {experiment.creator_name}
                                            </p>
                                            <p className="text-gray-600">
                                                Créateur
                                            </p>
                                        </div>
                                    </div>
                                    <div className="group relative mt-4">
                                        <h3 className="text-lg font-semibold leading-6 text-gray-900 group-hover:text-gray-600">
                                            {experiment.name}
                                        </h3>
                                        <p className="mt-3 line-clamp-3 text-sm leading-6 text-gray-600">
                                            {experiment.description}
                                        </p>
                                    </div>
                                    <div className="mt-6 flex w-full justify-end space-x-4">
                                        <button
                                            onClick={() =>
                                                handleAccessRequest(
                                                    experiment.id,
                                                    "results"
                                                )
                                            }
                                            className={`rounded-md px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 ${
                                                isAuthenticated
                                                    ? "bg-blue-600 hover:bg-blue-500"
                                                    : "bg-gray-400 cursor-not-allowed"
                                            }`}
                                        >
                                            {!isAuthenticated && (
                                                <LockClosedIcon className="w-4 h-4 inline mr-2" />
                                            )}
                                            Demander l'accès aux résultats
                                        </button>
                                        <button
                                            onClick={() =>
                                                handleAccessRequest(
                                                    experiment.id,
                                                    "access"
                                                )
                                            }
                                            className={`rounded-md px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 ${
                                                isAuthenticated
                                                    ? "bg-green-600 hover:bg-green-500"
                                                    : "bg-gray-400 cursor-not-allowed"
                                            }`}
                                        >
                                            {!isAuthenticated && (
                                                <LockClosedIcon className="w-4 h-4 inline mr-2" />
                                            )}
                                            Demander l'accès à l'expérimentation
                                        </button>
                                    </div>
                                </article>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Modal de connexion */}
            <Modal
                isOpen={showLoginModal}
                onClose={closeModals}
                title="Connexion requise"
                footer={
                    <>
                        <button
                            className="px-4 py-2 text-sm text-gray-600"
                            onClick={closeModals}
                        >
                            Annuler
                        </button>
                        <a
                            href="/admin/login"
                            className="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500"
                        >
                            Se connecter
                        </a>
                    </>
                }
            >
                <p>Vous devez être connecté pour effectuer cette action.</p>
            </Modal>

            {/* Modal de demande */}
            <Modal
                isOpen={showRequestModal}
                onClose={closeModals}
                title={`Demande d'accès ${
                    currentRequest?.type === "results"
                        ? "aux résultats"
                        : "à l'expérimentation"
                }`}
                footer={
                    <>
                        <button
                            className="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-md"
                            onClick={closeModals}
                        >
                            Annuler
                        </button>
                        <button
                            onClick={submitRequest}
                            disabled={requestMessage.length < 10}
                            className={`px-4 py-2 text-sm text-white rounded-md ${
                                requestMessage.length >= 10
                                    ? "bg-indigo-600 hover:bg-indigo-500"
                                    : "bg-gray-400 cursor-not-allowed"
                            }`}
                        >
                            Envoyer la demande
                        </button>
                    </>
                }
            >
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        Veuillez expliquer pourquoi vous souhaitez accéder à{" "}
                        {currentRequest?.type === "results"
                            ? "ces résultats"
                            : "cette expérimentation"}{" "}
                        :
                    </label>
                    <textarea
                        value={requestMessage}
                        onChange={(e) => setRequestMessage(e.target.value)}
                        className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        rows="4"
                        placeholder="Décrivez votre intérêt et vos motivations..."
                        minLength={10}
                        required
                    />
                    {requestMessage.length < 10 && (
                        <p className="text-sm text-red-500 mt-1">
                            Le message doit contenir au moins 10 caractères
                        </p>
                    )}
                </div>
            </Modal>

            {/* Notification de statut */}
            {requestStatus.show && (
                <div
                    className={`fixed bottom-4 right-4 p-4 rounded-md shadow-lg ${
                        requestStatus.error
                            ? "bg-red-100 text-red-700"
                            : "bg-green-100 text-green-700"
                    }`}
                >
                    {requestStatus.message}
                </div>
            )}
        </div>
    );
}

export default ExperimentList;
