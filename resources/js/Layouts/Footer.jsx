import React from "react";

function Footer() {
    return (
        <footer class="md:fixed md:bottom-0 md:inset-x-0 text-center py-2 px-4 bg-slate-100">
            <small class="text-gray-500">
                Copyright © Université Toulouse 2 Jean Jaurès 2024-2025 ·{" "}
                <a href="https://synesthesies.3rgo.tech/legal">
                    Mentions Légales
                </a>{" "}
                ·{" "}
                <a href="https://synesthesies.3rgo.tech/privacy">
                    Politique de Confidentialité
                </a>
            </small>
        </footer>
    );
}

export default Footer;
