import {
    AlertCircle,
    CirclePlus,
    Clock,
    Image as ImageIcon,
    Music,
} from "lucide-react";
import React, { useEffect, useState } from "react";
import { useTranslation } from "../Contexts/LanguageContext";
import SpeechToText from "./SpeechToText";
import clsx from "clsx";
import ReactMarkdown from "react-markdown";
import isLightColor from "../Utils/isLightColor.jsx";
import rehypeRaw from "rehype-raw";

function SidePanelResults({
    isOpen,
    groups = [],
    onGroupsChange,
    elapsedTime,
    instruction,
    onSubmit,
    onEditModeChange,
    actionsLog = [],
    sessionId,
}) {
    const { t } = useTranslation();
    const [localGroups, setLocalGroups] = useState(groups);
    const [feedback, setFeedback] = useState("");
    const [errors, setErrors] = useState([]);
    const [editingGroupIndex, setEditingGroupIndex] = useState(null);
    const [showEmptyGroups, setShowEmptyGroups] = useState(false);

    const formatTime = (ms) => {
        const totalSeconds = Math.floor(ms / 1000);
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        let timeString = "";

        if (hours > 0) {
            timeString = `${hours} ${t(
                `sidePanelResults.time.${hours > 1 ? "hours" : "hour"}`
            )}`;
            if (minutes > 0)
                timeString += `${t(
                    "sidePanelResults.time.separator"
                )}${minutes} ${t(
                    `sidePanelResults.time.${
                        minutes > 1 ? "minutes" : "minute"
                    }`
                )}`;
            if (seconds > 0)
                timeString += `${t(
                    "sidePanelResults.time.separator"
                )}${seconds} ${t(
                    `sidePanelResults.time.${
                        seconds > 1 ? "seconds" : "second"
                    }`
                )}`;
        } else if (minutes > 0) {
            timeString = `${minutes} ${t(
                `sidePanelResults.time.${minutes > 1 ? "minutes" : "minute"}`
            )}`;
            if (seconds > 0)
                timeString += `${t(
                    "sidePanelResults.time.separator"
                )}${seconds} ${t(
                    `sidePanelResults.time.${
                        seconds > 1 ? "seconds" : "second"
                    }`
                )}`;
        } else {
            timeString = `${seconds} ${t(
                `sidePanelResults.time.${seconds > 1 ? "seconds" : "second"}`
            )}`;
        }

        return timeString;
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

    useEffect(() => {
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

        const existingNumbers = groups
            .map((g) => parseInt(g.name.match(/\d+/)?.[0] || "0"))
            .sort((a, b) => a - b);

        let nextNumber = 1;
        for (const num of existingNumbers) {
            if (num !== nextNumber) break;
            nextNumber++;
        }

        const newGroup = {
            name: `C${nextNumber}`,
            color: availableColor,
            elements: [],
            comment: "",
        };

        setShowEmptyGroups(true);
        onGroupsChange([
            ...groups.filter(
                (g) => g.elements.length > 0 || g.name !== newGroup.name
            ),
            newGroup,
        ]);
        setEditingGroupIndex(groups.length);
        onEditModeChange(groups.length);
    };

    useEffect(() => {
        if (!showEmptyGroups) {
            setLocalGroups(groups.filter((group) => group.elements.length > 0));
        } else {
            setLocalGroups(groups);
        }
    }, [groups, showEmptyGroups]);

    const handleEditGroup = (index) => {
        if (editingGroupIndex === index) {
            setEditingGroupIndex(null);
            onEditModeChange(null);
            setShowEmptyGroups(false);
            onGroupsChange(groups.filter((g) => g.elements.length > 0));
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
                actionsLog,
                sessionId,
            });
        }
    };

    return (
        <div className="border-l border-gray-500 bg-slate-50 flex flex-col h-full w-full">
            <div className="p-6 border-b border-gray-200 bg-white">
                <h2 className="text-xl font-bold">
                    {!isOpen
                        ? t("sidePanelResults.header.title.inProgress")
                        : t("sidePanelResults.header.title.completed")}
                </h2>
                {isOpen && (
                    <div className="flex flex-col items-center justify-between gap-4 text-gray-600 mt-2">
                        <div className="flex items-center gap-2">
                            <Clock size={16} />
                            {t("sidePanelResults.header.duration")}:{" "}
                            {formatTime(elapsedTime)}
                        </div>
                        <button
                            onClick={handleAddGroup}
                            className="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors flex items-center gap-2"
                        >
                            <CirclePlus />
                            {t("sidePanelResults.header.addGroup")}
                        </button>
                    </div>
                )}
            </div>

            <div className="flex-1 overflow-y-auto">
                <div className="p-4">
                    {!isOpen ? (
                        <ReactMarkdown
                            rehypePlugins={[rehypeRaw]}
                            className="mt-4 text-black prose"
                        >
                            {instruction}
                        </ReactMarkdown>
                    ) : (
                        <div className="space-y-4">
                            {localGroups.map((group, index) => (
                                <div
                                    key={index}
                                    className="bg-white rounded-lg shadow-lg border overflow-hidden"
                                >
                                    <div className="p-4 border-b bg-slate-300">
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
                                                className="flex-1 border rounded-lg px-3 py-2 text-lg font-medium focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                placeholder={t(
                                                    "sidePanelResults.groups.input.name"
                                                )}
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
                                                className="w-12 h-12 rounded-lg border-2 cursor-pointer transition-transform hover:scale-105"
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
                                            placeholder={t(
                                                "sidePanelResults.groups.input.comment"
                                            )}
                                            className="border rounded-lg h-20 text-sm w-full focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        />
                                    </div>

                                    <div className="px-4 py-3 bg-slate-300 border-b flex items-center justify-between">
                                        <button
                                            onClick={() =>
                                                handleEditGroup(index)
                                            }
                                            className={clsx(
                                                "px-4 py-2 rounded-lg transition-all flex items-center gap-2",
                                                editingGroupIndex === index
                                                    ? "bg-blue-500 text-white shadow-lg transform scale-105"
                                                    : "bg-gray-100 text-gray-700 hover:bg-gray-200"
                                            )}
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20"
                                                fill="currentColor"
                                                className="w-5 h-5"
                                            >
                                                {editingGroupIndex === index ? (
                                                    <path d="M10 2a8 8 0 100 16 8 8 0 000-16zm0 14a6 6 0 110-12 6 6 0 010 12zm3.293-7.707a1 1 0 00-1.414-1.414L9 9.586 7.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l3-3z" />
                                                ) : (
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zm-2.207 2.207L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                )}
                                            </svg>
                                            {editingGroupIndex === index
                                                ? t(
                                                      "sidePanelResults.groups.buttons.finish"
                                                  )
                                                : t(
                                                      "sidePanelResults.groups.buttons.edit"
                                                  )}
                                        </button>
                                        <span className="text-sm text-gray-500">
                                            {group.elements.length}{" "}
                                            {t(
                                                `sidePanelResults.groups.elements.count.${
                                                    group.elements.length > 1
                                                        ? "plural"
                                                        : "singular"
                                                }`
                                            )}
                                        </span>
                                    </div>

                                    <div className="p-4 bg-slate-300">
                                        <div className="grid grid-cols-3 gap-3">
                                            {group.elements.map((item) => {
                                                const isImage = isImageUrl(
                                                    item.url
                                                );
                                                const isSound = isSoundUrl(
                                                    item.url
                                                );

                                                return (
                                                    <div
                                                        key={item.id}
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
                                                                <Music
                                                                    className={clsx(
                                                                        "w-4 h-4",
                                                                        isLightColor(
                                                                            group.color
                                                                        )
                                                                            ? "text-black"
                                                                            : "text-white"
                                                                    )}
                                                                />
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
                                                                    {t(
                                                                        "sidePanelResults.groups.elements.prefix.image"
                                                                    )}
                                                                    {item.originalIndex +
                                                                        1}
                                                                </div>
                                                            </div>
                                                        ) : isSound ? (
                                                            <div className="flex flex-col items-center justify-center">
                                                                <span
                                                                    className={`text-sm font-medium ${
                                                                        isLightColor(
                                                                            group.color
                                                                        )
                                                                            ? "text-black"
                                                                            : "text-white"
                                                                    }`}
                                                                >
                                                                    {t(
                                                                        "sidePanelResults.groups.elements.prefix.sound"
                                                                    )}
                                                                    {item.originalIndex +
                                                                        1}
                                                                </span>
                                                            </div>
                                                        ) : (
                                                            <div className="flex flex-col items-center justify-center text-gray-400 gap-2">
                                                                <AlertCircle className="w-6 h-6" />
                                                                <span className="text-xs">
                                                                    {t(
                                                                        "sidePanelResults.groups.elements.unknownType.title"
                                                                    )}
                                                                </span>
                                                            </div>
                                                        )}
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                </div>
                            ))}

                            <div className="bg-slate-300 rounded-lg shadow-md border overflow-hidden">
                                <div className="px-4 py-3 bg-slate-300 border-b flex items-center gap-3">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        className="h-5 w-5 text-gray-600"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fillRule="evenodd"
                                            d="M18 10c0 4.418-3.582 8-8 8s-8-3.582-8-8 3.582-8 8-8 8 3.582 8 8zm-8-5a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1zm0 8a1 1 0 100 2 1 1 0 000-2z"
                                            clipRule="evenodd"
                                        />
                                    </svg>
                                    <h3 className="text-lg font-semibold">
                                        {t("sidePanelResults.feedback.title")}
                                    </h3>
                                </div>
                                <div className="p-4">
                                    <div className="relative">
                                        <SpeechToText
                                            value={feedback}
                                            onChange={setFeedback}
                                            placeholder={t(
                                                "sidePanelResults.feedback.placeholder"
                                            )}
                                            className="min-h-[8rem] w-full rounded-lg border border-gray-200 p-3 pr-12 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="bg-slate-300 rounded-lg shadow-md border p-4">
                                <h3 className="text-lg font-semibold mb-3">
                                    {t(
                                        "sidePanelResults.technicalIssues.title"
                                    )}
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
                                        {t(
                                            "sidePanelResults.technicalIssues.buttons.audio"
                                        )}
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
                                        {t(
                                            "sidePanelResults.technicalIssues.buttons.visual"
                                        )}
                                    </button>
                                </div>

                                {errors.length > 0 && (
                                    <div className="bg-gray-50 rounded-lg p-4">
                                        <h4 className="font-medium mb-2">
                                            {t(
                                                "sidePanelResults.technicalIssues.reportedIssues.title"
                                            )}
                                            :
                                        </h4>
                                        <ul className="space-y-1 text-sm text-gray-600">
                                            {errors.map((error, index) => (
                                                <li
                                                    key={index}
                                                    className="flex items-center gap-2"
                                                >
                                                    <span className="w-2 h-2 rounded-full bg-red-500" />
                                                    <span>
                                                        {t(
                                                            "sidePanelResults.technicalIssues.reportedIssues.prefix"
                                                        )}{" "}
                                                        {error.type}
                                                    </span>
                                                    <span className="text-gray-400">
                                                        {t(
                                                            "sidePanelResults.technicalIssues.reportedIssues.timePrefix"
                                                        )}{" "}
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
                        {t("sidePanelResults.actions.finish")}
                    </button>
                </div>
            )}
        </div>
    );
}

export default SidePanelResults;
