import preset from "../../../../vendor/filament/filament/tailwind.config.preset";
import forms from "@tailwindcss/forms";
import headlessui from "@headlessui/tailwindcss";

export default {
    presets: [preset],
    content: [
        "./app/Filament/**/*.php",
        "./resources/views/filament/**/*.blade.php",
        "./vendor/filament/**/*.blade.php",
        './vendor/bezhansalleh/filament-language-switch/resources/views/language-switch.blade.php',
    ],
    plugins: [forms, headlessui],
};
