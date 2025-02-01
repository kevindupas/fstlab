import { UAParser } from "ua-parser-js";

export const getSystemInfo = () => {
    const parser = new UAParser();
    const result = parser.getResult();

    return {
        browser: `${result.browser.name} ${result.browser.version}`,
        device_type: result.device.type || "desktop",
        operating_system: `${result.os.name} ${result.os.version}`,
        screen_width: window.innerWidth,
        screen_height: window.innerHeight,
        is_dark: window.matchMedia("(prefers-color-scheme: dark)").matches,
    };
};