import React from "react";
import { useTranslation } from "../Contexts/LanguageContext";
import { useNavigate, useLocation } from "react-router-dom";
import { XCircle } from "lucide-react";

function ExperimentError() {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const location = useLocation();
    const { status } = location.state || { status: 'unknown' };

    const getErrorContent = () => {
        switch (status) {
            case 'pause':
                return {
                    title: "Expérience en Pause",
                    message: "Cette expérience est actuellement en pause. Veuillez réessayer ultérieurement.",
                    icon: "⏸️"
                };
            case 'stop':
                return {
                    title: "Expérience Terminée",
                    message: "Cette expérience est terminée. Merci de votre intérêt.",
                    icon: "🏁"
                };
            case 'not_found':
                return {
                    title: "Expérience Introuvable",
                    message: "Cette expérience n'existe pas ou est terminée.",
                    icon: "❌"
                };
            default:
                return {
                    title: "Erreur",
                    message: "Une erreur est survenue lors de l'accès à l'expérience. Veuillez contacter l'administrateur.",
                    icon: "⚠️"
                };
        }
    };

    const errorContent = getErrorContent();

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50 p-4">
            <div className="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow-md">
                <div className="text-center">
                    <h1 className="text-3xl font-bold text-gray-900 mb-4">
                        {errorContent.title}
                    </h1>
                    <div className="mb-6 flex justify-center">
                        {status === 'not_found' ? (
                            <XCircle className="h-16 w-16 text-red-500" />
                        ) : (
                            <span className="text-4xl">
                                {errorContent.icon}
                            </span>
                        )}
                    </div>
                    <p className="text-lg text-gray-600 mb-8">
                        {errorContent.message}
                    </p>
                    <button
                        onClick={() => navigate("/")}
                        className="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200"
                    >
                        Retourner à l'accueil
                    </button>
                </div>
            </div>
        </div>
    );
}

export default ExperimentError;
