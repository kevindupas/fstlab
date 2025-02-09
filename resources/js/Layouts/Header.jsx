"use client";

import React, { useState } from "react";
import { Link } from "react-router-dom";
import { useAuth } from "../Contexts/AuthContext";
import { Logo } from "../Components/Logo";
import { NavLink } from "../Components/NavLink";
import { Button } from "../Components/Button";
import { useTranslation } from "../Contexts/LanguageContext";
import { Menu, X } from "lucide-react";

const Header = () => {
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const { t } = useTranslation();
    const { user, isLoading, refreshAuth } = useAuth();

    if (isLoading) return null;

    const handleLogout = async (e) => {
        e.preventDefault();
        try {
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
            } else {
                console.error("Logout error");
            }
        } catch (error) {
            console.error("Error:", error);
        }
    };

    const toggleMenu = () => {
        setIsMenuOpen(!isMenuOpen);
    };

    const closeMenu = () => {
        setIsMenuOpen(false);
    };

    return (
        <header className="py-4 px-4 md:py-6 md:px-6 bg-white fixed w-full z-50">
            <nav className="relative z-50 flex justify-between items-center max-w-7xl mx-auto">
                <Link to="/" aria-label="Home" className="flex-shrink-0">
                    <Logo
                        className="h-12 md:h-16 w-auto"
                        desc={t("header.logo.logo_alt")}
                    />
                </Link>

                {/* Mobile Menu Button */}
                <button
                    className="md:hidden p-2 rounded-lg hover:bg-slate-100"
                    onClick={toggleMenu}
                    aria-label={isMenuOpen ? "Close menu" : "Open menu"}
                >
                    {isMenuOpen ? (
                        <X className="h-6 w-6 text-slate-600" />
                    ) : (
                        <Menu className="h-6 w-6 text-slate-600" />
                    )}
                </button>

                {/* Desktop Navigation */}
                <div className="hidden md:flex items-center justify-center gap-x-8">
                    <NavLink href="/" isReactRoute>
                        {t("header.home")}
                    </NavLink>
                    <NavLink href="/experiments" isReactRoute>
                        {t("header.experiments")}
                    </NavLink>
                    <NavLink href="/how-it-work" isReactRoute>
                        {t("header.how_it_work")}
                    </NavLink>
                    {/* <NavLink href="/changelog" isReactRoute>
                        {t("header.changelog")}
                    </NavLink> */}
                </div>

                {/* Desktop Auth Buttons */}
                <div className="hidden md:flex items-center gap-x-3">
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
                            <NavLink
                                href="/admin/login"
                                isReactRoute={false}
                                className="inline-block rounded-lg px-2 py-1 text-sm text-slate-700 hover:bg-slate-100 hover:text-slate-900"
                            >
                                {t("header.login")}
                            </NavLink>
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

                {/* Mobile Menu */}
                <div
                    className={`${
                        isMenuOpen ? "fixed" : "hidden"
                    } inset-0 top-0 left-0 w-full h-full bg-white z-40 md:hidden`}
                >
                    <div className="flex flex-col h-full p-4">
                        <div className="flex justify-between items-center mb-8">
                            <Logo
                                className="h-12 w-auto"
                                desc={t("header.logo.logo_alt")}
                            />
                            <button
                                className="p-2 rounded-lg hover:bg-slate-100"
                                onClick={closeMenu}
                                aria-label="Close menu"
                            >
                                <X className="h-6 w-6 text-slate-600" />
                            </button>
                        </div>

                        <div className="flex flex-col gap-y-4">
                            <NavLink href="/" isReactRoute onClick={closeMenu}>
                                {t("header.home")}
                            </NavLink>
                            <NavLink
                                href="/experiments"
                                isReactRoute
                                onClick={closeMenu}
                            >
                                {t("header.experiments")}
                            </NavLink>
                            <NavLink
                                href="/how-it-work"
                                isReactRoute
                                onClick={closeMenu}
                            >
                                {t("header.how_it_work")}
                            </NavLink>
                            <NavLink
                                href="/changelog"
                                isReactRoute
                                onClick={closeMenu}
                            >
                                {t("header.changelog")}
                            </NavLink>
                        </div>

                        <div className="mt-auto flex flex-col gap-y-4">
                            {user ? (
                                <>
                                    <NavLink
                                        href="/admin"
                                        className="w-full text-center rounded-full py-2 px-4 text-sm font-semibold bg-blue-600 text-white hover:bg-blue-500"
                                        onClick={closeMenu}
                                    >
                                        {t("header.admin_panel")}
                                    </NavLink>
                                    <Button onClick={handleLogout} color="red">
                                        {t("header.logout")}
                                    </Button>
                                </>
                            ) : (
                                <>
                                    <NavLink
                                        href="/admin/login"
                                        isReactRoute={false}
                                        className="w-full text-center rounded-lg py-2 text-sm text-slate-700 hover:bg-slate-100"
                                        onClick={closeMenu}
                                    >
                                        {t("header.login")}
                                    </NavLink>
                                    <NavLink
                                        href="/admin/register"
                                        className="w-full text-center rounded-full py-2 px-4 text-sm font-semibold bg-blue-600 text-white hover:bg-blue-500"
                                        isReactRoute={false}
                                        onClick={closeMenu}
                                    >
                                        {t("header.register")}
                                    </NavLink>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            </nav>
        </header>
    );
};

export default Header;
