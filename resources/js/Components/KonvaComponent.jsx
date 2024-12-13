import React, { useEffect, useMemo, useRef, useState } from "react";
import { Layer, Stage } from "react-konva";
import MediaGroup from "./MediaGroup";
import Modal from "./Modal";
import { arrangeItemsInGrid } from "../Utils/layoutUtils";
import { shuffleWithSeed } from "../Utils/randomUtils";

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
    const [currentSoundUrl, setCurrentSoundUrl] = useState(null);

    const getSoundUrl = (item) => {
        const soundExtensions = [".wav", ".mp3", ".ogg", ".m4a", ".aac"];

        if (item.type === "image_sound") {
            // Si c'est une URL se terminant par une extension de son, c'est un son
            if (soundExtensions.some(ext => item.url.toLowerCase().endsWith(ext))) {
                return item.url;
            }
        }
        if (item.type === "sound") {
            return item.url;
        }
        return null;
    };

    useEffect(() => {
        if (!mediaArray.length) return;

        const shuffledItems = shuffleWithSeed(mediaArray, 12213).map((item, originalIndex) => ({
            ...item,
            originalIndex // Ajouter l'index original à chaque item
        }));

        const arrangedItems = arrangeItemsInGrid(
            shuffledItems,
            size,
            window.innerWidth,
            window.innerHeight
        );

        setMediaItems(arrangedItems);
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

            setStageSize({ width: newWidth, height: newHeight });
            setMediaItems((prevItems) =>
                arrangeItemsInGrid(prevItems, size, newWidth, newHeight)
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


    const handlePlaySound = (url) => {
        // Toujours arrêter le son actuel
        if (currentAudioRef.current) {
            currentAudioRef.current.pause();
            currentAudioRef.current = null;
            setCurrentSoundUrl(null);
        }

        // Jouer le nouveau son si fourni
        if (url) {
            const audio = new Audio(url);
            currentAudioRef.current = audio;
            audio.play().catch((err) => console.error("Erreur audio:", err));
            setCurrentSoundUrl(url);

            audio.onended = () => {
                setCurrentSoundUrl(null);
                currentAudioRef.current = null;
            };
        }
    };

    useEffect(() => {
        const sessionData = JSON.parse(localStorage.getItem("session") || "{}");
        setIsTablet(sessionData.device_type === "tablet");
    }, []);

    const handleShowImage = (url) => {
        // Si un son est en cours, on l'arrête
        if (currentAudioRef.current) {
            currentAudioRef.current.pause();
            currentAudioRef.current = null;
            setCurrentSoundUrl(null);
        }

        setSelectedImage(url);
        setShowImageModal(true);
    };

    const getItemColor = (item) => {
        if (
            !isFinished &&
            (item.type === "sound" || item.type === "image_sound")
        ) {
            return buttonColor;
        }

        if (isFinished) {
            const group = groups.find((g) =>
                g.elements.some((element) => element.id === item.id)
            );

            if (item.type === "sound" || item.type === "image_sound") {
                return group ? group.color : buttonColor;
            }

            return group ? group.color : "transparent";
        }

        return "transparent";
    };

    const handleGroupClick = (item) => {
        if (editingGroupIndex !== null) {
            onMediaGroupChange(item.id, editingGroupIndex);
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
                            draggable={!isFinished}
                            onDragEnd={(e) => !isFinished && handleDragEnd(e, index)}
                            onDragMove={(e) => !isFinished && handleDragMove(e, index)}
                            onPlaySound={handlePlaySound}
                            onShowImage={handleShowImage}
                            currentSoundUrl={currentSoundUrl}
                            onClick={() => handleGroupClick(item)}
                            isClickable={true}
                            groups={groups}
                            isFinished={isFinished}
                            cursor={isFinished ? (item.type === "sound" ? "pointer" : "zoom-in") : "move"}
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
