import React, { useEffect, useMemo, useRef, useState } from "react";
import { Layer, Stage } from "react-konva";
import MediaGroup from "./MediaGroup";
import Modal from "./Modal";
import { arrangeItemsInGrid } from "../Utils/layoutUtils";
import { shuffleWithSeed } from "../Utils/randomUtils";
import { useTranslation } from "../Contexts/LanguageContext";
import AudioProgressBar from "./AudioProgressBar";
import getPhysicalScreenSize from "../Utils/getPhysicalScreenSize";
import { getSystemInfo } from "../Utils/getSystemInfo";

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
    onInteractionsUpdate,
    onCanvasSizeChange,
    checkIsTablet,
}) {
    const systemData = getSystemInfo();
    const sidebarWidth = checkIsTablet ? 350 : 450;

    const [stageSize, setStageSize] = useState({
        width: window.innerWidth - sidebarWidth,
        height: window.innerHeight - 50,
    });
    const { t } = useTranslation();
    const [showImageModal, setShowImageModal] = useState(false);
    const [selectedImage, setSelectedImage] = useState(null);
    const mediaArray = useMemo(() => Object.values(media || {}), [media]);
    const [mediaItems, setMediaItems] = useState([]);
    const currentAudioRef = useRef(null);
    const [currentSoundUrl, setCurrentSoundUrl] = useState(null);
    const [mediaInteractions, setMediaInteractions] = useState({});
    const [audioProgress, setAudioProgress] = useState(0);
    const [audioDuration, setAudioDuration] = useState(0);
    const [currentSoundName, setCurrentSoundName] = useState("");
    const audioProgressInterval = useRef(null);

    const { pixelsToCentimeters } = useMemo(() => getPhysicalScreenSize(), []);

    useEffect(() => {
        if (!mediaArray.length) return;

        const shuffledItems = shuffleWithSeed(mediaArray, 12213).map(
            (item, originalIndex) => ({
                ...item,
                originalIndex,
            })
        );

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

    useEffect(() => {
        if (onCanvasSizeChange) {
            onCanvasSizeChange({
                width_cm: pixelsToCentimeters(stageSize.width),
                height_cm: pixelsToCentimeters(stageSize.height),
                width_px: stageSize.width,
                height_px: stageSize.height,
                dpi: window.devicePixelRatio * 96,
            });
        }
    }, []);

    const handleResize = useMemo(() => {
        return () => {
            const newWidth = window.innerWidth - sidebarWidth;
            const newHeight = window.innerHeight - 50;

            if (
                newWidth !== stageSize.width ||
                newHeight !== stageSize.height
            ) {
                setStageSize({ width: newWidth, height: newHeight });
                setMediaItems((prevItems) =>
                    arrangeItemsInGrid(prevItems, size, newWidth, newHeight)
                );

                if (onCanvasSizeChange) {
                    onCanvasSizeChange({
                        width_cm: pixelsToCentimeters(newWidth),
                        height_cm: pixelsToCentimeters(newHeight),
                        width_px: newWidth,
                        height_px: newHeight,
                        dpi: window.devicePixelRatio * 96,
                    });
                }
            }
        };
    }, [
        size,
        onCanvasSizeChange,
        pixelsToCentimeters,
        stageSize,
        sidebarWidth,
    ]);

    useEffect(() => {
        handleResize();
        window.addEventListener("resize", handleResize);
        return () => window.removeEventListener("resize", handleResize);
    }, [handleResize]);

    const handleDragEnd = (e, index) => {
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
            type: "move",
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
        if (url) {
            // Gérer le compteur d'interactions
            const newInteractions = {
                ...mediaInteractions,
                [url]: (mediaInteractions[url] || 0) + 1,
            };
            setMediaInteractions(newInteractions);
            onInteractionsUpdate(newInteractions);

            // Trouver le nom du son (s1, s2, etc.)
            const item = mediaItems.find((item) => item.url === url);
            if (item) {
                const index = mediaItems.indexOf(item);
                setCurrentSoundName(`s${index + 1}`);

                onAction({
                    id: item.id,
                    type: "sound",
                    x: item.x,
                    y: item.y,
                    time: Date.now(),
                });
            }
        }

        // Arrêter l'ancien son s'il y en a un
        if (currentAudioRef.current) {
            clearInterval(audioProgressInterval.current);
            currentAudioRef.current.pause();
            currentAudioRef.current = null;
            setCurrentSoundUrl(null);
            setAudioProgress(0);
            setCurrentSoundName("");
        }

        // Jouer le nouveau son
        if (url && url !== currentSoundUrl) {
            const audio = new Audio(url);

            audio.addEventListener("loadedmetadata", () => {
                setAudioDuration(audio.duration);
            });

            audio.addEventListener("play", () => {
                audioProgressInterval.current = setInterval(() => {
                    setAudioProgress(audio.currentTime / audio.duration);
                }, 50);
            });

            audio.addEventListener("ended", () => {
                clearInterval(audioProgressInterval.current);
                setCurrentSoundUrl(null);
                setAudioProgress(0);
                setCurrentSoundName("");
                currentAudioRef.current = null;
            });

            currentAudioRef.current = audio;
            audio.play().catch((err) => console.error("Erreur audio:", err));
            setCurrentSoundUrl(url);
        }
    };

    const handleShowImage = (url) => {
        // Gérer le compteur d'interactions
        const newInteractions = {
            ...mediaInteractions,
            [url]: (mediaInteractions[url] || 0) + 1,
        };
        setMediaInteractions(newInteractions);
        onInteractionsUpdate(newInteractions);

        // Ajouter l'action au log
        const item = mediaItems.find((item) => item.url === url);
        if (item) {
            onAction({
                id: item.id,
                type: "image",
                x: item.x,
                y: item.y,
                time: Date.now(),
            });
        }

        // Reste de la logique d'affichage de l'image
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

    useEffect(() => {
        return () => {
            if (audioProgressInterval.current) {
                clearInterval(audioProgressInterval.current);
            }
            if (currentAudioRef.current) {
                currentAudioRef.current.pause();
            }
        };
    }, []);

    return (
        <div className="w-full h-full">
            <Stage
                width={stageSize.width}
                height={stageSize.height}
                // style={{ backgroundColor: "#f0f0f0" }}
            >
                <Layer>
                    {currentSoundName && (
                        <AudioProgressBar
                            currentSoundName={currentSoundName}
                            progress={audioProgress}
                            duration={audioDuration}
                            stageWidth={stageSize.width}
                        />
                    )}
                    {mediaItems.map((item, index) => (
                        <MediaGroup
                            key={index}
                            item={item}
                            index={index}
                            buttonColor={getItemColor(item)}
                            size={size}
                            isTablet={checkIsTablet}
                            draggable={!isFinished}
                            onDragEnd={(e) =>
                                !isFinished && handleDragEnd(e, index)
                            }
                            onDragMove={(e) =>
                                !isFinished && handleDragMove(e, index)
                            }
                            onPlaySound={handlePlaySound}
                            onShowImage={handleShowImage}
                            currentSoundUrl={currentSoundUrl}
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
                title={t("experimentSession.image_preview")}
            >
                <img
                    src={selectedImage}
                    alt={t("experimentSession.alt_image_preview")}
                    className="w-full h-auto max-h-[70vh] object-contain cursor-pointer"
                    onClick={() => setShowImageModal(false)}
                />
            </Modal>
        </div>
    );
}

export default KonvaComponent;
