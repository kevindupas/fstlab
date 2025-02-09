import React, { useEffect } from "react";
import { useTranslation } from "../Contexts/LanguageContext";
import { useLocation, useNavigate } from "react-router-dom";
import {
    AlertCircle,
    ArrowBigLeft,
    ArrowRight,
    CheckCircle2,
    Lock,
} from "lucide-react";
import Confetti from "../Components/ReactCanvasConfetti";

function ThankYou() {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const location = useLocation();
    const isTestMode = location.state?.isTest;

    useEffect(() => {
        const exitFullscreen = async () => {
            try {
                if (document.fullscreenElement) {
                    if (document.exitFullscreen) {
                        await document.exitFullscreen();
                    } else if (document.webkitExitFullscreen) {
                        await document.webkitExitFullscreen();
                    } else if (document.msExitFullscreen) {
                        await document.msExitFullscreen();
                    }
                }
            } catch (error) {
                console.warn("Exit fullscreen failed:", error);
            }
        };

        exitFullscreen();
    }, []);

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
                    <div className="space-y-4">
                        <button
                            onClick={() => navigate("/")}
                            className="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors duration-200"
                        >
                            {t("experimentSession.thankYou.returnHome")}
                        </button>

                        <div className="pt-4 border-t flex flex-col justify-center items-center space-y-4">
                            <a
                                href="/privacy"
                                className="items-center text-blue-600 hover:text-blue-800 transition-colors gap-2 text-sm"
                            >
                                {t("experimentSession.thankYou.privacyPolicy")}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <Confetti />
        </div>
    );
}

export default ThankYou;
