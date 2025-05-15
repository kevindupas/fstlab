import React, { useMemo } from "react";
import { useTranslation } from "../Contexts/LanguageContext";
import {
    GiftIcon,
    BellIcon,
    UserGroupIcon,
    BeakerIcon,
    ShieldCheckIcon,
    LanguageIcon,
} from "@heroicons/react/24/outline";
import FloatingLanguageButton from "../Components/FloatingLanguageButton";

function Changelog() {
    const { t } = useTranslation();

    const releaseData = useMemo(
        () => [
            {
                version: "1.0.1",
                date: t("changelog.version_two.date"),
                description: t("changelog.version_two.description"),
                categories: [
                    {
                        title: t("changelog.version_two.categories.ui.title"),
                        icon: <LanguageIcon className="w-6 h-6" />,
                        features: [
                            t("changelog.version_two.categories.ui.features.0"),
                            t("changelog.version_two.categories.ui.features.1"),
                            t("changelog.version_two.categories.ui.features.2"),
                            t("changelog.version_two.categories.ui.features.3"),
                            t("changelog.version_two.categories.ui.features.4"),
                            t("changelog.version_two.categories.ui.features.5"),
                            t("changelog.version_two.categories.ui.features.6"),
                            t("changelog.version_two.categories.ui.features.7"),
                        ],
                    },
                    {
                        title: t(
                            "changelog.version_two.categories.fixes.title"
                        ),
                        icon: <ShieldCheckIcon className="w-6 h-6" />,
                        features: [
                            t(
                                "changelog.version_two.categories.fixes.features.0"
                            ),
                            t(
                                "changelog.version_two.categories.fixes.features.1"
                            ),
                            t(
                                "changelog.version_two.categories.fixes.features.2"
                            ),
                            t(
                                "changelog.version_two.categories.fixes.features.3"
                            ),
                            t(
                                "changelog.version_two.categories.fixes.features.4"
                            ),
                            t(
                                "changelog.version_two.categories.fixes.features.5"
                            ),
                        ],
                    },
                ],
            },
            {
                version: "1.0.0",
                date: t("changelog.version_one.date"),
                description: t("changelog.version_one.description"),
                categories: [
                    {
                        title: t(
                            "changelog.version_one.categories.auth_users.title"
                        ),
                        icon: <UserGroupIcon className="w-6 h-6" />,
                        features: [
                            t(
                                "changelog.version_one.categories.auth_users.features.0"
                            ),
                            t(
                                "changelog.version_one.categories.auth_users.features.1"
                            ),
                            t(
                                "changelog.version_one.categories.auth_users.features.2"
                            ),
                            t(
                                "changelog.version_one.categories.auth_users.features.3"
                            ),
                            t(
                                "changelog.version_one.categories.auth_users.features.4"
                            ),
                            t(
                                "changelog.version_one.categories.auth_users.features.5"
                            ),
                        ],
                    },
                    {
                        title: t(
                            "changelog.version_one.categories.experiments.title"
                        ),
                        icon: <BeakerIcon className="w-6 h-6" />,
                        features: [
                            t(
                                "changelog.version_one.categories.experiments.features.0"
                            ),
                            t(
                                "changelog.version_one.categories.experiments.features.1"
                            ),
                            t(
                                "changelog.version_one.categories.experiments.features.2"
                            ),
                            t(
                                "changelog.version_one.categories.experiments.features.3"
                            ),
                            t(
                                "changelog.version_one.categories.experiments.features.4"
                            ),
                            t(
                                "changelog.version_one.categories.experiments.features.5"
                            ),
                        ],
                    },
                    {
                        title: t(
                            "changelog.version_one.categories.monitoring.title"
                        ),
                        icon: <ShieldCheckIcon className="w-6 h-6" />,
                        features: [
                            t(
                                "changelog.version_one.categories.monitoring.features.0"
                            ),
                            t(
                                "changelog.version_one.categories.monitoring.features.1"
                            ),
                            t(
                                "changelog.version_one.categories.monitoring.features.2"
                            ),
                            t(
                                "changelog.version_one.categories.monitoring.features.3"
                            ),
                            t(
                                "changelog.version_one.categories.monitoring.features.4"
                            ),
                            t(
                                "changelog.version_one.categories.monitoring.features.5"
                            ),
                        ],
                    },
                    {
                        title: t(
                            "changelog.version_one.categories.participant.title"
                        ),
                        icon: <GiftIcon className="w-6 h-6" />,
                        features: [
                            t(
                                "changelog.version_one.categories.participant.features.0"
                            ),
                            t(
                                "changelog.version_one.categories.participant.features.1"
                            ),
                            t(
                                "changelog.version_one.categories.participant.features.2"
                            ),
                            t(
                                "changelog.version_one.categories.participant.features.3"
                            ),
                            t(
                                "changelog.version_one.categories.participant.features.4"
                            ),
                            t(
                                "changelog.version_one.categories.participant.features.5"
                            ),
                        ],
                    },
                    {
                        title: t(
                            "changelog.version_one.categories.notifications.title"
                        ),
                        icon: <BellIcon className="w-6 h-6" />,
                        features: [
                            t(
                                "changelog.version_one.categories.notifications.features.0"
                            ),
                            t(
                                "changelog.version_one.categories.notifications.features.1"
                            ),
                            t(
                                "changelog.version_one.categories.notifications.features.2"
                            ),
                            t(
                                "changelog.version_one.categories.notifications.features.3"
                            ),
                            t(
                                "changelog.version_one.categories.notifications.features.4"
                            ),
                        ],
                    },
                ],
            },
        ],
        [t]
    );

    const upcomingFeatures = useMemo(
        () => ({
            version: t("changelog.upcoming_version.version"),
            title: t("changelog.upcoming_version.title"),
            date: t("changelog.upcoming_version.date"),
            icon: <BeakerIcon className="w-6 h-6" />,
            features: [
                t("changelog.upcoming_version.features.0"),
                t("changelog.upcoming_version.features.1"),
                t("changelog.upcoming_version.features.2"),
                t("changelog.upcoming_version.features.3"),
                t("changelog.upcoming_version.features.4"),
                t("changelog.upcoming_version.features.5"),
                t("changelog.upcoming_version.features.6"),
                t("changelog.upcoming_version.features.7"),
            ],
            fixes: [
                t("changelog.upcoming_version.fixes.0"),
                t("changelog.upcoming_version.fixes.1"),
                t("changelog.upcoming_version.fixes.2"),
            ],
        }),
        [t]
    );

    return (
        <div className="min-h-screen bg-gradient-to-b from-white to-gray-50 py-12 px-4 mb-12 mt-28">
            <div className="max-w-7xl mx-auto">
                <div className="text-center mb-12">
                    <h1 className="text-4xl font-bold text-gray-900 mb-4">
                        {t("changelog.title")}
                    </h1>
                    <p className="text-lg text-gray-600">
                        {t("changelog.subtitle")}
                    </p>
                </div>

                <div className="mt-16 bg-blue-400 rounded-xl shadow-lg p-8">
                    <div className="flex items-center gap-4 mb-6">
                        <div className="text-white -mt-4">
                            {upcomingFeatures.icon}
                        </div>
                        <div className="flex items-center gap-4">
                            <h2 className="text-2xl font-semibold text-white">
                                {t("changelog.upcoming_features")}
                            </h2>
                            <span className="text-sm bg-blue-300 text-white px-3 py-1 rounded-full -mt-4 inline-block">
                                {t("changelog.version")}{" "}
                                {upcomingFeatures.version} -{" "}
                                {upcomingFeatures.date}
                            </span>
                        </div>
                    </div>
                    <div className="space-y-6">
                        <div>
                            <h3 className="text-xl font-semibold text-white mb-4">
                                {t("changelog.new_features")}
                            </h3>
                            <ul className="grid gap-4 md:grid-cols-2">
                                {upcomingFeatures.features.map(
                                    (feature, index) => (
                                        <li
                                            key={index}
                                            className="flex items-start gap-2 text-white"
                                        >
                                            <span>•</span>
                                            <span>{feature}</span>
                                        </li>
                                    )
                                )}
                            </ul>
                        </div>
                        {upcomingFeatures.fixes.length > 0 && (
                            <div>
                                <h3 className="text-xl font-semibold text-white mb-4">
                                    {t("changelog.fixes")}
                                </h3>
                                <ul className="grid gap-4 md:grid-cols-2">
                                    {upcomingFeatures.fixes.map(
                                        (fix, index) => (
                                            <li
                                                key={index}
                                                className="flex items-start gap-2 text-white"
                                            >
                                                <span>•</span>
                                                <span>{fix}</span>
                                            </li>
                                        )
                                    )}
                                </ul>
                            </div>
                        )}
                    </div>
                </div>

                {releaseData.map((release, index) => (
                    <div key={index} className="py-12">
                        <div className="flex items-center gap-4 mb-8">
                            <h2 className="text-2xl font-semibold text-gray-900">
                                {t("changelog.version")} {release.version}
                            </h2>
                            <span className="text-sm bg-blue-100 text-blue-800 px-3 py-1 rounded-full -mt-3.5">
                                {release.date}
                            </span>
                        </div>

                        <p className="text-lg text-gray-700 mb-8">
                            {release.description}
                        </p>

                        <div className="grid gap-8 md:grid-cols-2">
                            {release.categories.map(
                                (category, categoryIndex) => (
                                    <div
                                        key={categoryIndex}
                                        className="bg-white rounded-xl shadow-sm p-6"
                                    >
                                        <div className="flex items-center gap-3 mb-4">
                                            <div className="text-blue-600">
                                                {category.icon}
                                            </div>
                                            <h3 className="text-xl font-semibold text-gray-900">
                                                {category.title}
                                            </h3>
                                        </div>
                                        <ul className="space-y-3">
                                            {category.features.map(
                                                (feature, featureIndex) => (
                                                    <li
                                                        key={featureIndex}
                                                        className="flex items-start gap-2"
                                                    >
                                                        <span className="text-blue-600">
                                                            •
                                                        </span>
                                                        <span className="text-gray-600">
                                                            {feature}
                                                        </span>
                                                    </li>
                                                )
                                            )}
                                        </ul>
                                    </div>
                                )
                            )}
                        </div>
                    </div>
                ))}
            </div>
            <FloatingLanguageButton />
        </div>
    );
}

export default Changelog;
