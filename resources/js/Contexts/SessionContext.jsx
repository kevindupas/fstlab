// contexts/SessionContext.jsx
import React, { createContext, useContext, useState } from "react";
import { useNavigate } from "react-router-dom";

const SessionContext = createContext(null);

export function SessionProvider({ children }) {
    const [showUnfinishedModal, setShowUnfinishedModal] = useState(false);
    const navigate = useNavigate();

    // Vérifie si une session existe dans le localStorage
    const checkExistingSession = (currentLink) => {
        const existingSession = localStorage.getItem("session");
        const isRegistered = localStorage.getItem("isRegistered") === "true";

        if (existingSession && isRegistered) {
            const storedSession = JSON.parse(existingSession);

            if (storedSession.status !== "completed") {
                setShowUnfinishedModal(true);
                return true;
            }
        }
        return false;
    };

    // Continue la session existante
    const handleContinuePrevious = () => {
        const storedSession = JSON.parse(localStorage.getItem("session"));
        if (storedSession) {
            setShowUnfinishedModal(false);
            navigate(`/experiment/${storedSession.link}`);
        }
    };

    // Démarre une nouvelle session
    const handleStartNew = async (currentLink) => {
        try {
            const existingSession = JSON.parse(localStorage.getItem("session"));

            // Supprimer l'ancienne session de la BDD si elle existe
            if (existingSession?.id) {
                await fetch(`/api/experiment/session/${existingSession.id}`, {
                    method: "DELETE",
                });
            }
        } catch (error) {
            console.error("Error deleting previous session:", error);
        } finally {
            // Dans tous les cas, on nettoie le localStorage et on redirige
            localStorage.clear();
            setShowUnfinishedModal(false);
            navigate(`/login/${currentLink}`);
        }
    };

    return (
        <SessionContext.Provider
            value={{
                showUnfinishedModal,
                checkExistingSession,
                handleContinuePrevious,
                handleStartNew,
            }}
        >
            {children}
        </SessionContext.Provider>
    );
}

export const useSession = () => {
    const context = useContext(SessionContext);
    if (!context) {
        throw new Error("useSession must be used within a SessionProvider");
    }
    return context;
};
