import { useState } from "react";
import { Plus, X } from "lucide-react";

const LanguageMenu = () => {
    const [isOpen, setIsOpen] = useState(false);
    const [currentLang, setCurrentLang] = useState("fr");

    const languages = [
        {
            code: "fr",
            name: "Français",
            label: "FR",
        },
        {
            code: "en",
            name: "English",
            label: "EN",
        },
        {
            code: "es",
            name: "Español",
            label: "ES",
        },
    ];

    const handleLanguageSelect = (code) => {
        setCurrentLang(code);
        setIsOpen(false); // Ferme le menu après la sélection
        // Ajoutez ici votre logique de changement de langue
        console.log(`Changing language to: ${code}`);
    };

    return (
        <div className="fixed right-11 bottom-20 flex flex-col items-end gap-4 z-50">
            {isOpen && (
                <div className="flex flex-col gap-3 mb-3">
                    {languages
                        .filter((lang) => lang.code !== currentLang)
                        .map((lang, index) => (
                            <div
                                key={lang.code}
                                className="flex items-center justify-end gap-2 group w-full"
                                style={{
                                    transform: `translateY(${
                                        isOpen ? "0" : "20px"
                                    }`,
                                    opacity: isOpen ? 1 : 0,
                                    transition: `transform 0.3s ease-in-out ${
                                        index * 0.1
                                    }s, opacity 0.3s ease-in-out ${
                                        index * 0.1
                                    }s`,
                                }}
                            >
                                <div className="flex items-center gap-2 w-full justify-end">
                                    <span className="bg-black text-white text-sm py-1 px-3 rounded-full transition-opacity duration-200 whitespace-nowrap">
                                        {lang.name}
                                    </span>
                                    <button
                                        onClick={() =>
                                            handleLanguageSelect(lang.code)
                                        }
                                        className="bg-slate-200 w-14 h-14 rounded-full flex items-center justify-center text-white shadow-xl hover:scale-110 transition-transform duration-200 flex-shrink-0"
                                    >
                                        <img
                                            src={`https://flagcdn.com/${
                                                lang.code === "en"
                                                    ? "gb"
                                                    : lang.code
                                            }.svg`}
                                            alt={lang.name}
                                            className="w-8 h-auto object-cover"
                                        />
                                    </button>
                                </div>
                            </div>
                        ))}
                </div>
            )}
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="bg-slate-400 w-14 h-14 rounded-full flex items-center justify-center text-white shadow-lg hover:bg-slate-600 transition-colors duration-200"
            >
                {isOpen ? (
                    <X size={20} />
                ) : (
                    <div className="flex justify-center items-center">
                        <img
                            src={`https://flagcdn.com/${
                                currentLang === "en" ? "gb" : currentLang
                            }.svg`}
                            alt={
                                languages.find((l) => l.code === currentLang)
                                    ?.name
                            }
                            className="w-8 h-auto object-cover"
                        />
                    </div>
                )}
            </button>
        </div>
    );
};

export default LanguageMenu;
