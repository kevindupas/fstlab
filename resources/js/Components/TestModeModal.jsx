import React from "react";
import { Dialog } from "@headlessui/react";
import { AlertCircle } from "lucide-react";

export function TestModeModal({ isOpen, onClose }) {
    return (
        <Dialog
            open={isOpen}
            onClose={onClose}
            className="relative z-50"
        >
            <div className="fixed inset-0 bg-black/30" aria-hidden="true" />
            <div className="fixed inset-0 flex items-center justify-center p-4">
                <Dialog.Panel className="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                    <div className="flex space-x-4">
                        <div className="flex-shrink-0">
                            <AlertCircle className="h-6 w-6 text-yellow-500" />
                        </div>
                        <Dialog.Title className="text-lg font-medium leading-6 text-gray-900">
                            Mode Test Activé
                        </Dialog.Title>
                    </div>

                    <div className="mt-2">
                        <p className="text-sm text-gray-500">
                            Vous êtes actuellement en mode test. Cela signifie que :
                        </p>
                        <ul className="mt-3 list-disc pl-5 text-sm text-gray-500 space-y-2">
                            <li>Aucune donnée ne sera enregistrée</li>
                            <li>Aucune session ne sera créée</li>
                            <li>Les résultats ne seront pas sauvegardés</li>
                            <li>Ce mode est uniquement destiné à tester l'interface et le fonctionnement</li>
                        </ul>
                    </div>

                    <div className="mt-6">
                        <button
                            type="button"
                            className="w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600"
                            onClick={onClose}
                        >
                            J'ai compris
                        </button>
                    </div>
                </Dialog.Panel>
            </div>
        </Dialog>
    );
}
