import React, { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import UAParser from "ua-parser-js";
import { UnfinishedSessionModal } from "../Components/UnfinishedSessionModal";
import { useSession } from "../Contexts/SessionContext";
import DeviceOrientationCheck from "../Utils/DeviceOrientationCheck";

function Login() {
    const {
        checkExistingSession,
        setParticipantNumber: resetParticipantNumber,
    } = useSession();
    const { sessionId } = useParams();
    const [participantNumber, setParticipantNumber] = useState("");
    const [error, setError] = useState("");
    const [isLoading, setIsLoading] = useState(false);
    const navigate = useNavigate();

    useEffect(() => {
        checkExistingSession(sessionId);
        setParticipantNumber("");
    }, [sessionId]);

    const getSystemInfo = () => {
        const parser = new UAParser();
        const result = parser.getResult();

        return {
            browser: `${result.browser.name} ${result.browser.version}`,
            device_type: result.device.type || "desktop",
            operating_system: `${result.os.name} ${result.os.version}`,
            screen_width: window.innerWidth,
            screen_height: window.innerHeight,
            is_dark: window.matchMedia("(prefers-color-scheme: dark)").matches,
        };
    };

    const handleRegistration = async (e) => {
        e.preventDefault();
        setIsLoading(true);
        setError("");

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
                        participant_number: participantNumber,
                        ...systemData,
                        status: "created",
                    }),
                }
            );

            const data = await response.json();

            if (response.status === 409) {
                setError("Ce numéro a déjà été utilisé pour cette expérience.");
                return;
            }

            if (data.session) {
                localStorage.setItem("participantNumber", participantNumber);
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
            setError(
                "Une erreur s'est produite lors de l'enregistrement. Veuillez réessayer."
            );
            console.error("Registration error:", error);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <DeviceOrientationCheck>
            <UnfinishedSessionModal />
            <div className="flex flex-col items-center justify-center min-h-screen bg-gray-100">
                <form
                    onSubmit={handleRegistration}
                    className="bg-white p-6 rounded-lg shadow-lg w-full max-w-md"
                >
                    <h2 className="text-2xl font-bold text-gray-900 mb-4">
                        S'inscrire pour l'expérience
                    </h2>
                    <div className="mb-4">
                        <label className="block text-gray-700 mb-2">
                            Numéro d'identification :
                        </label>
                        <input
                            type="text"
                            value={participantNumber}
                            onChange={(e) =>
                                setParticipantNumber(e.target.value)
                            }
                            className="w-full px-4 py-2 border border-gray-300 rounded-md"
                            required
                            disabled={isLoading}
                        />
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
                                Enregistrement en cours...
                            </div>
                        ) : (
                            "Démarrer l'expérience"
                        )}
                    </button>
                </form>
            </div>
        </DeviceOrientationCheck>
    );
}

export default Login;
