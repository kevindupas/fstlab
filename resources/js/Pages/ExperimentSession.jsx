import React, { useEffect, useState } from "react";
import { useNavigate, useParams } from "react-router-dom";
import KonvaImage from "../Components/KonvaImage";
import KonvaSound from "../Components/KonvaSound";

function ExperimentSession() {
    const { sessionId } = useParams();
    const [experiment, setExperiment] = useState(null);
    const [error, setError] = useState("");
    const [media, setMedia] = useState([]);
    const navigate = useNavigate();

    console.log(experiment);

    useEffect(() => {
        const isRegistered = localStorage.getItem("isRegistered") === "true";
        if (!isRegistered) {
            navigate(`/login/${sessionId}`);
        }
    }, [navigate, sessionId]);

    useEffect(() => {
        fetch(`/api/experiment/session/${sessionId}`)
            .then((response) => response.json())
            .then((data) => {
                if (data.experiment) {
                    setExperiment(data);
                    setMedia(data.media);
                } else {
                    setError("Experiment not found.");
                }
            });
    }, [sessionId]);

    if (!experiment) {
        return (
            <p className="text-center mt-6 text-gray-700">
                Loading experiment...
            </p>
        );
    }

    return (
        <div className="flex flex-col items-center justify-center min-h-screen bg-gray-100">
            {error && <p className="text-red-500">{error}</p>}
            <div className="w-full h-screen">
                {experiment.experiment.type === "sound" ? (
                    <KonvaSound
                        media={media}
                        type={experiment.experiment.type}
                        buttonColor={experiment.experiment.button_color}
                        size={experiment.experiment.button_size}
                    />
                ) : (
                    <KonvaImage media={media} />
                )}
            </div>
        </div>
    );
}

export default ExperimentSession;
