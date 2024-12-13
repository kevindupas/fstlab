import React from "react";
import { useTranslation } from "../Contexts/LanguageContext";
import {useLocation, useNavigate} from "react-router-dom";
import {AlertCircle, CheckCircle2} from "lucide-react";

function ThankYou() {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const location = useLocation();
    const isTestMode = location.state?.isTest;

    console.log("Translation function:", t);
    console.log("Test translation:", t("experimentSession.thankYou.title"));

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50">
            <div className="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow-md">
                <div className="text-center">
                    <h1 className="text-3xl font-bold text-gray-900 mb-4">
                        {isTestMode ? "Test Terminé" : t("experimentSession.thankYou.title")}
                    </h1>
                    <div className="mb-6">
                        {isTestMode ? (
                            <AlertCircle className="mx-auto h-16 w-16 text-yellow-500" />
                        ) : (
                            <CheckCircle2 className="mx-auto h-16 w-16 text-green-500" />
                        )}
                    </div>
                    <p className="text-lg text-gray-600 mb-8">
                        {isTestMode
                            ? "Merci d'avoir testé l'expérience. Aucune donnée n'a été enregistrée puisque vous étiez en mode test."
                            : t("experimentSession.thankYou.message")
                        }
                    </p>
                    <button
                        onClick={() => navigate("/")}
                        className="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200"
                    >
                        {t("experimentSession.thankYou.returnHome")}
                    </button>
                </div>
            </div>
        </div>
    );
}

export default ThankYou;
