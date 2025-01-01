"use client";

import React from "react";
import { Link } from "react-router-dom";
import { useAuth } from "../Contexts/AuthContext";
import { Logo } from "../Components/Logo";
import { NavLink } from "../Components/NavLink";
import { Button } from "../Components/Button";
import { Container } from "../Components/Container";
import { useTranslation } from "../Contexts/LanguageContext";

function Header() {
    const { t } = useTranslation();
    const { user, isLoading, refreshAuth } = useAuth();

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
                await refreshAuth();

                // window.location.href = "/";
            } else {
                console.error("Erreur lors de la déconnexion");
            }
        } catch (error) {
            console.error("Erreur:", error);
        }
    };

    return (
        <header className="py-10 px-6">
            <nav className="relative z-50 flex justify-between items-center max-w-[100rem] mx-auto">
                {/* Logo collé à gauche */}
                <Link href="#" aria-label="Home">
                    <Logo className="h-10 w-auto" />
                </Link>

                {/* Navigation centrée */}
                <div className="hidden md:flex justify-center gap-x-12 absolute left-1/2 -translate-x-1/2">
                    <NavLink href="/" isReactRoute>
                        {t("header.home")}
                    </NavLink>
                    <NavLink href="/experiments" isReactRoute>
                        {t("header.experiments")}
                    </NavLink>
                    <NavLink href="/how-it-work" isReactRoute>
                        {t("header.how_it_work")}
                    </NavLink>
                    <NavLink href="/changelog" isReactRoute>
                        {t("header.changelog")}
                    </NavLink>
                </div>

                {/* Boutons collés à droite */}
                <div className="flex items-center gap-x-3">
                    {user ? (
                        <>
                            <NavLink
                                href="/admin"
                                className="group inline-flex items-center justify-center rounded-full py-2 px-4 text-sm font-semibold focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 bg-blue-600 text-white hover:text-slate-100 hover:bg-blue-500 active:bg-blue-800 active:text-blue-100 focus-visible:outline-blue-600"
                            >
                                {t("header.admin_panel")}
                            </NavLink>
                            <Button onClick={handleLogout} color="red">
                                {t("header.logout")}
                            </Button>
                        </>
                    ) : (
                        <>
                            <div className="hidden md:block">
                                <NavLink
                                    href="/admin/login"
                                    isReactRoute={false}
                                    className="inline-block rounded-lg px-2 py-1 text-sm text-slate-700 hover:bg-slate-100 hover:text-slate-900"
                                >
                                    {t("header.login")}
                                </NavLink>
                            </div>
                            <NavLink
                                href="/admin/register"
                                className="group inline-flex items-center justify-center rounded-full py-2 px-4 text-sm font-semibold focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 bg-blue-600 text-white hover:text-slate-100 hover:bg-blue-500 active:bg-blue-800 active:text-blue-100 focus-visible:outline-blue-600"
                                isReactRoute={false}
                            >
                                {t("header.register")}
                            </NavLink>
                        </>
                    )}
                </div>
            </nav>
        </header>
    );
}

export default Header;
