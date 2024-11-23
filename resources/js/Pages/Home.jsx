import { Dialog, DialogPanel } from "@headlessui/react";
import { useState } from "react";
import { useNavigate } from "react-router-dom";
import Header from "../Components/Header";

export default function Home() {
    const [showModal, setShowModal] = useState(false);
    const [showSessionModal, setShowSessionModal] = useState(false);
    const [sessionId, setSessionId] = useState("");
    const [error, setError] = useState("");
    const [isLoading, setIsLoading] = useState(false);
    const [currentExperiment, setCurrentExperiment] = useState(null);
    const [previousSession, setPreviousSession] = useState(null);
    const navigate = useNavigate();

    const handleSessionCheck = async () => {
        setIsLoading(true);
        setError("");

        try {
            // Vérifier si l'expérience existe
            const response = await fetch(
                `/api/experiment/session/${sessionId}`
            );
            const expData = await response.json();

            if (!expData.experiment) {
                setError("Code d'expérience invalide");
                return;
            }

            // Vérifier s'il existe une session en cours
            const existingSession = localStorage.getItem("session");
            const isRegistered =
                localStorage.getItem("isRegistered") === "true";

            if (existingSession && isRegistered) {
                const storedSession = JSON.parse(existingSession);

                if (storedSession.status !== "completed") {
                    setCurrentExperiment(expData.experiment);
                    setPreviousSession(storedSession);
                    setShowSessionModal(true);
                    return;
                }
            }

            // Si pas de session en cours, rediriger vers login
            navigate(`/login/${sessionId}`);
        } catch (error) {
            setError("Une erreur s'est produite");
        } finally {
            setIsLoading(false);
        }
    };

    const handleStartNew = async () => {
        setIsLoading(true);
        try {
            if (previousSession) {
                await fetch(`/api/experiment/session/${previousSession.id}`, {
                    method: "DELETE",
                });
            }

            // Nettoyer le localStorage
            localStorage.removeItem("session");
            localStorage.removeItem("isRegistered");
            localStorage.removeItem("participantNumber");

            // Rediriger vers la page de login
            navigate(`/login/${sessionId}`);
        } catch (error) {
            console.error("Error:", error);
            localStorage.clear();
            navigate(`/login/${sessionId}`);
        } finally {
            setIsLoading(false);
            setShowSessionModal(false);
        }
    };

    const handleContinueSession = () => {
        if (previousSession) {
            navigate(`/experiment/${sessionId}`);
        }
        setShowSessionModal(false);
    };

    return (
        <div className="bg-white">
            <Header />
            <div className="relative isolate pt-14">
                <div className="py-24 sm:py-32 lg:pb-40">
                    <div className="mx-auto max-w-7xl px-6 lg:px-8">
                        <div className="mx-auto max-w-2xl text-center">
                            <h1 className="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">
                                Plateforme d'Expérimentation Interactive
                            </h1>
                            <p className="mt-6 text-lg leading-8 text-gray-600">
                                Participez à des expériences innovantes et
                                contribuez à l'avancement de la recherche.
                            </p>
                            <div className="mt-10 flex items-center justify-center gap-x-6">
                                <button
                                    onClick={() => setShowModal(true)}
                                    className="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                                >
                                    Démarrer une expérience
                                </button>
                                <button
                                    onClick={() => navigate("/experiments")}
                                    className="text-sm font-semibold leading-6 text-gray-900"
                                >
                                    Voir les expériences{" "}
                                    <span aria-hidden="true">→</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Modal code d'expérience */}
                {showModal && (
                    <Dialog
                        open={showModal}
                        onClose={() => setShowModal(false)}
                        className="relative z-50"
                    >
                        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                        <div className="fixed inset-0 z-10 overflow-y-auto">
                            <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <DialogPanel className="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                                    <div>
                                        <div className="mt-3 text-center sm:mt-5">
                                            <h3 className="text-2xl font-semibold leading-6 text-gray-900 mb-4">
                                                Code d'accès à l'expérience
                                            </h3>
                                            <form
                                                onSubmit={(e) => {
                                                    e.preventDefault();
                                                    handleSessionCheck();
                                                }}
                                            >
                                                <input
                                                    type="text"
                                                    placeholder="Entrez le code d'expérience"
                                                    value={sessionId}
                                                    onChange={(e) =>
                                                        setSessionId(
                                                            e.target.value
                                                        )
                                                    }
                                                    className="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 mb-4 px-3"
                                                    disabled={isLoading}
                                                />
                                                {error && (
                                                    <p className="text-red-500 text-sm mb-4">
                                                        {error}
                                                    </p>
                                                )}
                                                <div className="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                                    <button
                                                        type="submit"
                                                        disabled={isLoading}
                                                        className="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:col-start-2"
                                                    >
                                                        {isLoading ? (
                                                            <div className="flex items-center justify-center">
                                                                <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                                                Vérification...
                                                            </div>
                                                        ) : (
                                                            "Valider"
                                                        )}
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() =>
                                                            setShowModal(false)
                                                        }
                                                        className="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:col-start-1 sm:mt-0"
                                                        disabled={isLoading}
                                                    >
                                                        Annuler
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </DialogPanel>
                            </div>
                        </div>
                    </Dialog>
                )}

                {/* Modal session existante */}
                {showSessionModal && (
                    <Dialog
                        open={showSessionModal}
                        onClose={() => setShowSessionModal(false)}
                        className="relative z-50"
                    >
                        <div className="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
                        <div className="fixed inset-0 z-10 overflow-y-auto">
                            <div className="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                                <DialogPanel className="relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6">
                                    <div className="mt-3 text-center sm:mt-5">
                                        <h3 className="text-2xl font-semibold leading-6 text-gray-900 mb-4">
                                            Session non terminée détectée
                                        </h3>
                                        <p className="text-gray-600 mb-6">
                                            Nous avons détecté une session
                                            d'expérience non terminée.
                                            Voulez-vous continuer la session
                                            précédente ou en commencer une
                                            nouvelle ? Le démarrage d'une
                                            nouvelle session supprimera la
                                            session non terminée.
                                        </p>
                                        <div className="mt-5 sm:mt-6 sm:grid sm:grid-flow-row-dense sm:grid-cols-2 sm:gap-3">
                                            <button
                                                onClick={handleContinueSession}
                                                className="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                                                disabled={isLoading}
                                            >
                                                Continuer la session précédente
                                            </button>
                                            <button
                                                onClick={handleStartNew}
                                                className="mt-3 inline-flex w-full justify-center rounded-md bg-gray-500 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-600 sm:mt-0"
                                                disabled={isLoading}
                                            >
                                                {isLoading ? (
                                                    <div className="flex items-center justify-center">
                                                        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                                                        Traitement en cours...
                                                    </div>
                                                ) : (
                                                    "Démarrer une nouvelle session"
                                                )}
                                            </button>
                                        </div>
                                    </div>
                                </DialogPanel>
                            </div>
                        </div>
                    </Dialog>
                )}
            </div>
        </div>
    );
}
