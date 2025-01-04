import React, { useState } from "react";
import { useLocation, useNavigate } from "react-router-dom";
import { useTranslation } from "../Contexts/LanguageContext";

function Result() {
    const { t } = useTranslation();
    const location = useLocation();
    const navigate = useNavigate();
    const { groups, elapsedTime, actionsLog } = location.state || {};
    const sessionData = JSON.parse(localStorage.getItem("session"));
    const sessionId = sessionData ? sessionData.id : null;

    const [updatedGroups, setUpdatedGroups] = useState(groups);
    const [feedback, setFeedback] = useState("");
    const [errors, setErrors] = useState([]);

    const formatTime = (timeInSeconds) => {
        const minutes = Math.floor(timeInSeconds / 60);
        const seconds = timeInSeconds % 60;
        return minutes > 0
            ? `${minutes} ${t(
                  minutes > 1 ? "result.time.minutes" : "result.time.minute"
              )} ${t("result.time.and")} ${seconds} ${t(
                  seconds > 1 ? "result.time.seconds" : "result.time.second"
              )}`
            : `${seconds} ${t(
                  seconds > 1 ? "result.time.seconds" : "result.time.second"
              )}`;
    };

    const handleGroupChange = (index, key, value) => {
        setUpdatedGroups((prevGroups) => {
            const newGroups = prevGroups.map((group, i) =>
                i === index ? { ...group, [key]: value } : group
            );
            return newGroups.filter((group) => group.elements.length > 0);
        });
    };

    const findNextGroupNumber = (groups) => {
        const existingNumbers = groups
            .map((g) => parseInt(g.name.match(/\d+/)?.[0] || "0"))
            .sort((a, b) => a - b);

        for (let i = 1; i <= existingNumbers.length + 1; i++) {
            if (!existingNumbers.includes(i)) {
                return i;
            }
        }
        return existingNumbers.length + 1;
    };

    const addNewGroup = () => {
        const nextNumber = findNextGroupNumber(updatedGroups);
        setUpdatedGroups([
            ...updatedGroups,
            {
                name: `C ${nextNumber}`,
                color: "#" + Math.floor(Math.random() * 16777215).toString(16),
                elements: [],
            },
        ]);
    };

    const handleSendData = async () => {
        const dataToSend = {
            group_data: updatedGroups,
            actions_log: actionsLog,
            duration: elapsedTime,
            feedback: feedback,
            errors_log: errors,
        };

        try {
            const response = await fetch(`/api/experiment/save/${sessionId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(dataToSend),
            });

            if (response.ok) {
                localStorage.removeItem("isRegistered");
                localStorage.removeItem("participantEmail");
                localStorage.removeItem("participantName");
                localStorage.removeItem("session");
                navigate("/");
            }
        } catch (error) {
            console.error("Error sending data:", error);
        }
    };

    if (!groups || !elapsedTime || !actionsLog) {
        return <p className="text-center text-red-500">{t("result.error")}</p>;
    }

    return (
        <div className="p-8 max-w-6xl mx-auto">
            <h2 className="text-3xl font-bold mb-8">{t("result.title")}</h2>
            <p className="text-lg mb-6">
                {t("result.totalTime")}:{" "}
                {formatTime(Math.floor(elapsedTime / 1000))}
            </p>

            {updatedGroups.map((group, index) => (
                <div
                    key={index}
                    className="mb-8 border p-4 rounded-lg shadow-md"
                >
                    <label className="block text-lg font-semibold mb-2">
                        {t("result.groupName")}:
                        <input
                            type="text"
                            value={group.name}
                            onChange={(e) =>
                                handleGroupChange(index, "name", e.target.value)
                            }
                            className="ml-2 p-2 border rounded-md w-full"
                        />
                    </label>

                    <label className="block text-lg font-semibold mb-4">
                        {t("result.groupColor")}:
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
                            className="ml-2 p-2"
                        />
                    </label>

                    <div>
                        <h4 className="text-lg font-semibold mb-4">
                            {t("result.mediaInGroup")}:
                        </h4>
                        <div className="flex space-x-4">
                            {group.elements.map((item) => (
                                <div key={item.id}>
                                    {item.type === "sound" ? (
                                        <div
                                            className="flex items-center justify-center w-24 h-24 border-2 border-black"
                                            style={{
                                                backgroundColor: group.color,
                                            }}
                                        >
                                            <button
                                                onClick={() =>
                                                    new Audio(item.url).play()
                                                }
                                                className="text-white font-bold"
                                            >
                                                {t("result.playSound")}
                                            </button>
                                        </div>
                                    ) : (
                                        <div className="flex w-24 h-24 border-2 border-black">
                                            <img src={item.url} alt="" />
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            ))}

            <div className="mt-8 border p-4 rounded-lg shadow-md">
                <h3 className="text-2xl font-bold mb-4">
                    {t("result.feedbackSection.title")}
                </h3>
                <div className="mb-4">
                    <label className="block text-lg font-semibold mb-2">
                        {t("result.feedbackSection.label")}:
                    </label>
                    <textarea
                        value={feedback}
                        onChange={(e) => setFeedback(e.target.value)}
                        className="w-full p-2 border rounded-md h-32"
                        placeholder={t("result.feedbackSection.placeholder")}
                    />
                </div>
            </div>

            <div className="mt-8 border p-4 rounded-lg shadow-md">
                <h3 className="text-2xl font-bold mb-4">
                    {t("result.technicalIssues.title")}
                </h3>
                <div className="mb-4">
                    <label className="block text-lg font-semibold mb-2">
                        {t("result.technicalIssues.label")}
                    </label>
                    <div className="flex gap-2 mb-2">
                        <button
                            onClick={() =>
                                setErrors([
                                    ...errors,
                                    { time: Date.now(), type: "audio" },
                                ])
                            }
                            className="bg-red-500 text-white px-4 py-2 rounded-md"
                        >
                            {t("result.technicalIssues.reportAudio")}
                        </button>
                        <button
                            onClick={() =>
                                setErrors([
                                    ...errors,
                                    { time: Date.now(), type: "visual" },
                                ])
                            }
                            className="bg-red-500 text-white px-4 py-2 rounded-md"
                        >
                            {t("result.technicalIssues.reportVisual")}
                        </button>
                    </div>
                    {errors.length > 0 && (
                        <div className="mt-2">
                            <h4 className="font-semibold">
                                {t("result.technicalIssues.reportedIssues")}:
                            </h4>
                            <ul className="list-disc list-inside">
                                {errors.map((error, index) => (
                                    <li key={index}>
                                        {error.type}{" "}
                                        {t("result.technicalIssues.issueAt")}{" "}
                                        {new Date(
                                            error.time
                                        ).toLocaleTimeString()}
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>
            </div>

            <div className="mt-8">
                <button
                    onClick={handleSendData}
                    className="bg-green-500 text-white font-bold py-2 px-4 rounded-md hover:bg-green-600 transition duration-200"
                >
                    {t("result.submit")}
                </button>
            </div>
        </div>
    );
}

export default Result;
