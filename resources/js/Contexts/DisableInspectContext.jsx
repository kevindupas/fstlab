import React, { createContext, useContext, useEffect } from "react";

const DisableInspectContext = createContext();

export function DisableInspectProvider({ children }) {
    useEffect(() => {
        // Désactiver le clic droit
        const handleContextMenu = (e) => {
            e.preventDefault();
        };

        // Désactiver F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U
        const handleKeyDown = (e) => {
            if (
                e.keyCode === 123 || // F12
                (e.ctrlKey && e.shiftKey && e.keyCode === 73) || // Ctrl+Shift+I
                (e.ctrlKey && e.shiftKey && e.keyCode === 74) || // Ctrl+Shift+J
                (e.ctrlKey && e.keyCode === 85) // Ctrl+U
            ) {
                e.preventDefault();
            }
        };

        // Désactiver la sélection de texte
        const handleSelect = (e) => {
            e.preventDefault();
        };

        // Désactiver le glisser-déposer d'images
        const handleDragStart = (e) => {
            e.preventDefault();
        };

        // Ajouter tous les event listeners
        document.addEventListener("contextmenu", handleContextMenu);
        document.addEventListener("keydown", handleKeyDown);
        document.addEventListener("selectstart", handleSelect);
        document.addEventListener("dragstart", handleDragStart);

        // Nettoyage
        return () => {
            document.removeEventListener("contextmenu", handleContextMenu);
            document.removeEventListener("keydown", handleKeyDown);
            document.removeEventListener("selectstart", handleSelect);
            document.removeEventListener("dragstart", handleDragStart);
        };
    }, []);

    return (
        <DisableInspectContext.Provider value={true}>
            {children}
        </DisableInspectContext.Provider>
    );
}

export function useDisableInspect() {
    return useContext(DisableInspectContext);
}
