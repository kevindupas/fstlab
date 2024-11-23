"use client";

import { Bars3Icon, BeakerIcon } from "@heroicons/react/24/outline";
import React, { useState } from "react";
import { Link } from "react-router-dom";
import { useAuth } from "../Contexts/AuthContext";

// Modifiez le tableau de navigation pour indiquer quelles routes sont React

function Header() {
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const { user, isAuthenticated, isLoading, refreshAuth } = useAuth();

    if (isLoading) {
        return null;
    }

    const handleLogout = async (e) => {
        e.preventDefault();

        try {
            // Récupérer le token CSRF
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

            const response = await fetch("/admin/logout", {
                method: "POST",
                credentials: "include",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
            });

            if (response.ok) {
                // Rafraîchir l'état d'authentification
                await refreshAuth();
                // Rediriger vers la page d'accueil
                window.location.href = "/";
            } else {
                console.error("Erreur lors de la déconnexion");
            }
        } catch (error) {
            console.error("Erreur:", error);
        }
    };

    // Composant NavigationLink qui choisit entre Link et a selon le type de route
    const NavigationLink = ({ item, className }) => {
        return item.isReactRoute ? (
            <Link to={item.href} className={className}>
                {item.name}
            </Link>
        ) : (
            <a href={item.href} className={className}>
                {item.name}
            </a>
        );
    };

    return (
        <header className="absolute inset-x-0 top-0 z-50">
            <nav
                className="flex items-center justify-between p-6 lg:px-8"
                aria-label="Global"
            >
                <div className="flex lg:flex-1">
                    <Link to="/" className="-m-1.5 p-1.5">
                        <span className="sr-only">
                            Plateforme d'Expérimentation
                        </span>
                        <BeakerIcon className="h-8 w-auto text-indigo-600" />
                    </Link>
                </div>

                {/* Bouton menu mobile */}
                <div className="flex lg:hidden">
                    <button
                        type="button"
                        onClick={() => setMobileMenuOpen(true)}
                        className="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700"
                    >
                        <span className="sr-only">Ouvrir le menu</span>
                        <Bars3Icon className="h-6 w-6" aria-hidden="true" />
                    </button>
                </div>

                {/* Navigation desktop */}
                <div className="hidden lg:flex lg:gap-x-12">
                    {[
                        {
                            name: "Accueil",
                            href: "/",
                            isReactRoute: true,
                        },
                        {
                            name: "Expériences",
                            href: "/experiments",
                            isReactRoute: true,
                        },
                    ].map((item, index) => (
                        <NavigationLink
                            key={index}
                            item={item}
                            className="text-sm font-semibold leading-6 text-gray-900"
                        />
                    ))}
                </div>

                {/* Authentification desktop */}
                <div className="hidden lg:flex lg:flex-1 lg:justify-end">
                    {user ? (
                        <div className="flex items-center gap-4">
                            <span className="text-sm text-gray-600">
                                {user.name}
                            </span>
                            <button
                                onClick={handleLogout}
                                className="text-sm font-semibold leading-6 text-gray-900"
                            >
                                Déconnexion{" "}
                                <span aria-hidden="true">&rarr;</span>
                            </button>
                        </div>
                    ) : (
                        <a
                            href="/admin/login"
                            className="text-sm font-semibold leading-6 text-gray-900"
                        >
                            Connexion <span aria-hidden="true">&rarr;</span>
                        </a>
                    )}
                </div>
            </nav>
        </header>
    );
}

export default Header;
