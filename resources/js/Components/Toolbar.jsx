import React from "react";
import { useTranslation } from "../Contexts/LanguageContext";
import { Check, RefreshCw } from "lucide-react";

function Toolbar({ onRestart, onLeave, onTerminate, isFinished, isTestMode }) {
    const { t } = useTranslation();
    return (
        <div className="fixed bottom-0 left-0 right-[449px] bg-gray-800 h-14 flex items-center justify-between gap-4 px-4 shadow-lg z-40">
            <div className="flex space-x-4">
                <button
                    onClick={onRestart}
                    disabled={isFinished}
                    className="flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <RefreshCw />
                    {t("experimentSession.toolbar.reload")}
                </button>

                <button
                    onClick={onLeave}
                    disabled={isFinished}
                    className="flex items-center gap-2 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {t("experimentSession.toolbar.quit")}
                </button>
            </div>

            {isTestMode && (
                <div className="px-4 bg-yellow-400 text-black py-2 text-center font-medium z-20">
                    {t("experimentSession.toolbar.testMode")}
                </div>
            )}

            {!isFinished && (
                <button
                    onClick={onTerminate}
                    className="flex items-center gap-2 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors"
                >
                    <Check />
                    {t("experimentSession.toolbar.terminate")}
                </button>
            )}
        </div>
    );
}

export default Toolbar;
