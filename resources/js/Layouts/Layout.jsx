import React from "react";
import { useLocation } from "react-router-dom";
import Header from "./Header";
import clsx from "clsx";
import DeviceOrientationCheck from "../Utils/DeviceOrientationCheck";
import Footer from "./Footer";

export function Layout({ children }) {
    const location = useLocation();
    const showHeader = ["/", "/two", "/experiments/"].includes(
        location.pathname
    );

    return (
        <DeviceOrientationCheck>
            <div
                className={clsx(
                    "min-h-full",
                    "bg-white",
                    "antialiased",
                    "font-inter"
                )}
            >
                {showHeader && <Header />}
                <div className="flex min-h-full flex-col">{children}</div>
                {showHeader && <Footer />}
            </div>
        </DeviceOrientationCheck>
    );
}
