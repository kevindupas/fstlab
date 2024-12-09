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
    isFinished,
    cursor,
    onClick,
}) {
    const [image, setImage] = useState(null);
    const [touchStart, setTouchStart] = useState(0);
    const DOUBLE_TAP_DELAY = 300;

    const imageExtensions = [".png", ".jpg", ".jpeg", ".gif", ".webp", ".bmp"];
    const soundExtensions = [".wav", ".mp3", ".ogg", ".m4a", ".aac"];

    const isImage = imageExtensions.some((ext) =>
        item.url.toLowerCase().endsWith(ext)
    );
    const isSound = soundExtensions.some((ext) =>
        item.url.toLowerCase().endsWith(ext)
    );

    const getGroupColor = () => {
        if (!isFinished) return item.button_color || buttonColor;

        const group = groups.find((g) =>
            g.elements.some((element) => element.id === item.id)
        );
        return group ? group.color : item.button_color || buttonColor;
    };

    useEffect(() => {
        if (isImage) {
            const img = new window.Image();
            img.src = item.url;
            img.onload = () => setImage(img);
        }
    }, [item, isImage]);

    const handleInteraction = (isDoubleTap, event = null) => {
        // Autoriser toujours les interactions audio/image, même quand draggable est false
        if (isSound) {
            if (isDoubleTap || (!isTablet && event?.evt?.detail === 2)) {
                onPlaySound(item.url);
            }
        } else if (isImage) {
            if (isDoubleTap || (!isTablet && event?.evt?.detail === 2)) {
                onShowImage(item.url);
            }
        }
    };

    const handleTouchStart = () => {
        const now = Date.now();
        if (touchStart && now - touchStart < DOUBLE_TAP_DELAY) {
            handleInteraction(true);
        }
        setTouchStart(now);
    };

    const handleClick = (e) => {
        // Gestion du double-clic pour son/image
        if (!isTablet) {
            handleInteraction(false, e);
        }

        // Gestion du simple clic pour le changement de groupe
        if (isFinished && onClick) {
            onClick(item);
        }
    };

    const renderContent = () => {
        const groupColor = getGroupColor();
        const displayIndex =
            item.originalIndex !== undefined ? item.originalIndex : index;

        if (isImage) {
            return (
                <>
                    <Image
                        image={image}
                        width={parseInt(item.button_size || size)}
                        height={parseInt(item.button_size || size)}
                        stroke="black"
                        strokeWidth={4}
                    />
                    {isFinished && (
                        <Rect
                            width={parseInt(item.button_size || size)}
                            height={parseInt(item.button_size || size)}
                            fill={groupColor}
                            opacity={0.3}
                        />
                    )}
                    <Rect
                        x={4}
                        y={parseInt(item.button_size || size) - 24}
                        width={30}
                        height={20}
                        fill="rgba(0, 0, 0, 0.7)"
                        cornerRadius={4}
                    />
                    <Text
                        text={`p${displayIndex + 1}`}
                        fontSize={14}
                        x={8}
                        y={parseInt(item.button_size || size) - 22}
                        fill="white"
                    />
                </>
            );
        }

        return (
            <>
                <Rect
                    width={parseInt(item.button_size || size)}
                    height={parseInt(item.button_size || size)}
                    fill={groupColor}
                    stroke="black"
                    strokeWidth={2}
                />
                <Text
                    text={`s${displayIndex + 1}`}
                    fontSize={20}
                    x={parseInt(item.button_size || size) / 2 - 15}
                    y={parseInt(item.button_size || size) / 2 - 10}
                    fill="white"
                />
            </>
        );
    };

    return (
        <Group
            x={item.x}
            y={item.y}
            draggable={draggable}
            onDragEnd={onDragEnd}
            onDragMove={onDragMove}
            onClick={handleClick}
            onTouchStart={handleTouchStart}
            cursor={cursor}
        >
            {renderContent()}
        </Group>
    );
}

export default MediaGroup;
