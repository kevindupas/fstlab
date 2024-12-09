import React from "react";
import { useTranslation } from "../Contexts/LanguageContext";

function Toolbar({ onRestart, onLeave, onTerminate, isFinished }) {
    const { t } = useTranslation();
    return (
        <div className="fixed bottom-0 left-0 right-[449px] bg-gray-800 h-14 flex items-center justify-between gap-4 px-4 shadow-lg z-40">
            <div className="flex space-x-4">
                <button
                    onClick={onRestart}
                    disabled={isFinished}
                    className="flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-5 w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fillRule="evenodd"
                            d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                            clipRule="evenodd"
                        />
                    </svg>
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

            {!isFinished && (
                <button
                    onClick={onTerminate}
                    className="flex items-center gap-2 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition-colors"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        className="h-5 w-5"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fillRule="evenodd"
                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                            clipRule="evenodd"
                        />
                    </svg>
                    {t("experimentSession.toolbar.terminate")}
                </button>
            )}
        </div>
    );
}

export default Toolbar;
