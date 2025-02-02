import React, { useEffect, useState } from "react";
import { Group, Image, Rect, Text } from "react-konva";

function MediaGroup({
    item,
    index,
    buttonColor,
    size,
    onDragEnd,
    onDragMove,
    onPlaySound,
    onShowImage,
    isTablet,
    draggable,
    groups,
    cursor,
    onClick,
    currentSoundUrl,
}) {
    const [image, setImage] = useState(null);
    // const [touchStart, setTouchStart] = useState(0);
    const [touchStartPosition, setTouchStartPosition] = useState(null);
    const [lastTapTime, setLastTapTime] = useState(0);
    const DOUBLE_TAP_DELAY = 300;
    const TOUCH_MOVE_THRESHOLD = 10;

    const imageExtensions = [".png", ".jpg", ".jpeg", ".gif", ".webp", ".bmp"];
    const soundExtensions = [".wav", ".mp3", ".ogg", ".m4a", ".aac"];

    const isImage = imageExtensions.some((ext) =>
        item.url.toLowerCase().endsWith(ext)
    );
    const isSound = soundExtensions.some((ext) =>
        item.url.toLowerCase().endsWith(ext)
    );

    const getSoundUrl = () => {
        if (isSound) return item.url;
        return null;
    };

    useEffect(() => {
        if (isImage) {
            const img = new window.Image();
            img.src = item.url;
            img.onload = () => setImage(img);
        }
    }, [item, isImage]);

    const handleClick = (e) => {
        if (!isTablet) {
            // Gestion des clics pour desktop
            if (e.evt.detail === 2) {
                const soundUrl = getSoundUrl();
                if (isSound) {
                    if (currentSoundUrl === soundUrl) {
                        onPlaySound(null);
                    } else {
                        onPlaySound(soundUrl);
                    }
                } else if (isImage) {
                    onShowImage(item.url);
                }
            } else if (e.evt.detail === 1) {
                onClick && onClick(item);
            }
        }
    };

    const handleTouchStart = (e) => {
        const now = Date.now();
        const touch = e.evt.touches[0];
        setTouchStartPosition({ x: touch.clientX, y: touch.clientY });

        const timeSinceLastTap = now - lastTapTime;
        if (timeSinceLastTap < DOUBLE_TAP_DELAY) {
            // Double tap détecté
            const soundUrl = getSoundUrl();
            if (isSound) {
                if (currentSoundUrl === soundUrl) {
                    onPlaySound(null);
                } else {
                    onPlaySound(soundUrl);
                }
            } else if (isImage) {
                onShowImage(item.url);
            }
            setLastTapTime(0); // Réinitialiser pour éviter les triples taps
        } else {
            setLastTapTime(now);
        }
    };

    const handleTouchEnd = (e) => {
        if (!touchStartPosition) return;

        const touch = e.evt.changedTouches[0];
        const moveX = Math.abs(touch.clientX - touchStartPosition.x);
        const moveY = Math.abs(touch.clientY - touchStartPosition.y);

        // Si le mouvement est minimal (pas de glissement significatif)
        if (moveX < TOUCH_MOVE_THRESHOLD && moveY < TOUCH_MOVE_THRESHOLD) {
            const timeSinceLastTap = Date.now() - lastTapTime;
            // Si ce n'est pas un double tap potentiel, traiter comme un simple tap
            if (timeSinceLastTap >= DOUBLE_TAP_DELAY) {
                onClick && onClick(item);
            }
        }

        setTouchStartPosition(null);
    };

    const getGroupColor = () => {
        const group = groups.find((g) =>
            g.elements.some((e) => e.id === item.id)
        );
        if (group) return group.color;
        if (isSound) return buttonColor;
        return "transparent";
    };

    return (
        <Group
            x={item.x}
            y={item.y}
            draggable={draggable}
            onDragEnd={onDragEnd}
            onDragMove={onDragMove}
            onClick={!isTablet ? handleClick : undefined}
            onTouchStart={isTablet ? handleTouchStart : undefined}
            onTouchEnd={isTablet ? handleTouchEnd : undefined}
            cursor={cursor}
        >
            {isImage ? (
                <>
                    <Image
                        image={image}
                        width={parseInt(item.button_size || size)}
                        height={parseInt(item.button_size || size)}
                        stroke="black"
                        strokeWidth={4}
                    />
                    <Rect
                        width={parseInt(item.button_size || size)}
                        height={parseInt(item.button_size || size)}
                        fill={getGroupColor()}
                        opacity={0.3}
                    />
                    <Rect
                        x={4}
                        y={parseInt(item.button_size || size) - 24}
                        width={30}
                        height={20}
                        fill="rgba(0, 0, 0, 0.7)"
                        cornerRadius={4}
                    />
                    <Text
                        text={`p${index + 1}`}
                        fontSize={14}
                        x={8}
                        y={parseInt(item.button_size || size) - 22}
                        fill="white"
                    />
                </>
            ) : (
                <>
                    <Rect
                        width={parseInt(item.button_size || size)}
                        height={parseInt(item.button_size || size)}
                        fill={getGroupColor()}
                        stroke="black"
                        strokeWidth={2}
                    />
                    <Text
                        text={`s${index + 1}`}
                        fontSize={20}
                        x={parseInt(item.button_size || size) / 2 - 15}
                        y={parseInt(item.button_size || size) / 2 - 10}
                        fill="white"
                    />
                </>
            )}
        </Group>
    );
}

export default MediaGroup;
