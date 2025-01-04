import React, { useMemo } from "react";
import { useTranslation } from "../Contexts/LanguageContext";

function Footer() {
    const { t } = useTranslation();

    const dates = useMemo(() => {
        const currentYear = new Date().getFullYear();
        return {
            start: currentYear - 1,
            current: currentYear,
        };
    }, []);

    return (
        <footer className="md:fixed md:bottom-0 md:inset-x-0 text-center py-2 px-4 bg-slate-100">
            <small className="text-gray-500">
                {t("footer.copyright")} {dates.start} {" - "} {dates.current} ·{" "}
                <a
                    href="https://synesthesies.3rgo.tech/legal"
                    className="hover:text-gray-700 transition-colors"
                >
                    {t("footer.legal_notices")}
                </a>{" "}
                ·{" "}
                <a
                    href="https://synesthesies.3rgo.tech/privacy"
                    className="hover:text-gray-700 transition-colors"
                >
                    {t("footer.privacy_policy")}
                </a>
            </small>
        </footer>
    );
}

export default Footer;
