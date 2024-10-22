import React, { useState } from "react";
import { useNavigate, useParams } from "react-router-dom";

function Login() {
    const { sessionId } = useParams();
    const [participantName, setParticipantName] = useState(
        localStorage.getItem("participantName") || ""
    );
    const [participantEmail, setParticipantEmail] = useState(
        localStorage.getItem("participantEmail") || ""
    );
    const [error, setError] = useState("");
    const navigate = useNavigate();

    // Gestion de l'enregistrement du participant
    const handleRegistration = (e) => {
        e.preventDefault();

        fetch(`/api/experiment/register/${sessionId}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                participant_name: participantName,
                participant_email: participantEmail,
            }),
        })
            .then((response) => {
                if (response.status === 409) {
                    setError(
                        "This email has already been used for this experiment."
                    );
                }
                return response.json();
            })
            .then((data) => {
                if (data.session) {
                    // Stocker les informations dans localStorage
                    localStorage.setItem("participantName", participantName);
                    localStorage.setItem("participantEmail", participantEmail);
                    localStorage.setItem("isRegistered", "true");
                    localStorage.setItem(
                        "session",
                        JSON.stringify(data.session)
                    );

                    // Rediriger vers la page d'expérience
                    navigate(`/experiment/${sessionId}`);
                }
            });
    };

    return (
        <div className="flex flex-col items-center justify-center min-h-screen bg-gray-100">
            <form
                onSubmit={handleRegistration}
                className="bg-white p-6 rounded-lg shadow-lg w-full max-w-md"
            >
                <h2 className="text-2xl font-bold text-gray-900 mb-4">
                    Register for the Experiment
                </h2>
                <div className="mb-4">
                    <label className="block text-gray-700">Name:</label>
                    <input
                        type="text"
                        value={participantName}
                        onChange={(e) => setParticipantName(e.target.value)}
                        className="w-full px-4 py-2 border border-gray-300 rounded-md"
                    />
                </div>
                <div className="mb-4">
                    <label className="block text-gray-700">Email:</label>
                    <input
                        type="email"
                        value={participantEmail}
                        onChange={(e) => setParticipantEmail(e.target.value)}
                        className="w-full px-4 py-2 border border-gray-300 rounded-md"
                    />
                </div>
                {error && <p className="text-red-500">{error}</p>}
                <button
                    type="submit"
                    className="bg-blue-500 text-white font-semibold py-2 px-4 rounded-md hover:bg-blue-600 transition-colors w-full"
                >
                    Start Experiment
                </button>
            </form>
        </div>
    );
}

export default Login;
