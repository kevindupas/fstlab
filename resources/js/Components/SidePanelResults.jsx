import { AlertCircle, Clock, Image as ImageIcon, Music } from "lucide-react";
import React, { useEffect, useState } from "react";
import SpeechToText from "./SpeechToText";

function SidePanelResults({
    isOpen,
    groups = [],
    onGroupsChange,
    elapsedTime,
    onSubmit,
    onEditModeChange,
}) {
    const [localGroups, setLocalGroups] = useState(groups);
    const [feedback, setFeedback] = useState("");
    const [errors, setErrors] = useState([]);
    const [editingGroupIndex, setEditingGroupIndex] = useState(null);
    const [showEmptyGroups, setShowEmptyGroups] = useState(false);

    const formatTime = (seconds) => {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;
        return `${minutes}:${remainingSeconds.toString().padStart(2, "0")}`;
    };

    const imageExtensions = [".png", ".jpg", ".jpeg", ".gif", ".webp", ".bmp"];
    const soundExtensions = [".wav", ".mp3", ".ogg", ".m4a", ".aac"];

    const isImageUrl = (url) =>
        imageExtensions.some((ext) => url.toLowerCase().endsWith(ext));
    const isSoundUrl = (url) =>
        soundExtensions.some((ext) => url.toLowerCase().endsWith(ext));

    const defaultColors = [
        "#FF0000",
        "#00FF00",
        "#0000FF",
        "#FFFF00",
        "#FF00FF",
        "#00FFFF",
        "#FFA500",
        "#800080",
        "#008000",
        "#000080",
        "#808000",
        "#800000",
    ];

    // useEffect(() => {
    //     const updatedGroups = groups.map((group) => ({
    //         ...group,
    //         comment: group.comment || "",
    //     }));
    //     setLocalGroups(updatedGroups);
    // }, [groups]);

    useEffect(() => {
        // Ne filtre plus les groupes vides si on vient d'en ajouter un
        const filteredGroups = showEmptyGroups
            ? groups
            : groups.filter((group) => group.elements.length > 0);

        const updatedGroups = filteredGroups.map((group) => ({
            ...group,
            comment: group.comment || "",
        }));
        setLocalGroups(updatedGroups);
    }, [groups, showEmptyGroups]);

    const handleGroupChange = (index, field, value) => {
        const updatedGroups = localGroups.map((group, i) => {
            if (i === index) {
                return { ...group, [field]: value };
            }
            return group;
        });
        setLocalGroups(updatedGroups);

        // Reconstruit l'array complet des groupes en préservant les groupes vides
        const allGroups = groups.map((originalGroup) => {
            const updatedGroup = updatedGroups.find(
                (g) => g.elements[0]?.id === originalGroup.elements[0]?.id
            );
            return updatedGroup || originalGroup;
        });

        onGroupsChange(allGroups);
    };

    const handleAddGroup = () => {
        const usedColors = new Set(groups.map((g) => g.color));
        const availableColor =
            defaultColors.find((color) => !usedColors.has(color)) ||
            "#" + Math.floor(Math.random() * 16777215).toString(16);

        const newGroup = {
            name: `Groupe ${groups.length + 1}`,
            color: availableColor,
            elements: [],
            comment: "",
        };

        // Active l'affichage des groupes vides
        setShowEmptyGroups(true);

        // Met à jour les groupes
        const updatedGroups = [...groups, newGroup];
        onGroupsChange(updatedGroups);

        // Force l'édition du nouveau groupe
        const newIndex = updatedGroups.length - 1;
        setEditingGroupIndex(newIndex);
        onEditModeChange(newIndex);
    };

    const handleEditGroup = (index) => {
        if (editingGroupIndex === index) {
            setEditingGroupIndex(null);
            onEditModeChange(null);

            // Si on termine l'édition, on peut cacher les groupes vides
            setShowEmptyGroups(false);
        } else {
            setEditingGroupIndex(index);
            onEditModeChange(index);
        }
    };

    const handleSubmit = () => {
        if (onSubmit) {
            onSubmit({
                groups: localGroups,
                feedback,
                errors,
                elapsedTime,
            });
        }
    };

    return (
        <div className="border-l border-gray-500 bg-slate-50 flex-shrink-0 flex flex-col h-screen">
            <div className="p-6 border-b border-gray-200 bg-white">
                <h2 className="text-2xl font-bold">
                    {!isOpen ? "Session en cours" : "Résultats de la session"}
                </h2>
                {isOpen && (
                    <div className="flex items-center justify-between gap-2 text-gray-600 mt-2">
                        <div className="flex items-center gap-2">
                            <Clock size={16} />
                            <p>Total Time: {formatTime(elapsedTime)}</p>
                        </div>
                        <button
                            onClick={handleAddGroup}
                            className="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors flex items-center gap-2"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="16"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                strokeWidth="2"
                                strokeLinecap="round"
                                strokeLinejoin="round"
                            >
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Ajouter un groupe
                        </button>
                    </div>
                )}
            </div>

            <div className="flex-1 overflow-y-auto">
                <div className="p-4">
                    {!isOpen ? (
                        <div className="flex flex-col items-center justify-center text-center p-6">
                            <AlertCircle className="h-20 w-20 text-gray-400 mb-4" />
                            <h3 className="text-xl font-semibold text-gray-700 mb-2">
                                Session en cours
                            </h3>
                            <p className="text-gray-500">
                                Regroupez les éléments puis cliquez sur
                                "Terminer"
                            </p>
                        </div>
                    ) : (
                        <div className="space-y-4">
                            {localGroups.map((group, index) => (
                                <div
                                    key={index}
                                    className="bg-white rounded-lg shadow-md border"
                                >
                                    <div className="p-3 border-b bg-gray-50">
                                        <div className="flex items-center gap-4 mb-4">
                                            <input
                                                type="text"
                                                value={group.name}
                                                onChange={(e) =>
                                                    handleGroupChange(
                                                        index,
                                                        "name",
                                                        e.target.value
                                                    )
                                                }
                                                className="flex-1 border rounded text-lg font-medium"
                                                placeholder="Nom du groupe"
                                            />
                                            <input
                                                type="color"
                                                value={group.color}
                                                onChange={(e) =>
                                                    handleGroupChange(
                                                        index,
                                                        "color",
                                                        e.target.value
                                                    )
                                                }
                                                className="w-11 h-11 rounded border-2 border-black cursor-pointer"
                                            />
                                        </div>
                                        <SpeechToText
                                            value={group.comment}
                                            onChange={(value) =>
                                                handleGroupChange(
                                                    index,
                                                    "comment",
                                                    value
                                                )
                                            }
                                            placeholder="Commentaire sur ce groupe..."
                                            className="border rounded h-20 text-sm w-[20.6rem]"
                                        />
                                    </div>
                                    <button
                                        onClick={() => handleEditGroup(index)}
                                        className={`px-3 py-1 rounded-md transition-colors ${
                                            editingGroupIndex === index
                                                ? "bg-blue-500 text-white"
                                                : "bg-gray-200 hover:bg-gray-300"
                                        }`}
                                    >
                                        {editingGroupIndex === index
                                            ? "Terminer"
                                            : "Modifier"}
                                    </button>

                                    <div className="p-4">
                                        <div className="grid grid-cols-3 gap-3">
                                            {group.elements.map(
                                                (item, elemIndex) => {
                                                    const isImage = isImageUrl(
                                                        item.url
                                                    );
                                                    const isSound = isSoundUrl(
                                                        item.url
                                                    );

                                                    return (
                                                        <div
                                                            key={elemIndex}
                                                            className="aspect-square rounded-lg overflow-hidden border flex items-center justify-center relative group"
                                                            style={{
                                                                backgroundColor:
                                                                    isSound
                                                                        ? group.color
                                                                        : "transparent",
                                                            }}
                                                        >
                                                            {isSound && (
                                                                <div className="absolute top-2 right-2">
                                                                    <Music className="w-4 h-4 text-white" />
                                                                </div>
                                                            )}

                                                            {isImage ? (
                                                                <div className="relative w-full h-full">
                                                                    <img
                                                                        src={
                                                                            item.url
                                                                        }
                                                                        alt=""
                                                                        className="w-full h-full object-cover"
                                                                    />
                                                                    <div className="absolute bottom-2 left-2 bg-black bg-opacity-50 px-2 py-1 rounded text-white text-xs">
                                                                        p
                                                                        {elemIndex +
                                                                            1}
                                                                    </div>
                                                                </div>
                                                            ) : isSound ? (
                                                                <div className="flex flex-col items-center justify-center text-white">
                                                                    <span className="text-sm font-medium">
                                                                        s
                                                                        {elemIndex +
                                                                            1}
                                                                    </span>
                                                                </div>
                                                            ) : (
                                                                <div className="flex flex-col items-center justify-center text-gray-400 gap-2">
                                                                    <AlertCircle className="w-6 h-6" />
                                                                    <span className="text-xs">
                                                                        Type non
                                                                        reconnu
                                                                    </span>
                                                                </div>
                                                            )}
                                                        </div>
                                                    );
                                                }
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}

                            <div className="bg-white rounded-lg shadow-md border p-4">
                                <h3 className="text-lg font-semibold mb-3">
                                    Commentaire global
                                </h3>
                                <SpeechToText
                                    value={feedback}
                                    onChange={setFeedback}
                                    placeholder="Commentaire général sur l'expérience..."
                                    className="h-32"
                                />
                            </div>

                            <div className="bg-white rounded-lg shadow-md border p-4">
                                <h3 className="text-lg font-semibold mb-3">
                                    Problèmes techniques
                                </h3>
                                <div className="flex gap-3 mb-4">
                                    <button
                                        onClick={() =>
                                            setErrors([
                                                ...errors,
                                                {
                                                    time: Date.now(),
                                                    type: "audio",
                                                },
                                            ])
                                        }
                                        className="flex items-center justify-center gap-2 flex-1 bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 transition-colors"
                                    >
                                        <Music className="w-4 h-4" />
                                        Problème Audio
                                    </button>
                                    <button
                                        onClick={() =>
                                            setErrors([
                                                ...errors,
                                                {
                                                    time: Date.now(),
                                                    type: "visual",
                                                },
                                            ])
                                        }
                                        className="flex items-center justify-center gap-2 flex-1 bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 transition-colors"
                                    >
                                        <ImageIcon className="w-4 h-4" />
                                        Problème Visuel
                                    </button>
                                </div>

                                {errors.length > 0 && (
                                    <div className="bg-gray-50 rounded-lg p-4">
                                        <h4 className="font-medium mb-2">
                                            Problèmes signalés :
                                        </h4>
                                        <ul className="space-y-1 text-sm text-gray-600">
                                            {errors.map((error, index) => (
                                                <li
                                                    key={index}
                                                    className="flex items-center gap-2"
                                                >
                                                    <span className="w-2 h-2 rounded-full bg-red-500" />
                                                    <span>
                                                        Problème {error.type}
                                                    </span>
                                                    <span className="text-gray-400">
                                                        à{" "}
                                                        {new Date(
                                                            error.time
                                                        ).toLocaleTimeString()}
                                                    </span>
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {isOpen && (
                <div className="p-4 border-t border-gray-500 bg-white">
                    <button
                        onClick={handleSubmit}
                        className="w-full bg-green-500 text-white font-semibold py-3 px-4 rounded-lg hover:bg-green-600 transition-colors"
                    >
                        Terminer l'expérience
                    </button>
                </div>
            )}
        </div>
    );
}

export default SidePanelResults;
