import React, { useState } from "react";
import { useNavigate } from "react-router-dom";

function Home() {
    const [showModal, setShowModal] = useState(false);
    const [sessionId, setSessionId] = useState("");
    const [error, setError] = useState("");
    const navigate = useNavigate();

    // Ouvrir ou fermer la modal
    const toggleModal = () => {
        setShowModal(!showModal);
        setError("");
        setSessionId("");
    };

    // Valider le lien de l'expérience
    const handleStartExperiment = (e) => {
        e.preventDefault();

        fetch(`/api/experiment/session/${sessionId}`)
            .then((response) => response.json())
            .then((data) => {
                if (data.experiment) {
                    // Si l'expérience est trouvée, rediriger vers la page d'expérience
                    navigate(`/experiment/${sessionId}`);
                } else {
                    setError("Invalid session ID. Please try again.");
                }
            })
            .catch(() => {
                setError("An error occurred. Please try again.");
            });
    };

    return (
        <div className="flex flex-col items-center justify-center min-h-screen bg-gray-100">
            <h1 className="text-3xl font-bold text-gray-900">
                Welcome to the Experiment Platform
            </h1>
            <p className="mt-4 text-lg text-gray-700 text-center max-w-xl">
                This platform allows you to participate in various experiments.
                Click the button below to start an experiment.
            </p>
            <button
                className="mt-8 bg-blue-500 text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-600 transition-colors"
                onClick={toggleModal}
            >
                Start Experimentation
            </button>

            {showModal && (
                <div className="fixed inset-0 bg-gray-800 bg-opacity-75 flex justify-center items-center">
                    <div className="bg-white p-6 rounded-lg shadow-lg max-w-md w-full">
                        <h2 className="text-2xl font-bold text-gray-900 mb-4">
                            Enter the experiment link
                        </h2>
                        <form onSubmit={handleStartExperiment}>
                            <input
                                type="text"
                                placeholder="Enter session ID"
                                value={sessionId}
                                onChange={(e) => setSessionId(e.target.value)}
                                className="w-full px-4 py-2 border border-gray-300 rounded-md mb-4"
                            />
                            <button
                                type="submit"
                                className="bg-blue-500 text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-600 transition-colors w-full"
                            >
                                Submit
                            </button>
                        </form>
                        {error && <p className="text-red-500 mt-4">{error}</p>}
                        <button
                            onClick={toggleModal}
                            className="mt-4 bg-gray-500 text-white font-semibold py-2 px-4 rounded-md hover:bg-gray-600 transition-colors w-full"
                        >
                            Close
                        </button>
                    </div>
                </div>
            )}
        </div>
    );
}

export default Home;
