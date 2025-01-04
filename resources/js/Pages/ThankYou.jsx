import React, { useState } from "react";
import { useTranslation } from "../Contexts/LanguageContext";
import { useLocation, useNavigate } from "react-router-dom";
import { AlertCircle, CheckCircle2 } from "lucide-react";
import Confetti from "../Components/ReactCanvasConfetti";

function ThankYou() {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const location = useLocation();
    const isTestMode = location.state?.isTest;

    return (
        <div className="min-h-screen flex items-center justify-center bg-gray-50">
            <div className="max-w-md w-full space-y-8 p-8 bg-white rounded-lg shadow-md">
                <div className="text-center">
                    <h1 className="text-3xl font-bold text-gray-900 mb-4">
                        {isTestMode
                            ? t("experimentSession.thankYou.titleTest")
                            : t("experimentSession.thankYou.title")}
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
                            ? t("experimentSession.thankYou.messageTest")
                            : t("experimentSession.thankYou.message")}
                    </p>
                    <button
                        onClick={() => navigate("/")}
                        className="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200"
                    >
                        {t("experimentSession.thankYou.returnHome")}
                    </button>
                </div>
            </div>
            <Confetti />
        </div>
    );
}

export default ThankYou;
