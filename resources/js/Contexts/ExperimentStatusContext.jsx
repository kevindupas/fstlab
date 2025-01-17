import React, { createContext, useContext } from "react";
import { useNavigate } from "react-router-dom";

const ExperimentStatusContext = createContext(null);

export function ExperimentStatusProvider({ children }) {
    const navigate = useNavigate();

    const checkExperimentStatus = async (sessionId) => {
        try {
            const response = await fetch(
                `/api/experiment/session/${sessionId}`
            );
            const data = await response.json();

            if (!data.experiment) {
                navigate("/experiment-error", {
                    state: { status: "not_found" },
                });
                return false;
            }

            switch (data.experiment.status) {
                case "start":
                    return true;
                case "test":
                    navigate(`/experiment/${sessionId}`, {
                        state: {
                            isTest: true,
                            experiment: data.experiment,
                            media: data.media,
                        },
                    });
                    return { isAvailable: false };
                case "pause":
                    // Si en pause, on supprime la session existante
                    try {
                        const existingSession = JSON.parse(
                            localStorage.getItem("session")
                        );
                        if (existingSession?.id) {
                            await fetch(
                                `/api/experiment/session/${existingSession.id}`,
                                {
                                    method: "DELETE",
                                }
                            );
                            localStorage.clear();
                        }
                    } catch (error) {
                        console.error("Error cleaning up session:", error);
                    }
                    navigate("/experiment-error", {
                        state: { status: "pause" },
                    });
                    return false;

                case "stop":
                    try {
                        const existingSession = JSON.parse(
                            localStorage.getItem("session")
                        );
                        if (existingSession?.id) {
                            await fetch(
                                `/api/experiment/session/${existingSession.id}`,
                                {
                                    method: "DELETE",
                                }
                            );
                            localStorage.clear();
                        }
                    } catch (error) {
                        console.error("Error cleaning up session:", error);
                    }
                    navigate("/experiment-error", {
                        state: { status: "stop" },
                    });
                    return false;

                default:
                    navigate("/experiment-error", {
                        state: { status: "unknown" },
                    });
                    return false;
            }
        } catch (error) {
            console.error("Error checking experiment status:", error);
            navigate("/experiment-error", { state: { status: "unknown" } });
            return false;
        }
    };

    return (
        <ExperimentStatusContext.Provider value={{ checkExperimentStatus }}>
            {children}
        </ExperimentStatusContext.Provider>
    );
}

export const useExperimentStatus = () => {
    const context = useContext(ExperimentStatusContext);
    if (!context) {
        throw new Error(
            "useExperimentStatus must be used within an ExperimentStatusProvider"
        );
    }
    return context;
};
