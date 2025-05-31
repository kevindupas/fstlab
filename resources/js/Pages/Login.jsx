import React, { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import { UnfinishedSessionModal } from "../Components/UnfinishedSessionModal";
import { useSession } from "../Contexts/SessionContext";
import DeviceOrientationCheck from "../Utils/DeviceOrientationCheck";
import { useTranslation } from "../Contexts/LanguageContext";
import { useExperimentStatus } from "../Contexts/ExperimentStatusContext";
import { getSystemInfo } from "../Utils/getSystemInfo.js";

function Login() {
    const { t, changeLanguageTemporary } = useTranslation();
    const { checkExperimentStatus } = useExperimentStatus();
    const { checkExistingSession } = useSession();
    const { sessionId } = useParams();
    const [participantId, setParticipantId] = useState("");
    const [error, setError] = useState("");
    const [isLoading, setIsLoading] = useState(false);
    const navigate = useNavigate();
    const [isInitialChecking, setIsInitialChecking] = useState(true);

    useEffect(() => {
        const verifyExperimentStatus = async () => {
            try {
                const isExperimentAvailable = await checkExperimentStatus(
                    sessionId
                );
                if (!isExperimentAvailable) {
                    setIsInitialChecking(false);
                    return;
                }

                // Récupérer les informations de l'expérimentation pour changer la langue
                try {
                    const response = await fetch(
                        `/api/experiment/session/${sessionId}`
                    );
                    const data = await response.json();

                    if (data.experiment && data.experiment.language) {
                        changeLanguageTemporary(data.experiment.language);
                    }
                } catch (error) {
                    console.error("Error getting experiment language:", error);
                }

                await checkExistingSession(sessionId);
                // Generate participant ID automatically
                const response = await fetch(
                    `/api/experiment/generate-participant-id/${sessionId}`
                );
                const data = await response.json();
                setParticipantId(data.participantId);
            } catch (error) {
                console.error("Error verifying experiment status:", error);
                setError(t("login.generic"));
            } finally {
                setIsLoading(false);
                setIsInitialChecking(false);
            }
        };

        verifyExperimentStatus();
    }, [
        sessionId,
        checkExperimentStatus,
        checkExistingSession,
        t,
        changeLanguageTemporary,
    ]);

    const handleRegistration = async (e) => {
        e.preventDefault();
        setIsLoading(true);
        setError("");

        const isExperimentAvailable = await checkExperimentStatus(sessionId);
        if (!isExperimentAvailable) {
            setIsLoading(false);
            return;
        }

        try {
            const systemData = getSystemInfo();

            const response = await fetch(
                `/api/experiment/register/${sessionId}`,
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        participant_number: participantId,
                        ...systemData,
                        status: "created",
                    }),
                }
            );

            const data = await response.json();

            if (response.status === 409) {
                setError(t("login.error"));
                return;
            }

            if (data.session) {
                try {
                    const elem = document.documentElement;
                    if (elem.requestFullscreen) {
                        await elem.requestFullscreen();
                    } else if (elem.webkitRequestFullscreen) {
                        await elem.webkitRequestFullscreen();
                    } else if (elem.msRequestFullscreen) {
                        await elem.msRequestFullscreen();
                    }
                } catch (error) {
                    console.warn("Fullscreen request failed:", error);
                }

                localStorage.setItem("participantNumber", participantId);
                localStorage.setItem("isRegistered", "true");
                localStorage.setItem(
                    "session",
                    JSON.stringify({
                        ...data.session,
                        link: sessionId,
                        ...systemData,
                    })
                );

                navigate(`/experiment/${sessionId}`);
            }
        } catch (error) {
            setError(t("login.generic"));
            console.error("Registration error:", error);
        } finally {
            setIsLoading(false);
        }
    };

    if (isInitialChecking) {
        return (
            <div className="flex flex-col items-center justify-center min-h-screen bg-gray-100">
                <div className="p-8 bg-white rounded-lg shadow-lg flex flex-col items-center">
                    <svg
                        className="animate-spin h-10 w-10 text-blue-500 mb-4"
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
                    <p className="text-gray-600">{t("login.checking")}</p>
                </div>
            </div>
        );
    }

    return (
        <DeviceOrientationCheck>
            <UnfinishedSessionModal />
            <div className="flex flex-col items-center justify-center min-h-screen bg-gray-100">
                <form
                    onSubmit={handleRegistration}
                    className="bg-white p-6 rounded-lg shadow-lg w-full max-w-md"
                >
                    <h2 className="text-2xl font-bold text-gray-900 mb-4">
                        {t("login.title")}
                    </h2>

                    {/* Privacy Notice */}
                    <p className="mb-6 p-4 bg-blue-50 rounded-lg text-sm text-blue-800">
                        {t("login.privacy_notice")}
                    </p>

                    <div className="mb-4">
                        <label className="block text-gray-700 mb-2">
                            {t("login.participant_id_label")} :
                        </label>
                        <div className="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-700 font-mono">
                            {participantId}
                        </div>
                    </div>

                    {error && <p className="text-red-500 mb-4">{error}</p>}

                    <button
                        type="submit"
                        className={`w-full px-4 py-2 rounded-md font-semibold text-white transition-colors ${
                            isLoading
                                ? "bg-blue-400 cursor-not-allowed"
                                : "bg-blue-500 hover:bg-blue-600"
                        }`}
                        disabled={isLoading}
                    >
                        {isLoading ? (
                            <div className="flex items-center justify-center">
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
                                {t("login.loading")}
                            </div>
                        ) : (
                            t("login.submit")
                        )}
                    </button>
                </form>
            </div>
        </DeviceOrientationCheck>
    );
}

export default Login;
