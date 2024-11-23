// components/UnfinishedSessionModal.jsx
import React from "react";
import { useParams } from "react-router-dom";
import { useSession } from "../Contexts/SessionContext";

export function UnfinishedSessionModal() {
    const { showUnfinishedModal, handleContinuePrevious, handleStartNew } =
        useSession();
    const { sessionId } = useParams();

    if (!showUnfinishedModal) return null;

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div className="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h2 className="text-xl font-bold text-gray-900 mb-4">
                    Session non terminée détectée
                </h2>
                <p className="text-gray-600 mb-6">
                    Une session est déjà en cours. Voulez-vous la continuer ou
                    en démarrer une nouvelle ? Le démarrage d'une nouvelle
                    session supprimera la session en cours.
                </p>
                <div className="flex flex-col sm:flex-row gap-3 justify-end">
                    <button
                        onClick={handleContinuePrevious}
                        className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors"
                    >
                        Continuer la session en cours
                    </button>
                    <button
                        onClick={() => handleStartNew(sessionId)}
                        className="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors"
                    >
                        Démarrer une nouvelle session
                    </button>
                </div>
            </div>
        </div>
    );
}
