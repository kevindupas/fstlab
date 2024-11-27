import { useState } from "react";
import { Plus, X } from "lucide-react";
import { GB, FR, ES } from "country-flag-icons/react/3x2";

const FloatingLanguageButton = () => {
    const [isOpen, setIsOpen] = useState(false);
    const [currentLang, setCurrentLang] = useState("fr");

    const languages = [
        {
            code: "fr",
            name: "Français",
            label: "FR",
            flag: FR,
        },
        {
            code: "en",
            name: "English",
            label: "EN",
            flag: GB,
        },
        {
            code: "es",
            name: "Español",
            label: "ES",
            flag: ES,
        },
    ];

    const handleLanguageSelect = (code) => {
        setCurrentLang(code);
        setIsOpen(false);
        console.log(`Changing language to: ${code}`);
    };

    const FlagComponent = languages.find(
        (lang) => lang.code === currentLang
    )?.flag;

    return (
        <div className="fixed right-11 bottom-20 flex flex-col items-end gap-4 z-50">
            {isOpen && (
                <div className="flex flex-col gap-3 mb-3">
                    {languages
                        .filter((lang) => lang.code !== currentLang)
                        .map((lang, index) => {
                            const Flag = lang.flag;
                            return (
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
                                            className="bg-slate-200 w-14 h-14 rounded-full flex items-center justify-center text-white shadow-xl hover:scale-110 transition-transform duration-200 flex-shrink-0 p-3"
                                        >
                                            <Flag className="w-full h-full object-cover rounded-sm" />
                                        </button>
                                    </div>
                                </div>
                            );
                        })}
                </div>
            )}
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="bg-slate-400 w-14 h-14 rounded-full flex items-center justify-center text-white shadow-lg hover:bg-slate-600 transition-colors duration-200 p-3"
            >
                {isOpen ? (
                    <X size={20} />
                ) : FlagComponent ? (
                    <FlagComponent className="w-full h-full object-cover rounded-sm" />
                ) : null}
            </button>
        </div>
    );
};

export default FloatingLanguageButton;
