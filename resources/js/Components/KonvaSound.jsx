import React, { useEffect, useMemo, useState } from "react";
import { Circle, Group, Layer, Rect, Stage, Text } from "react-konva";
import { useNavigate } from "react-router-dom";

const getDistance = (pos1, pos2) => {
    const dx = pos1.x - pos2.x;
    const dy = pos1.y - pos2.y;
    return Math.sqrt(dx * dx + dy * dy);
};

function KonvaSound({ media, buttonColor, size }) {
    // Utiliser useMemo pour éviter de recréer le tableau à chaque rendu
    const mediaArray = useMemo(() => Object.values(media || {}), [media]);

    const [mediaItems, setMediaItems] = useState([]);
    const [clusters, setClusters] = useState([]);
    const [stageSize, setStageSize] = useState({
        width: window.innerWidth,
        height: window.innerHeight,
    });
    const [elapsedTime, setElapsedTime] = useState(0);
    const [actionsLog, setActionsLog] = useState([]);

    const navigate = useNavigate();

    const marginTop = 50;
    const marginLeft = 50;

    const clusterColors = [
        "#FF0000",
        "#00FF00",
        "#0000FF",
        "#FFFF00",
        "#FF00FF",
        "#00FFFF",
        "#FFA500",
    ];

    // Initialisation des positions
    useEffect(() => {
        if (!mediaArray.length) return;

        const newWidth = window.innerWidth;
        const newHeight = window.innerHeight;
        const spacing = 20;

        const updatedMediaItems = mediaArray.map((item, index) => {
            const itemSize = parseInt(item.button_size || size);
            const row = Math.floor(index / 5);
            const col = index % 5;
            const x = marginLeft + col * (itemSize + spacing);
            const y = marginTop + row * (itemSize + spacing);
            return {
                ...item,
                x: Math.min(x, newWidth - itemSize),
                y: Math.min(y, newHeight - itemSize),
            };
        });

        setMediaItems(updatedMediaItems);
        setElapsedTime(Date.now());
    }, [mediaArray, size]); // Dépendances explicites

    const handleResize = useMemo(() => {
        return () => {
            const newWidth = window.innerWidth;
            const newHeight = window.innerHeight;

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
    }, [size]); // Dépendance à size uniquement

    // Gestionnaire de redimensionnement
    useEffect(() => {
        window.addEventListener("resize", handleResize);
        return () => window.removeEventListener("resize", handleResize);
    }, [handleResize]);

    const handleDragEnd = (e, index) => {
        const itemSize = parseInt(mediaItems[index].button_size || size);
        const newX = Math.max(
            0,
            Math.min(e.target.x(), stageSize.width - itemSize)
        );
        const newY = Math.max(
            0,
            Math.min(e.target.y(), stageSize.height - itemSize)
        );

        setMediaItems((prevItems) =>
            prevItems.map((item, i) =>
                i === index ? { ...item, x: newX, y: newY } : item
            )
        );

        setActionsLog((prevLog) => [
            ...prevLog,
            { id: mediaItems[index].id, x: newX, y: newY, time: Date.now() },
        ]);
    };

    // Gestionnaire audio avec useRef pour éviter les fuites
    const currentAudioRef = React.useRef(null);
    const handlePlaySound = (item) => {
        if (item.type === "sound") {
            if (currentAudioRef.current && !currentAudioRef.current.paused) {
                currentAudioRef.current.pause();
                currentAudioRef.current = null;
                return;
            }
            const audio = new Audio(item.url);
            currentAudioRef.current = audio;
            audio
                .play()
                .catch((err) =>
                    console.error("Erreur lors de la lecture audio:", err)
                );
        }
    };

    const handleTerminate = () => {
        const sessionData = JSON.parse(localStorage.getItem("session"));
        const sessionId = sessionData?.id;

        const threshold = 200;
        const clustersMap = [];

        mediaItems.forEach((item) => {
            let foundCluster = false;
            for (let cluster of clustersMap) {
                if (cluster.some((g) => getDistance(g, item) < threshold)) {
                    cluster.push(item);
                    foundCluster = true;
                    break;
                }
            }
            if (!foundCluster) {
                clustersMap.push([item]);
            }
        });

        const preparedGroups = clustersMap.map((cluster, index) => ({
            name: `Group ${index + 1}`,
            color: clusterColors[index % clusterColors.length],
            elements: cluster,
        }));

        const finalElapsedTime = Date.now() - elapsedTime;

        navigate("/results", {
            state: {
                sessionId,
                groups: preparedGroups,
                elapsedTime: finalElapsedTime,
                actionsLog,
            },
        });
    };

    return (
        <div>
            <Stage
                width={stageSize.width}
                height={stageSize.height}
                style={{ backgroundColor: "#f0f0f0" }}
            >
                <Layer>
                    {mediaItems.map((item, index) => (
                        <Group
                            key={index}
                            x={item.x}
                            y={item.y}
                            draggable
                            onDragEnd={(e) => handleDragEnd(e, index)}
                            onClick={() => handlePlaySound(item)}
                        >
                            <Rect
                                width={parseInt(item.button_size || size)}
                                height={parseInt(item.button_size || size)}
                                fill={item.button_color || buttonColor}
                                stroke={"black"}
                                strokeWidth={4}
                            />
                            <Text
                                text={`${index + 1}`}
                                fontSize={20}
                                x={parseInt(item.button_size || size) / 2 - 5}
                                y={parseInt(item.button_size || size) / 2 - 10}
                                fill="white"
                            />
                        </Group>
                    ))}

                    {clusters.map((cluster, clusterIndex) => {
                        const minX = Math.min(...cluster.map((g) => g.x));
                        const maxX = Math.max(
                            ...cluster.map(
                                (g) => g.x + parseInt(g.button_size || size)
                            )
                        );
                        const minY = Math.min(...cluster.map((g) => g.y));
                        const maxY = Math.max(
                            ...cluster.map(
                                (g) => g.y + parseInt(g.button_size || size)
                            )
                        );
                        const centerX = (minX + maxX) / 2;
                        const centerY = (minY + maxY) / 2;
                        const radius =
                            Math.max(maxX - minX, maxY - minY) / 2 + 20;

                        const clusterColor =
                            clusterColors[clusterIndex % clusterColors.length];

                        return (
                            <Circle
                                key={clusterIndex}
                                x={centerX}
                                y={centerY}
                                radius={radius}
                                stroke={clusterColor}
                                strokeWidth={5}
                            />
                        );
                    })}
                </Layer>
            </Stage>

            <button
                onClick={handleTerminate}
                className="fixed top-4 right-4 bg-green-500 text-white py-2 px-4 rounded"
            >
                Terminate
            </button>
        </div>
    );
}

export default KonvaSound;
