import { createContext, useContext, useState, useEffect } from "react";

const LanguageContext = createContext();

export const LanguageProvider = ({ children }) => {
    const [language, setLanguage] = useState(
        () => localStorage.getItem("language") || "fr"
    );
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

    return (
        <LanguageContext.Provider
            value={{
                language,
                changeLanguage,
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
