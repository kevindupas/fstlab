import { XMarkIcon } from "@heroicons/react/24/outline";
import React from "react";

function Modal({ isOpen, onClose, title, children, footer }) {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center">
            <div className="relative bg-white rounded-lg p-8 max-w-md w-full mx-4 shadow-xl">
                <div className="flex justify-between items-center mb-4">
                    <h3 className="text-lg font-medium text-gray-900">
                        {title}
                    </h3>
                    <button
                        onClick={onClose}
                        className="text-gray-400 hover:text-gray-500"
                    >
                        <XMarkIcon className="h-6 w-6" />
                    </button>
                </div>

                <div className="mb-6">{children}</div>

                {footer && (
                    <div className="flex justify-end gap-4">{footer}</div>
                )}
            </div>
        </div>
    );
}

export default Modal;
