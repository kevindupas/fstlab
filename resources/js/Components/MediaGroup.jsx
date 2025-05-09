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
    const [lastTap, setLastTap] = useState(0);
    const [isDragging, setIsDragging] = useState(false);

    const imageExtensions = [".png", ".jpg", ".jpeg", ".gif", ".webp", ".bmp"];
    const soundExtensions = [".wav", ".mp3", ".ogg", ".m4a", ".aac"];

    const isImage = imageExtensions.some((ext) =>
        item.url.toLowerCase().endsWith(ext)
    );
    const isSound = soundExtensions.some((ext) =>
        item.url.toLowerCase().endsWith(ext)
    );

    useEffect(() => {
        if (isImage) {
            const img = new window.Image();
            img.src = item.url;
            img.onload = () => setImage(img);
        }
    }, [item, isImage]);

    const handleTap = (e) => {
        if (isTablet) {
            const currentTime = new Date().getTime();
            const tapLength = currentTime - lastTap;

            if (!isDragging) {
                if (tapLength < 300 && tapLength > 0) {
                    // Double tap
                    if (isSound) {
                        if (currentSoundUrl === item.url) {
                            onPlaySound(null);
                        } else {
                            onPlaySound(item.url);
                        }
                    } else if (isImage) {
                        onShowImage(item.url);
                    }
                    setLastTap(0);
                } else {
                    // Single tap
                    onClick && onClick(item);
                    setLastTap(currentTime);
                }
            }
        } else {
            // Desktop behavior
            if (e.evt.detail === 2) {
                if (isSound) {
                    if (currentSoundUrl === item.url) {
                        onPlaySound(null);
                    } else {
                        onPlaySound(item.url);
                    }
                } else if (isImage) {
                    onShowImage(item.url);
                }
            } else {
                onClick && onClick(item);
            }
        }
    };

    const handleDragStart = () => {
        setIsDragging(true);
    };

    const handleDragEndCustom = (e) => {
        setIsDragging(false);
        if (onDragEnd) {
            onDragEnd(e);
        }
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
            onDragStart={handleDragStart}
            onDragEnd={handleDragEndCustom}
            onDragMove={onDragMove}
            onTap={isTablet ? handleTap : undefined}
            onClick={!isTablet ? handleTap : undefined}
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
                        text={`p${item.displayIndex + 1}`}
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
                        text={`s${item.displayIndex + 1}`}
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
