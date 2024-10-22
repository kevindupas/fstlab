import React, { useEffect, useState } from "react";
import { Circle, Group, Image, Layer, Stage } from "react-konva";
import { useNavigate } from "react-router-dom";

const getDistance = (pos1, pos2) => {
    const dx = pos1.x - pos2.x;
    const dy = pos1.y - pos2.y;
    return Math.sqrt(dx * dx + dy * dy);
};

function KonvaImage({ media }) {
    const [mediaItems, setMediaItems] = useState([]);
    const [clusters, setClusters] = useState([]);
    const [stageSize, setStageSize] = useState({
        width: window.innerWidth,
        height: window.innerHeight,
    });
    const [elapsedTime, setElapsedTime] = useState(0);
    const [actionsLog, setActionsLog] = useState([]);
    const [images, setImages] = useState([]);

    const navigate = useNavigate();

    const size = 100; // Taille fixe pour tous les éléments

    const clusterColors = [
        "#FF0000",
        "#00FF00",
        "#0000FF",
        "#FFFF00",
        "#FF00FF",
        "#00FFFF",
        "#FFA500",
    ];

    useEffect(() => {
        const newWidth = window.innerWidth;
        const newHeight = window.innerHeight;

        const updatedMediaItems = media.map((item) => ({
            ...item,
            x: Math.random() * (newWidth - size),
            y: Math.random() * (newHeight - size),
            width: size,
            height: size,
        }));

        setMediaItems(updatedMediaItems);
        setElapsedTime(Date.now());
    }, [media]);

    useEffect(() => {
        const loadImages = async () => {
            const loadedImages = await Promise.all(
                media.map((item) => {
                    return new Promise((resolve, reject) => {
                        const img = new window.Image();
                        img.src = item.url;
                        img.onload = () => resolve(img);
                        img.onerror = reject;
                    });
                })
            );
            setImages(loadedImages);
        };
        loadImages();
    }, [media]);

    const handleResize = () => {
        const newWidth = window.innerWidth;
        const newHeight = window.innerHeight;

        setStageSize({ width: newWidth, height: newHeight });

        const updatedMediaItems = mediaItems.map((item) => ({
            ...item,
            x: Math.min(item.x, newWidth - size),
            y: Math.min(item.y, newHeight - size),
        }));

        setMediaItems(updatedMediaItems);
    };

    useEffect(() => {
        window.addEventListener("resize", handleResize);
        return () => window.removeEventListener("resize", handleResize);
    }, [mediaItems]);

    const handleDragEnd = (e, index) => {
        const newX = Math.max(
            0,
            Math.min(e.target.x(), stageSize.width - size)
        );
        const newY = Math.max(
            0,
            Math.min(e.target.y(), stageSize.height - size)
        );

        const updatedMediaItems = mediaItems.map((item, i) =>
            i === index ? { ...item, x: newX, y: newY } : item
        );

        setActionsLog((prevLog) => [
            ...prevLog,
            { id: mediaItems[index].id, x: newX, y: newY, time: Date.now() },
        ]);

        setMediaItems(updatedMediaItems);
    };

    const handleTerminate = () => {
        const sessionData = JSON.parse(localStorage.getItem("session"));
        const sessionId = sessionData.id;

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

        setElapsedTime(Date.now() - elapsedTime);

        navigate("/results", {
            state: {
                sessionId: sessionId,
                groups: preparedGroups,
                elapsedTime: Date.now() - elapsedTime,
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
                        >
                            <Image
                                image={images[index]}
                                width={size}
                                height={size}
                                stroke={"black"}
                                strokeWidth={4}
                            />
                        </Group>
                    ))}

                    {clusters.map((cluster, clusterIndex) => {
                        const minX = Math.min(...cluster.map((g) => g.x));
                        const maxX = Math.max(
                            ...cluster.map((g) => g.x + size)
                        );
                        const minY = Math.min(...cluster.map((g) => g.y));
                        const maxY = Math.max(
                            ...cluster.map((g) => g.y + size)
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

export default KonvaImage;
