import React from "react";
import { GiftIcon, BellIcon, UserGroupIcon, BeakerIcon, ShieldCheckIcon, LanguageIcon } from "@heroicons/react/24/outline";

function Changelog() {
    const releases = [
        {
            version: "1.0.0",
            date: "14 Décembre 2024",
            description: "Première version stable de la plateforme d'expérimentation",
            categories: [
                {
                    title: "Système d'Authentification et Gestion des Utilisateurs",
                    icon: <UserGroupIcon className="w-6 h-6" />,
                    features: [
                        "Inscription avec vérification ORCID et affiliation universitaire",
                        "Système d'approbation des nouveaux comptes par le superviseur",
                        "Notifications par email pour les étapes d'inscription et d'approbation",
                        "Gestion des rôles : Superviseur, Expérimentateur Principal, Expérimentateur Secondaire",
                        "Système de bannissement avec justification et notifications",
                        "Page de contact administrateur pour les demandes spéciales"
                    ]
                },
                {
                    title: "Gestion des Expériences",
                    icon: <BeakerIcon className="w-6 h-6" />,
                    features: [
                        "Création d'expériences avec support multimédia (images et sons)",
                        "Personnalisation des paramètres d'interaction",
                        "Système de DOI unique pour chaque expérience",
                        "Mode test avec visualisation dans How It Works",
                        "Gestion des documents complémentaires",
                        "Interface de contrôle des sessions"
                    ]
                },
                {
                    title: "Suivi et Analyse",
                    icon: <ShieldCheckIcon className="w-6 h-6" />,
                    features: [
                        "Tableau de bord détaillé des sessions",
                        "Suivi des interactions participants",
                        "Export des données au format CSV",
                        "Visualisation des groupes et interactions",
                        "Logs d'activité détaillés",
                        "Statistiques d'utilisation"
                    ]
                },
                {
                    title: "Interface Participant",
                    icon: <GiftIcon className="w-6 h-6" />,
                    features: [
                        "Interface intuitive de classement",
                        "Gestion optimisée des sons et images",
                        "Système de groupes (C1, C2, etc.)",
                        "Affichage des consignes",
                        "Retour visuel des interactions",
                        "Compatibilité multi-appareils"
                    ]
                },
                {
                    title: "Notifications et Communications",
                    icon: <BellIcon className="w-6 h-6" />,
                    features: [
                        "Système de notifications par email",
                        "Communication superviseur-utilisateurs",
                        "Alertes de bannissement",
                        "Notifications de changements de statut",
                        "Système de demandes d'accès"
                    ]
                }
            ]
        }
    ];

    const upcomingFeatures = {
        version: "1.0.1",
        title: "Fonctionnalités à venir",
        icon: <LanguageIcon className="w-6 h-6" />,
        features: [
            "Support multilingue (Français, Anglais, Espagnol)",
            "Système de demande d'accès aux expériences",
            "Communication directe superviseur-expérimentateur",
            "Améliorations de l'interface utilisateur"
        ]
    };

    return (
        <div className="min-h-screen bg-gradient-to-b from-white to-gray-50 py-12 px-4 mb-12">
            <div className="max-w-7xl mx-auto">
                <div className="text-center mb-12">
                    <h1 className="text-4xl font-bold text-gray-900 mb-4">
                        Journal des Modifications
                    </h1>
                    <p className="text-lg text-gray-600">
                        Suivez l'évolution de notre plateforme d'expérimentation
                    </p>
                </div>

                {releases.map((release, index) => (
                    <div key={index} className="mb-12">
                        <div className="flex items-center gap-4 mb-8">
                            <h2 className="text-2xl font-semibold text-gray-900">
                                Version {release.version}
                            </h2>
                            <span className="text-sm bg-blue-100 text-blue-800 px-3 py-1 rounded-full -mt-3.5">
                                {release.date}
                            </span>
                        </div>

                        <p className="text-lg text-gray-700 mb-8">
                            {release.description}
                        </p>

                        <div className="grid gap-8 md:grid-cols-2">
                            {release.categories.map((category, categoryIndex) => (
                                <div key={categoryIndex} className="bg-white rounded-xl shadow-sm p-6">
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="text-blue-600">
                                            {category.icon}
                                        </div>
                                        <h3 className="text-xl font-semibold text-gray-900">
                                            {category.title}
                                        </h3>
                                    </div>
                                    <ul className="space-y-3">
                                        {category.features.map((feature, featureIndex) => (
                                            <li key={featureIndex} className="flex items-start gap-2">
                                                <span className="text-blue-600">•</span>
                                                <span className="text-gray-600">{feature}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            ))}
                        </div>
                    </div>
                ))}

                <div className="mt-16 bg-blue-400 rounded-xl shadow-lg p-8">
                    <div className="flex items-center gap-4 mb-6">
                        <div className="text-white -mt-4">
                            {upcomingFeatures.icon}
                        </div>
                        <div className="flex items-center gap-4">
                            <h2 className="text-2xl font-semibold text-white">
                                {upcomingFeatures.title}
                            </h2>
                            <span className="text-sm bg-blue-300 text-white px-3 py-1 rounded-full -mt-4 inline-block">
                                Version {upcomingFeatures.version}
                            </span>
                        </div>
                    </div>
                    <ul className="grid gap-4 md:grid-cols-2">
                        {upcomingFeatures.features.map((feature, index) => (
                            <li key={index} className="flex items-start gap-2 text-white">
                                <span>•</span>
                                <span>{feature}</span>
                            </li>
                        ))}
                    </ul>
                </div>
            </div>
        </div>
    );
}

export default Changelog;
