import React, { useEffect, useMemo, useRef, useState } from "react";
import { Layer, Stage } from "react-konva";
import MediaGroup from "./MediaGroup";
import Modal from "./Modal";

function KonvaComponent({
    media,
    buttonColor,
    size = 100,
    onAction,
    onMediaItemsChange,
    isFinished,
    groups = [],
    editingGroupIndex,
    onMediaGroupChange,
}) {
    const [stageSize, setStageSize] = useState({
        width: window.innerWidth,
        height: window.innerHeight,
    });
    const [showImageModal, setShowImageModal] = useState(false);
    const [selectedImage, setSelectedImage] = useState(null);
    const [isTablet, setIsTablet] = useState(false);
    const mediaArray = useMemo(() => Object.values(media || {}), [media]);
    const [mediaItems, setMediaItems] = useState([]);
    const currentAudioRef = useRef(null);

    const marginTop = 50;
    const marginLeft = 50;

    useEffect(() => {
        if (!mediaArray.length) return;

        const shuffledMedia = [...mediaArray].sort(() => Math.random() - 0.5);

        const newWidth = window.innerWidth;
        const newHeight = window.innerHeight;
        const spacing = 20;

        const updatedMediaItems = shuffledMedia.map((item, index) => {
            const itemSize = parseInt(item.button_size || size);
            const row = Math.floor(index / 5);
            const col = index % 5;

            const x =
                item.type === "image"
                    ? Math.random() * (newWidth - itemSize)
                    : marginLeft + col * (itemSize + spacing);

            const y =
                item.type === "image"
                    ? Math.random() * (newHeight - itemSize)
                    : marginTop + row * (itemSize + spacing);

            return {
                ...item,
                x: Math.min(x, newWidth - itemSize),
                y: Math.min(y, newHeight - itemSize),
                width: itemSize,
                height: itemSize,
            };
        });

        setMediaItems(updatedMediaItems);
    }, [mediaArray, size]);

    useEffect(() => {
        if (onMediaItemsChange && mediaItems.length > 0) {
            onMediaItemsChange(mediaItems);
        }
    }, [mediaItems, onMediaItemsChange]);

    const handleResize = useMemo(() => {
        return () => {
            const newWidth = window.innerWidth - 450;
            const newHeight = window.innerHeight - 50;

            setStageSize({
                width: newWidth,
                height: newHeight,
            });

            setMediaItems((prevItems) =>
                prevItems.map((item) => {
                    const itemSize = parseInt(item.button_size || size);
                    const newX = Math.min(item.x, newWidth - itemSize);
                    const newY = Math.min(item.y, newHeight - itemSize);
                    return { ...item, x: newX, y: newY };
                })
            );
        };
    }, [size]);

    useEffect(() => {
        handleResize();
        window.addEventListener("resize", handleResize);
        return () => window.removeEventListener("resize", handleResize);
    }, [handleResize]);

    const handleDragEnd = (e, index) => {
        if (isFinished) return;

        const item = mediaItems[index];
        const itemSize = parseInt(item.button_size || size);

        // Contraindre X entre 0 et la largeur du stage moins la taille de l'item
        const newX = Math.max(
            0,
            Math.min(e.target.x(), stageSize.width - itemSize)
        );

        // Contraindre Y entre 0 et la hauteur du stage moins la taille de l'item
        const newY = Math.max(
            0,
            Math.min(e.target.y(), stageSize.height - itemSize)
        );

        // Si la position a changé, forcer le repositionnement
        if (e.target.x() !== newX || e.target.y() !== newY) {
            e.target.position({ x: newX, y: newY });
        }

        setMediaItems((prevItems) =>
            prevItems.map((item, i) =>
                i === index ? { ...item, x: newX, y: newY } : item
            )
        );

        onAction({
            id: item.id,
            x: newX,
            y: newY,
            time: Date.now(),
        });
    };

    const handlePlaySound = (url) => {
        if (currentAudioRef.current && !currentAudioRef.current.paused) {
            currentAudioRef.current.pause();
            currentAudioRef.current = null;
            return;
        }

        const audio = new Audio(url);
        currentAudioRef.current = audio;
        audio.play().catch((err) => console.error("Erreur audio:", err));
    };

    useEffect(() => {
        const sessionData = JSON.parse(localStorage.getItem("session") || "{}");
        setIsTablet(sessionData.device_type === "tablet");
    }, []);

    const handleShowImage = (url) => {
        setSelectedImage(url);
        setShowImageModal(true);
    };

    const getItemColor = (item) => {
        // Avant la fin, les sons ont toujours la couleur de base
        if (!isFinished && item.type === "sound") {
            return buttonColor;
        }

        // Après la fin, on cherche la couleur du groupe
        if (isFinished) {
            const group = groups.find((g) =>
                g.elements.some((element) => element.id === item.id)
            );

            // Pour les sons, on utilise soit la couleur du groupe soit la couleur de base
            if (item.type === "sound") {
                return group ? group.color : buttonColor;
            }

            // Pour les images, on utilise la couleur du groupe ou transparent
            return group ? group.color : "transparent";
        }

        // Par défaut, transparent pour les images
        return "transparent";
    };
    const handleDragMove = (e, index) => {
        if (isFinished) return;

        const item = mediaItems[index];
        const itemSize = parseInt(item.button_size || size);

        const newX = Math.max(
            0,
            Math.min(e.target.x(), stageSize.width - itemSize)
        );
        const newY = Math.max(
            0,
            Math.min(e.target.y(), stageSize.height - itemSize)
        );

        e.target.position({ x: newX, y: newY });
    };

    const handleGroupClick = (item) => {
        if (!isFinished) return;

        if (editingGroupIndex !== null) {
            onMediaGroupChange(item.id, editingGroupIndex);

            if (item.type === "sound") {
                handlePlaySound(item.url);
            } else if (item.type === "image") {
                handleShowImage(item.url);
            }
        } else {
            // Comportement normal
            if (item.type === "sound") {
                handlePlaySound(item.url);
            } else if (item.type === "image") {
                handleShowImage(item.url);
            }
        }
    };

    return (
        <div className="w-full h-full">
            <Stage
                width={stageSize.width}
                height={stageSize.height}
                // style={{ backgroundColor: "#f0f0f0" }}
            >
                <Layer>
                    {mediaItems.map((item, index) => (
                        <MediaGroup
                            key={index}
                            item={item}
                            index={index}
                            buttonColor={getItemColor(item)}
                            size={size}
                            isTablet={isTablet}
                            draggable={!isFinished} // Déjà correct
                            onDragEnd={(e) =>
                                !isFinished && handleDragEnd(e, index)
                            }
                            onDragMove={(e) =>
                                !isFinished && handleDragMove(e, index)
                            }
                            onPlaySound={handlePlaySound}
                            onShowImage={handleShowImage}
                            onClick={() => handleGroupClick(item)}
                            isClickable={true}
                            groups={groups}
                            isFinished={isFinished}
                            cursor={
                                isFinished
                                    ? item.type === "sound"
                                        ? "pointer"
                                        : "zoom-in"
                                    : "move"
                            }
                        />
                    ))}
                </Layer>
            </Stage>

            <Modal
                isOpen={showImageModal}
                onClose={() => setShowImageModal(false)}
                title="Aperçu de l'image"
            >
                <img
                    src={selectedImage}
                    alt="Aperçu"
                    className="w-full h-auto max-h-[70vh] object-contain"
                />
            </Modal>
        </div>
    );
}

export default KonvaComponent;
