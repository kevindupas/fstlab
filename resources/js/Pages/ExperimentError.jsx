import React from "react";
import { useTranslation } from "../Contexts/LanguageContext";
import { useNavigate, useLocation } from "react-router-dom";
import { XCircle } from "lucide-react";

function ExperimentError() {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const location = useLocation();
    const { status } = location.state || { status: "unknown" };

    const getErrorContent = () => {
        switch (status) {
            case "pause":
                return {
                    title: t("experimentError.status.pause.title"),
                    message: t("experimentError.status.pause.message"),
                    icon: "⏸️",
                };
            case "stop":
                return {
                    title: t("experimentError.status.stop.title"),
                    message: t("experimentError.status.stop.message"),
                    icon: "🏁",
                };
            case "not_found":
                return {
                    title: t("experimentError.status.not_found.title"),
                    message: t("experimentError.status.not_found.message"),
                    icon: "❌",
                };
            default:
                return {
                    title: t("experimentError.status.default.title"),
                    message: t("experimentError.status.default.message"),
                    icon: "⚠️",
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
                        {status === "not_found" ? (
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
                        {t("experimentError.actions.returnHome")}
                    </button>
                </div>
            </div>
        </div>
    );
}

export default ExperimentError;
