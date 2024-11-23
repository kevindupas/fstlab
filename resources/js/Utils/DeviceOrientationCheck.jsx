import {
    CornerDownLeft,
    CornerUpRight,
    Smartphone,
    Tablet,
} from "lucide-react";
import React, { useEffect, useState } from "react";
import UAParser from "ua-parser-js";

const DeviceOrientationCheck = ({ children }) => {
    const [deviceInfo, setDeviceInfo] = useState({
        type: null,
        isLandscape: true,
    });
    const [showWarning, setShowWarning] = useState(false);

    useEffect(() => {
        const parser = new UAParser();
        const result = parser.getResult();

        const checkOrientation = () => {
            const isLandscape = window.innerWidth > window.innerHeight;
            const deviceType = result.device.type || "desktop";

            setDeviceInfo({ type: deviceType, isLandscape });

            // Afficher l'avertissement si :
            // 1. C'est un mobile
            // 2. C'est une tablette en mode portrait
            setShowWarning(
                deviceType === "mobile" ||
                    (deviceType === "tablet" && !isLandscape)
            );
        };

        checkOrientation();
        window.addEventListener("resize", checkOrientation);
        window.addEventListener("orientationchange", checkOrientation);

        return () => {
            window.removeEventListener("resize", checkOrientation);
            window.removeEventListener("orientationchange", checkOrientation);
        };
    }, []);

    if (!showWarning) return children;

    return (
        <div className="fixed inset-0 bg-white z-50 flex items-center justify-center">
            <div className="text-center p-6">
                {deviceInfo.type === "mobile" ? (
                    <div className="flex flex-col items-center space-y-4">
                        <Smartphone className="w-16 h-16 text-red-500 mb-2" />
                        <h2 className="text-xl font-bold text-gray-900">
                            Desktop Required
                        </h2>
                        <p className="text-gray-600 max-w-sm">
                            This experiment requires a desktop computer or a
                            tablet in landscape mode. Please switch to a
                            compatible device to continue.
                        </p>
                    </div>
                ) : (
                    <div className="flex flex-col items-center space-y-4">
                        <div className="relative animate-rotate-device">
                            <CornerUpRight className="absolute mt-5 right-14 text-blue-500 -rotate-45" />
                            <Tablet className="w-16 h-16 text-blue-500" />
                            <CornerDownLeft className="absolute left-14 -mt-10 text-blue-500 -rotate-45" />
                        </div>
                        <h2 className="text-xl font-bold text-gray-900">
                            Rotate Your Device
                        </h2>
                        <p className="text-gray-600 max-w-sm">
                            Please rotate your device to landscape mode to
                            continue with the experiment.
                        </p>
                    </div>
                )}
            </div>
        </div>
    );
};

export default DeviceOrientationCheck;
