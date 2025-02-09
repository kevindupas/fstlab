import React, { useState, useEffect } from "react";
import { Beaker, Play, Music, Image, Film } from "lucide-react";
import axios from "axios";
import { useTranslation } from "../Contexts/LanguageContext";
import FloatingLanguageButton from "../Components/FloatingLanguageButton";

function HowItWork() {
    const { t } = useTranslation();
    const [experiments, setExperiments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        axios
            .get("/api/howitwork/experiments")
            .then((response) => {
                setExperiments(response.data);
                setLoading(false);
            })
            .catch((error) => {
                setError(t("howItWork.loading.error"));
                setLoading(false);
            });
    }, [t]);

    const typeInfo = {
        sound: {
            title: t("howItWork.types.sound.title"),
            description: t("howItWork.types.sound.description"),
            icon: Music,
            gradient: "from-purple-500 to-blue-500",
        },
        image: {
            title: t("howItWork.types.image.title"),
            description: t("howItWork.types.image.description"),
            icon: Image,
            gradient: "from-green-500 to-teal-500",
        },
        image_sound: {
            title: t("howItWork.types.image_sound.title"),
            description: t("howItWork.types.image_sound.description"),
            icon: Film,
            gradient: "from-orange-500 to-red-500",
        },
    };

    if (loading) {
        return (
            <div className="min-h-[80vh] flex items-center justify-center">
                <div className="animate-spin">
                    <Beaker size={40} className="text-blue-600" />
                </div>
            </div>
        );
    }

    return (
        <div className="min-h-[80vh] bg-gradient-to-b from-white to-gray-50 py-12 px-4 mt-5">
            <div className="max-w-7xl mx-auto">
                {/* En-tête */}
                <div className="text-center mb-16">
                    <div className="flex justify-center items-center mb-6">
                        <Beaker size={48} className="text-blue-600" />
                    </div>
                    <h1 className="text-4xl font-bold text-gray-900 mb-4">
                        {t("howItWork.header.title")}
                    </h1>
                    <p className="text-lg text-gray-600 max-w-2xl mx-auto">
                        {t("howItWork.header.description")}
                    </p>
                </div>

                {/* Grid des types d'expériences */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    {Object.entries(typeInfo).map(([type, info]) => {
                        const Icon = info.icon;
                        const experimentsOfType = experiments.filter(
                            (exp) => exp.type === type
                        );
                        const demoExperiment = experimentsOfType[0];

                        return (
                            <div key={type} className="relative group h-full">
                                <div className="absolute inset-0 bg-gradient-to-r w-full h-full rounded-2xl opacity-75 group-hover:opacity-85 transition-opacity duration-300"></div>

                                <div className="relative overflow-hidden rounded-2xl bg-white shadow-xl transition-all duration-300 group-hover:shadow-2xl h-full flex flex-col">
                                    {/* Image de fond */}
                                    <div className="aspect-w-16 aspect-h-9">
                                        <img
                                            src="https://placehold.co/600x400"
                                            alt={info.title}
                                            className="w-full h-48 object-cover"
                                        />
                                    </div>

                                    {/* Contenu */}
                                    <div className="p-6 flex flex-col flex-grow">
                                        <div className="flex items-center gap-3 mb-4">
                                            <Icon
                                                size={24}
                                                className="text-blue-600"
                                            />
                                            <h3 className="text-xl font-semibold text-gray-900">
                                                {info.title}
                                            </h3>
                                        </div>

                                        <p className="text-gray-600 mb-6 flex-grow">
                                            {info.description}
                                        </p>

                                        <div className="mt-auto">
                                            {demoExperiment ? (
                                                <a
                                                    href={demoExperiment.link}
                                                    className="inline-flex items-center gap-2 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors w-full justify-center"
                                                >
                                                    <Play size={18} />
                                                    {t(
                                                        "howItWork.actions.tryExperiment"
                                                    )}
                                                </a>
                                            ) : (
                                                <button
                                                    disabled
                                                    className="inline-flex items-center gap-2 bg-gray-300 text-gray-500 px-6 py-3 rounded-lg w-full justify-center cursor-not-allowed"
                                                >
                                                    {t(
                                                        "howItWork.actions.noDemo"
                                                    )}
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>

                {error && (
                    <div className="bg-red-50 text-red-600 p-4 rounded-lg text-center">
                        {error}
                    </div>
                )}
            </div>
            <FloatingLanguageButton />
        </div>
    );
}

export default HowItWork;
