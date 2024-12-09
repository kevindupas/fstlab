import {
    CheckCircleIcon,
    MagnifyingGlassIcon,
    UserCircleIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
} from "@heroicons/react/24/outline";
import React, { useState, useMemo } from "react";
import { useNavigate } from "react-router-dom";
import { useExperiments } from "../Contexts/ExperimentsContext";
import { useTranslation } from "../Contexts/LanguageContext";
import FloatingLanguageButton from "../Components/FloatingLanguageButton";

const ITEMS_PER_PAGE = 5;

function ExperimentList() {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const { experiments, isLoading, error } = useExperiments();
    const [searchQuery, setSearchQuery] = useState("");
    const [currentPage, setCurrentPage] = useState(1);

    // Filtrer les expériences en fonction de la recherche
    const filteredExperiments = useMemo(() => {
        return experiments.filter(
            (experiment) =>
                experiment.name
                    .toLowerCase()
                    .includes(searchQuery.toLowerCase()) ||
                experiment.description
                    .toLowerCase()
                    .includes(searchQuery.toLowerCase()) ||
                experiment.creator_name
                    .toLowerCase()
                    .includes(searchQuery.toLowerCase())
        );
    }, [experiments, searchQuery]);

    // Calculer le nombre total de pages
    const totalPages = Math.ceil(filteredExperiments.length / ITEMS_PER_PAGE);

    // Obtenir les expériences pour la page actuelle
    const currentExperiments = useMemo(() => {
        const start = (currentPage - 1) * ITEMS_PER_PAGE;
        const end = start + ITEMS_PER_PAGE;
        return filteredExperiments.slice(start, end);
    }, [filteredExperiments, currentPage]);

    // Reset la page à 1 quand on fait une recherche
    const handleSearch = (value) => {
        setSearchQuery(value);
        setCurrentPage(1);
    };

    // Générer les numéros de page à afficher
    const getPageNumbers = () => {
        const pages = [];
        const maxVisiblePages = 5; // Nombre maximum de pages à afficher

        if (totalPages <= maxVisiblePages) {
            // Si le nombre total de pages est inférieur ou égal au maximum, afficher toutes les pages
            for (let i = 1; i <= totalPages; i++) {
                pages.push(i);
            }
        } else {
            // Sinon, afficher une sélection de pages avec des ellipses
            if (currentPage <= 3) {
                // Début de la liste
                for (let i = 1; i <= 4; i++) pages.push(i);
                pages.push("...");
                pages.push(totalPages);
            } else if (currentPage >= totalPages - 2) {
                // Fin de la liste
                pages.push(1);
                pages.push("...");
                for (let i = totalPages - 3; i <= totalPages; i++)
                    pages.push(i);
            } else {
                // Milieu de la liste
                pages.push(1);
                pages.push("...");
                for (let i = currentPage - 1; i <= currentPage + 1; i++)
                    pages.push(i);
                pages.push("...");
                pages.push(totalPages);
            }
        }

        return pages;
    };

    if (isLoading) {
        return (
            <div className="min-h-screen bg-white px-6 py-24 sm:py-32 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <p className="text-lg font-semibold text-blue-600">
                        {t("experiment.list.loading")}
                    </p>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="min-h-screen bg-white px-6 py-24 sm:py-32 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <p className="text-lg font-semibold text-red-600">
                        {error}
                    </p>
                </div>
            </div>
        );
    }

    return (
        <div className="bg-white">
            <div className="relative isolate">
                <div className="py-12 sm:py-20">
                    <div className="mx-auto max-w-7xl px-6 lg:px-8">
                        <div className="mx-auto max-w-3xl text-center">
                            <h2 className="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                                {t("experiment.list.title")}
                            </h2>
                            <p className="mt-2 text-lg leading-8 text-gray-600">
                                {t("experiment.list.message")}
                            </p>
                        </div>

                        {/* Barre de recherche */}
                        <div className="mx-auto mt-8 max-w-2xl">
                            <div className="relative">
                                <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <MagnifyingGlassIcon className="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="text"
                                    className="block w-full rounded-md border border-gray-300 bg-white py-2 pl-10 pr-3 text-sm placeholder-gray-500 focus:border-blue-500 focus:text-gray-900 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    placeholder={t(
                                        "experiment.list.placeholder"
                                    )}
                                    value={searchQuery}
                                    onChange={(e) =>
                                        handleSearch(e.target.value)
                                    }
                                />
                            </div>
                        </div>

                        {/* Nombre de résultats */}
                        <div className="mx-auto mt-4 max-w-2xl">
                            <p className="text-sm text-gray-500">
                                {filteredExperiments.length}{" "}
                                {t("experiment.list.result")}
                                {filteredExperiments.length > 1 ? "s" : ""}{" "}
                                {t("experiment.list.find")}
                                {filteredExperiments.length > 1 ? "s" : ""}
                            </p>
                        </div>

                        {/* Liste des expérimentations */}
                        <div className="mx-auto mt-8 max-w-3xl divide-y divide-gray-200">
                            {currentExperiments.map((experiment) => (
                                <div
                                    key={experiment.id}
                                    className="py-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
                                >
                                    <div className="flex-1">
                                        <div className="flex items-center gap-4">
                                            <UserCircleIcon className="h-10 w-10 text-gray-600" />
                                            <div>
                                                <h3 className="text-lg font-semibold text-gray-900">
                                                    {experiment.name}
                                                </h3>
                                                <p className="text-sm text-gray-500">
                                                    {t("experiment.list.by")}{" "}
                                                    {experiment.creator_name}
                                                </p>
                                            </div>
                                        </div>
                                        <p className="mt-2 text-sm text-gray-600 line-clamp-2">
                                            {experiment.description}
                                        </p>
                                        <div className="mt-2 flex items-center gap-2 text-sm text-gray-500">
                                            <CheckCircleIcon className="h-5 w-5 text-green-500" />
                                            <span>
                                                {
                                                    experiment.completed_sessions_count
                                                }{" "}
                                                {t(
                                                    "experiment.list.completed_session"
                                                )}
                                            </span>
                                        </div>
                                    </div>
                                    <div className="flex flex-col sm:flex-row gap-2">
                                        <button
                                            onClick={() =>
                                                navigate(
                                                    `/experiment-detail/${experiment.id}`
                                                )
                                            }
                                            className="rounded-md px-3.5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 shadow-sm"
                                        >
                                            {t("experiment.list.more_info")}
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* Pagination */}
                        {totalPages > 1 && (
                            <div className="mt-8 flex items-center justify-center gap-1">
                                <button
                                    onClick={() =>
                                        setCurrentPage((prev) =>
                                            Math.max(prev - 1, 1)
                                        )
                                    }
                                    disabled={currentPage === 1}
                                    className={`p-2 rounded-md ${
                                        currentPage === 1
                                            ? "text-gray-400 cursor-not-allowed"
                                            : "text-gray-700 hover:bg-gray-100"
                                    }`}
                                >
                                    <ChevronLeftIcon className="h-5 w-5" />
                                </button>

                                {getPageNumbers().map((page, index) => (
                                    <button
                                        key={index}
                                        onClick={() =>
                                            typeof page === "number" &&
                                            setCurrentPage(page)
                                        }
                                        className={`px-3 py-1 rounded-md ${
                                            page === currentPage
                                                ? "bg-blue-600 text-white"
                                                : page === "..."
                                                ? "text-gray-500 cursor-default"
                                                : "text-gray-700 hover:bg-gray-100"
                                        }`}
                                        disabled={page === "..."}
                                    >
                                        {page}
                                    </button>
                                ))}

                                <button
                                    onClick={() =>
                                        setCurrentPage((prev) =>
                                            Math.min(prev + 1, totalPages)
                                        )
                                    }
                                    disabled={currentPage === totalPages}
                                    className={`p-2 rounded-md ${
                                        currentPage === totalPages
                                            ? "text-gray-400 cursor-not-allowed"
                                            : "text-gray-700 hover:bg-gray-100"
                                    }`}
                                >
                                    <ChevronRightIcon className="h-5 w-5" />
                                </button>
                            </div>
                        )}

                        {/* Message quand aucun résultat */}
                        {filteredExperiments.length === 0 && (
                            <div className="mt-8 text-center text-gray-500">
                                {t("experiment.list.no_result")}
                            </div>
                        )}
                    </div>
                </div>
            </div>
            <FloatingLanguageButton />
        </div>
    );
}

export default ExperimentList;
