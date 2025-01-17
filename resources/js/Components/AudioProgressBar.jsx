import React from "react";
import { Group, Rect, Text } from "react-konva";

function AudioProgressBar({
    currentSoundName,
    progress,
    duration,
    stageWidth,
}) {
    const barHeight = 24;
    const progressWidth = Math.min(300, stageWidth * 0.4);
    const x = (stageWidth - progressWidth) / 2;
    const y = 10;

    // Convertir la durée en format mm:ss
    const formatTime = (timeInSeconds) => {
        const minutes = Math.floor(timeInSeconds / 60);
        const seconds = Math.floor(timeInSeconds % 60);
        return `${minutes}:${seconds.toString().padStart(2, "0")}`;
    };

    return (
        <Group>
            {/* Fond de la barre */}
            <Rect
                x={x}
                y={y}
                width={progressWidth}
                height={barHeight}
                fill="rgba(0, 0, 0, 0.7)"
                cornerRadius={4}
            />

            {/* Barre de progression */}
            <Rect
                x={x + 4}
                y={y + 18} // Ajusté pour la nouvelle hauteur
                width={(progressWidth - 8) * progress}
                height={4} // Plus fine
                fill="#4CAF50"
                cornerRadius={2}
            />

            {/* Fond de la barre de progression */}
            <Rect
                x={x + 4}
                y={y + 18} // Ajusté pour la nouvelle hauteur
                width={progressWidth - 8}
                height={4} // Plus fine
                stroke="rgba(255, 255, 255, 0.3)"
                strokeWidth={1}
                cornerRadius={2}
            />

            {/* Nom du son et durée sur la même ligne */}
            <Text
                x={x + 6}
                y={y + 4}
                text={`${currentSoundName} - ${formatTime(
                    progress * duration
                )} / ${formatTime(duration)}`}
                fill="white"
                fontSize={12} // Police plus petite
            />
        </Group>
    );
}

export default AudioProgressBar;
