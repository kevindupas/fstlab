import {
    CheckCircleIcon,
    MagnifyingGlassIcon,
    UserCircleIcon,
    ChevronLeftIcon,
    ChevronRightIcon,
    AdjustmentsHorizontalIcon,
    BeakerIcon,
    BookmarkIcon,
} from "@heroicons/react/24/outline";
import React, { useState, useMemo, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useExperiments } from "../Contexts/ExperimentsContext";
import { useTranslation } from "../Contexts/LanguageContext";
import { useAuth } from "../Contexts/AuthContext";
import FloatingLanguageButton from "../Components/FloatingLanguageButton";
import clsx from "clsx";

const ITEMS_PER_PAGE = 5;

function ExperimentList() {
    const { t } = useTranslation();
    const navigate = useNavigate();
    const { experiments, isLoading, error } = useExperiments();
    const { user } = useAuth();
    const [searchQuery, setSearchQuery] = useState("");
    const [currentPage, setCurrentPage] = useState(1);
    const [showFilters, setShowFilters] = useState(false);
    const [selectedType, setSelectedType] = useState(null);
    const [sessionsRange, setSessionsRange] = useState({ min: 0, max: 0 });
    const [maxPossibleSessions, setMaxPossibleSessions] = useState(0);

    useEffect(() => {
        if (experiments.length) {
            const max = Math.max(
                ...experiments.map((e) => e.completed_sessions_count)
            );
            setMaxPossibleSessions(max);
            setSessionsRange((prev) => ({ ...prev, max }));
        }
    }, [experiments]);

    const filterTypes = [
        {
            id: "image",
            label: t("experiment.detail.type_image"),
            color: "blue",
        },
        {
            id: "sound",
            label: t("experiment.detail.type_sound"),
            color: "green",
        },
        {
            id: "image_sound",
            label: t("experiment.detail.type_image_sound"),
            color: "purple",
        },
    ];

    // Filtrer les expériences en fonction de la recherche et des filtres
    const filteredExperiments = useMemo(() => {
        return experiments.filter((experiment) => {
            const matchesSearch =
                experiment.name
                    .toLowerCase()
                    .includes(searchQuery.toLowerCase()) ||
                experiment.description
                    .toLowerCase()
                    .includes(searchQuery.toLowerCase()) ||
                experiment.creator_name
                    .toLowerCase()
                    .includes(searchQuery.toLowerCase());

            const matchesType =
                !selectedType || experiment.type === selectedType;
            const matchesSessions =
                experiment.completed_sessions_count >= sessionsRange.min &&
                experiment.completed_sessions_count <= sessionsRange.max;

            return matchesSearch && matchesType && matchesSessions;
        });
    }, [experiments, searchQuery, selectedType, sessionsRange]);

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

    // Handler pour le changement des sessions
    const handleSessionRangeChange = (value, type) => {
        setSessionsRange((prev) => ({
            ...prev,
            [type]: parseInt(value) || 0,
        }));
    };

    // Générer les numéros de page à afficher
    const getPageNumbers = () => {
        const pages = [];
        const maxVisiblePages = 5;

        if (totalPages <= maxVisiblePages) {
            for (let i = 1; i <= totalPages; i++) {
                pages.push(i);
            }
        } else {
            if (currentPage <= 3) {
                for (let i = 1; i <= 4; i++) pages.push(i);
                pages.push("...");
                pages.push(totalPages);
            } else if (currentPage >= totalPages - 2) {
                pages.push(1);
                pages.push("...");
                for (let i = totalPages - 3; i <= totalPages; i++)
                    pages.push(i);
            } else {
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

                        {/* Barre de recherche avec filtres */}
                        <div className="mx-auto mt-8 max-w-2xl">
                            <div className="flex gap-4">
                                <div className="relative flex-1">
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
                                <button
                                    onClick={() => setShowFilters(!showFilters)}
                                    className={clsx(
                                        "inline-flex items-center rounded-md px-4 py-2 text-sm font-semibold transition-colors",
                                        showFilters
                                            ? "bg-indigo-600 text-white hover:bg-indigo-500"
                                            : "bg-white text-gray-700 border border-gray-300 hover:bg-gray-50"
                                    )}
                                >
                                    <AdjustmentsHorizontalIcon className="h-5 w-5 mr-2" />
                                    {t("experiment.list.filters")}
                                </button>
                            </div>

                            {/* Panneau de filtres */}
                            {showFilters && (
                                <div className="mt-4 p-4 bg-white rounded-lg border border-gray-200 space-y-6">
                                    {/* Filtres de type */}
                                    <div className="space-y-2">
                                        <label className="block text-sm font-medium text-gray-700">
                                            {t("experiment.list.type_filter")}
                                        </label>
                                        <div className="flex flex-wrap gap-2">
                                            {filterTypes.map((type) => (
                                                <button
                                                    key={type.id}
                                                    onClick={() =>
                                                        setSelectedType(
                                                            selectedType ===
                                                                type.id
                                                                ? null
                                                                : type.id
                                                        )
                                                    }
                                                    className={clsx(
                                                        "inline-flex items-center rounded-full px-3 py-1 text-sm font-medium transition-colors",
                                                        selectedType === type.id
                                                            ? `bg-${type.color}-100 text-${type.color}-700`
                                                            : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                                                    )}
                                                >
                                                    {type.label}
                                                </button>
                                            ))}
                                        </div>
                                    </div>

                                    {/* Filtre de sessions */}
                                    <div className="space-y-2">
                                        <label className="block text-sm font-medium text-gray-700">
                                            {t(
                                                "experiment.list.sessions_filter"
                                            )}
                                        </label>
                                        <div className="flex gap-4 items-center">
                                            <div>
                                                <label className="block text-xs text-gray-500 mb-1">
                                                    Min
                                                </label>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    max={sessionsRange.max}
                                                    value={sessionsRange.min}
                                                    onChange={(e) =>
                                                        handleSessionRangeChange(
                                                            e.target.value,
                                                            "min"
                                                        )
                                                    }
                                                    className="w-24 rounded-md border-gray-300 text-sm"
                                                />
                                            </div>
                                            <div>
                                                <label className="block text-xs text-gray-500 mb-1">
                                                    Max
                                                </label>
                                                <input
                                                    type="number"
                                                    min={sessionsRange.min}
                                                    max={maxPossibleSessions}
                                                    value={sessionsRange.max}
                                                    onChange={(e) =>
                                                        handleSessionRangeChange(
                                                            e.target.value,
                                                            "max"
                                                        )
                                                    }
                                                    className="w-24 rounded-md border-gray-300 text-sm"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            )}
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
                        <div className="mx-auto mt-8 max-w-3xl space-y-6">
                            {currentExperiments.map((experiment) => (
                                <div
                                    key={experiment.id}
                                    className="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-6"
                                >
                                    <div className="flex flex-col gap-6">
                                        {/* En-tête avec avatar et titre */}
                                        <div className="flex flex-col sm:flex-row gap-4">
                                            <div className="flex-1 min-w-0">
                                                <h3 className="text-xl font-semibold text-gray-900 break-words">
                                                    {experiment.name}
                                                </h3>
                                                <div className="mt-2 flex flex-wrap items-center gap-3">
                                                    <span className="text-sm font-medium text-gray-500">
                                                        {t(
                                                            "experiment.list.by"
                                                        )}
                                                        <span className="ml-1 font-semibold text-indigo-600">
                                                            {
                                                                experiment.creator_name
                                                            }
                                                        </span>
                                                    </span>

                                                    {user &&
                                                        experiment.created_by ===
                                                            user.id && (
                                                            <span className="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium">
                                                                <BeakerIcon className="h-3 w-3" />
                                                                {t(
                                                                    "experiment.list.your_creation"
                                                                )}
                                                            </span>
                                                        )}

                                                    {experiment.original_creator_name && (
                                                        <span className="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-purple-50 text-purple-700 text-xs font-medium">
                                                            <BookmarkIcon className="h-3 w-3" />
                                                            {t(
                                                                "experiment.list.duplicated_from",
                                                                {
                                                                    name: experiment.original_creator_name,
                                                                }
                                                            )}
                                                        </span>
                                                    )}

                                                    <span
                                                        className={clsx(
                                                            "inline-flex items-center rounded-full px-3 py-1 text-sm font-medium",
                                                            {
                                                                "bg-blue-100 text-blue-700":
                                                                    experiment.type ===
                                                                    "image",
                                                                "bg-green-100 text-green-700":
                                                                    experiment.type ===
                                                                    "sound",
                                                                "bg-purple-100 text-purple-700":
                                                                    experiment.type ===
                                                                    "image_sound",
                                                            }
                                                        )}
                                                    >
                                                        {experiment.type ===
                                                            "image" &&
                                                            t(
                                                                "experiment.detail.type_image"
                                                            )}
                                                        {experiment.type ===
                                                            "sound" &&
                                                            t(
                                                                "experiment.detail.type_sound"
                                                            )}
                                                        {experiment.type ===
                                                            "image_sound" &&
                                                            t(
                                                                "experiment.detail.type_image_sound"
                                                            )}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {/* Description */}
                                        <div className="prose prose-sm max-w-none text-gray-600">
                                            {experiment.description}
                                        </div>

                                        {/* Actions */}
                                        <div className="flex flex-col sm:flex-row gap-4 justify-end sm:items-center">
                                            <div className="flex items-center gap-2 px-4 py-2 bg-green-50 rounded-lg whitespace-nowrap">
                                                <CheckCircleIcon className="h-5 w-5 text-green-500" />
                                                <span className="text-sm font-medium text-green-700">
                                                    {
                                                        experiment.completed_sessions_count
                                                    }{" "}
                                                    {t(
                                                        "experiment.list.completed_session"
                                                    )}
                                                </span>
                                            </div>
                                            <button
                                                onClick={() =>
                                                    navigate(
                                                        `/experiment-detail/${experiment.id}`
                                                    )
                                                }
                                                className="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-500 transition-colors shadow-sm hover:shadow whitespace-nowrap"
                                            >
                                                {t("experiment.list.more_info")}
                                                <ChevronRightIcon className="ml-2 h-4 w-4" />
                                            </button>
                                        </div>
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
