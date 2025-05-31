import { createContext, useContext, useState, useEffect } from "react";

const LanguageContext = createContext();

export const LanguageProvider = ({ children }) => {
    const [language, setLanguage] = useState(
        () => localStorage.getItem("language") || "fr"
    );
    const [originalLanguage, setOriginalLanguage] = useState(null);
    const [translations, setTranslations] = useState({});
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const loadTranslations = async () => {
            if (translations[language]) {
                setLoading(false);
                return;
            }

            try {
                const response = await fetch(
                    `/api/translations/${language}?_=${Date.now()}`
                );
                if (!response.ok) {
                    throw new Error("Failed to fetch translations");
                }
                const data = await response.json();
                console.log(data);

                setTranslations((prev) => ({
                    ...prev,
                    [language]: data,
                }));
            } catch (error) {
                console.error("Error loading translations:", error);
            } finally {
                setLoading(false);
            }
        };

        localStorage.setItem("language", language);
        loadTranslations();
    }, [language]);

    const t = (key) => {
        if (loading || !translations[language]) return key;

        const keys = key.split(".");
        let value = translations[language];

        for (const k of keys) {
            value = value?.[k];
            if (!value) return key;
        }

        return value;
    };

    const changeLanguage = (newLang) => {
        setLanguage(newLang);
    };

    // Changer temporairement la langue pour une expérimentation
    const changeLanguageTemporary = (experimentLang) => {
        // Sauvegarder la langue actuelle si ce n'est pas déjà fait
        if (!originalLanguage) {
            setOriginalLanguage(language);
        }
        // Changer vers la langue de l'expérimentation
        setLanguage(experimentLang);
    };

    // Restaurer la langue originale
    const restoreOriginalLanguage = () => {
        if (originalLanguage) {
            setLanguage(originalLanguage);
            setOriginalLanguage(null);
        }
    };

    return (
        <LanguageContext.Provider
            value={{
                language,
                changeLanguage,
                changeLanguageTemporary,
                restoreOriginalLanguage,
                t,
                isLoading: loading,
            }}
        >
            {children}
        </LanguageContext.Provider>
    );
};

export const useTranslation = () => {
    const context = useContext(LanguageContext);
    if (context === undefined) {
        throw new Error(
            "useTranslation must be used within a LanguageProvider"
        );
    }
    return context;
};
