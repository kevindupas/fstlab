import React, { useEffect, useState } from "react";
import { Circle, Group, Layer, Rect, Stage, Text } from "react-konva";
import { useNavigate } from "react-router-dom";

// Fonction pour calculer la distance entre deux points
const getDistance = (pos1, pos2) => {
    const dx = pos1.x - pos2.x;
    const dy = pos1.y - pos2.y;
    return Math.sqrt(dx * dx + dy * dy);
};

function KonvaSound({ media, buttonColor, size }) {
    const [mediaItems, setMediaItems] = useState([]);
    const [clusters, setClusters] = useState([]);
    const [stageSize, setStageSize] = useState({
        width: window.innerWidth,
        height: window.innerHeight,
    });
    const [elapsedTime, setElapsedTime] = useState(0); // Temps écoulé
    const [actionsLog, setActionsLog] = useState([]); // Journal des actions de déplacement

    const navigate = useNavigate(); // Utiliser le hook pour la navigation

    // Variables pour définir la marge en haut et à gauche
    const marginTop = 50; // Marge en haut
    const marginLeft = 50; // Marge à gauche

    // Tableau de couleurs pour les clusters
    const clusterColors = [
        "#FF0000",
        "#00FF00",
        "#0000FF",
        "#FFFF00",
        "#FF00FF",
        "#00FFFF",
        "#FFA500",
    ];

    // Initialiser les éléments avec un alignement en haut à gauche + marges
    useEffect(() => {
        const newWidth = window.innerWidth;
        const newHeight = window.innerHeight;

        // Espacement horizontal et vertical entre les éléments
        const spacing = 20;

        // Initialiser les positions des éléments alignés en haut à gauche avec marges
        const updatedMediaItems = media.map((item, index) => {
            const row = Math.floor(index / 5); // 5 éléments par rangée
            const col = index % 5; // Calculer la colonne
            const x = marginLeft + col * (parseInt(size) + spacing); // Position horizontale avec marge
            const y = marginTop + row * (parseInt(size) + spacing); // Position verticale avec marge
            return {
                ...item,
                x: Math.min(x, newWidth - parseInt(size)), // S'assurer que l'élément reste à l'intérieur
                y: Math.min(y, newHeight - parseInt(size)), // S'assurer que l'élément reste à l'intérieur
            };
        });

        setMediaItems(updatedMediaItems);
        setElapsedTime(Date.now()); // Démarrer le chronomètre
    }, [media, size]);

    // Fonction pour redimensionner la fenêtre et repositionner les éléments
    const handleResize = () => {
        const newWidth = window.innerWidth;
        const newHeight = window.innerHeight;

        // Mettre à jour la taille de la scène
        setStageSize({
            width: newWidth,
            height: newHeight,
        });

        // Repositionner les éléments si en dehors des nouvelles dimensions
        const updatedMediaItems = mediaItems.map((item) => {
            const newX = Math.min(item.x, newWidth - parseInt(size));
            const newY = Math.min(item.y, newHeight - parseInt(size));
            return { ...item, x: newX, y: newY };
        });

        setMediaItems(updatedMediaItems);
    };

    useEffect(() => {
        window.addEventListener("resize", handleResize);
        return () => window.removeEventListener("resize", handleResize);
    }, [mediaItems]);

    // Fonction pour déplacer les carrés et vérifier les limites de la fenêtre
    const handleDragEnd = (e, index) => {
        const newX = Math.max(
            0,
            Math.min(e.target.x(), stageSize.width - parseInt(size))
        );
        const newY = Math.max(
            0,
            Math.min(e.target.y(), stageSize.height - parseInt(size))
        );

        const updatedMediaItems = mediaItems.map((item, i) =>
            i === index ? { ...item, x: newX, y: newY } : item
        );

        // Ajouter l'action au journal des mouvements
        setActionsLog((prevLog) => [
            ...prevLog,
            { id: mediaItems[index].id, x: newX, y: newY, time: Date.now() },
        ]);

        setMediaItems(updatedMediaItems);
    };

    // Fonction pour jouer un son au clic sur un carré
    let currentAudio = null;
    const handlePlaySound = (item) => {
        if (item.type === "sound") {
            if (currentAudio && !currentAudio.paused) {
                console.log("Another audio is currently playing.");
                return;
            }
            currentAudio = new Audio(item.url);
            currentAudio
                .play()
                .catch((err) =>
                    console.error("Erreur lors de la lecture audio:", err)
                );
        }
    };

    // Fonction pour créer des clusters et rediriger vers la page des résultats
    const handleTerminate = () => {
        const sessionData = JSON.parse(localStorage.getItem("session")); // Récupérer l'objet session
        const sessionId = sessionData.id; // Extraire l'ID de session

        const threshold = 200; // Distance pour créer un cluster
        const clustersMap = [];

        mediaItems.forEach((item) => {
            let foundCluster = false;
            for (let cluster of clustersMap) {
                // Vérifier si cet item peut être ajouté à un cluster existant
                if (cluster.some((g) => getDistance(g, item) < threshold)) {
                    cluster.push(item);
                    foundCluster = true;
                    break;
                }
            }
            // Si aucun cluster trouvé, créer un nouveau cluster
            if (!foundCluster) {
                clustersMap.push([item]);
            }
        });

        // Préparer les groupes avec nom et couleur par défaut
        const preparedGroups = clustersMap.map((cluster, index) => ({
            name: `Group ${index + 1}`,
            color: clusterColors[index % clusterColors.length],
            elements: cluster,
        }));

        setElapsedTime(Date.now() - elapsedTime); // Calculer le temps écoulé

        // Redirection vers la page des résultats avec les données
        navigate("/results", {
            state: {
                sessionId: sessionId, // Passer l'ID de session ici
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
                            onClick={() => handlePlaySound(item)}
                        >
                            <Rect
                                width={parseInt(size)}
                                height={parseInt(size)}
                                fill={item.button_color || buttonColor}
                                stroke={"black"}
                                strokeWidth={4}
                            />
                            <Text
                                text={`${index + 1}`}
                                fontSize={20}
                                x={parseInt(size) / 2 - 5}
                                y={parseInt(size) / 2 - 10}
                                fill="white"
                            />
                        </Group>
                    ))}

                    {clusters.map((cluster, clusterIndex) => {
                        // Calculer les limites pour dessiner le cercle autour du cluster
                        const minX = Math.min(...cluster.map((g) => g.x));
                        const maxX = Math.max(
                            ...cluster.map((g) => g.x + parseInt(size))
                        );
                        const minY = Math.min(...cluster.map((g) => g.y));
                        const maxY = Math.max(
                            ...cluster.map((g) => g.y + parseInt(size))
                        );
                        const centerX = (minX + maxX) / 2;
                        const centerY = (minY + maxY) / 2;
                        const radius =
                            Math.max(maxX - minX, maxY - minY) / 2 + 20;

                        // Attribuer une couleur différente à chaque cluster
                        const clusterColor =
                            clusterColors[clusterIndex % clusterColors.length];

                        return (
                            <Circle
                                key={clusterIndex}
                                x={centerX}
                                y={centerY}
                                radius={radius}
                                stroke={clusterColor} // Utiliser une couleur différente pour chaque cluster
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
