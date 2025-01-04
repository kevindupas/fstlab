import React from "react";
import { Dialog, DialogPanel, DialogTitle } from "@headlessui/react";
import { AlertCircle } from "lucide-react";
import { useTranslation } from "../Contexts/LanguageContext";

export function TestModeModal({ isOpen, onClose }) {
    const { t } = useTranslation();

    return (
        <Dialog open={isOpen} onClose={onClose} className="relative z-50">
            <div className="fixed inset-0 bg-black/30" aria-hidden="true" />
            <div className="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel className="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                    <div className="flex space-x-4">
                        <div className="flex-shrink-0">
                            <AlertCircle className="h-6 w-6 text-yellow-500" />
                        </div>
                        <DialogTitle className="text-lg font-medium leading-6 text-gray-900">
                            {t("testModeModal.title")}
                        </DialogTitle>
                    </div>

                    <div className="mt-2">
                        <p className="text-sm text-gray-500">
                            {t("testModeModal.description")}
                        </p>
                        <ul className="mt-3 list-disc pl-5 text-sm text-gray-500 space-y-2">
                            <li>{t("testModeModal.features.noData")}</li>
                            <li>{t("testModeModal.features.noSession")}</li>
                            <li>{t("testModeModal.features.noResults")}</li>
                            <li>{t("testModeModal.features.testOnly")}</li>
                        </ul>
                    </div>

                    <div className="mt-6">
                        <button
                            type="button"
                            className="w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                            onClick={onClose}
                        >
                            {t("testModeModal.actions.understand")}
                        </button>
                    </div>
                </DialogPanel>
            </div>
        </Dialog>
    );
}
