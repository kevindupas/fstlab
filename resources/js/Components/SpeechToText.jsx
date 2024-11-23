import { Mic, MicOff } from "lucide-react";
import React, { useEffect, useRef, useState } from "react";

const SpeechToText = ({ value, onChange, placeholder, className }) => {
    const [isListening, setIsListening] = useState(false);
    const [transcript, setTranscript] = useState(value || "");
    const [recognition, setRecognition] = useState(null);
    const [error, setError] = useState("");
    const [isSupported, setIsSupported] = useState(false);
    const [timeLeft, setTimeLeft] = useState(30);
    const textareaRef = useRef(null);
    const timerRef = useRef(null);
    const recognitionRef = useRef(null);

    // Mettre à jour le parent quand le transcript change
    useEffect(() => {
        onChange(transcript);
    }, [transcript]);

    useEffect(() => {
        const SpeechRecognition =
            window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            setError(
                "Votre navigateur ne supporte pas l'API de reconnaissance vocale. Essayez Chrome ou Edge."
            );
            return;
        }
        setIsSupported(true);

        try {
            const recognitionInstance = new SpeechRecognition();
            recognitionInstance.continuous = true;
            recognitionInstance.interimResults = true;
            recognitionInstance.lang = "fr-FR";

            recognitionInstance.onstart = () => {
                setError("");
                setTimeLeft(30);
                startTimer();
            };

            recognitionInstance.onend = () => {
                setIsListening(false);
                clearInterval(timerRef.current);
            };

            recognitionInstance.onresult = (event) => {
                let currentTranscript = "";
                for (let i = 0; i < event.results.length; i++) {
                    currentTranscript += event.results[i][0].transcript;
                }
                setTranscript(currentTranscript);

                if (textareaRef.current) {
                    textareaRef.current.scrollTop =
                        textareaRef.current.scrollHeight;
                }
            };

            recognitionInstance.onerror = (event) => {
                setIsListening(false);
                clearInterval(timerRef.current);
                setTimeLeft(30);

                switch (event.error) {
                    case "not-allowed":
                        setError(
                            "Accès au microphone refusé. Veuillez autoriser l'accès dans les paramètres de votre navigateur."
                        );
                        break;
                    case "no-speech":
                        setError(
                            "Aucune parole détectée. Essayez de parler plus fort."
                        );
                        break;
                    default:
                        setError(`Erreur: ${event.error}`);
                }
            };

            setRecognition(recognitionInstance);
            recognitionRef.current = recognitionInstance;
        } catch (err) {
            setError(
                "Erreur lors de l'initialisation de la reconnaissance vocale"
            );
        }

        return () => {
            if (recognitionRef.current) {
                recognitionRef.current.stop();
            }
            clearInterval(timerRef.current);
        };
    }, []);

    useEffect(() => {
        if (timeLeft === 0 && isListening && recognitionRef.current) {
            recognitionRef.current.stop();
            setIsListening(false);
            clearInterval(timerRef.current);
        }
    }, [timeLeft, isListening]);

    const startTimer = () => {
        timerRef.current = setInterval(() => {
            setTimeLeft((prevTime) => {
                if (prevTime <= 1) {
                    clearInterval(timerRef.current);
                    if (recognitionRef.current) {
                        recognitionRef.current.stop();
                    }
                    return 0;
                }
                return prevTime - 1;
            });
        }, 1000);
    };

    const stopRecognition = () => {
        if (recognitionRef.current) {
            recognitionRef.current.stop();
        }
        setIsListening(false);
        clearInterval(timerRef.current);
        setTimeLeft(30);
    };

    const toggleListening = async () => {
        if (!recognitionRef.current) {
            setError("La reconnaissance vocale n'est pas initialisée");
            return;
        }

        try {
            if (isListening) {
                stopRecognition();
            } else {
                const stream = await navigator.mediaDevices.getUserMedia({
                    audio: true,
                });
                stream.getTracks().forEach((track) => track.stop());

                recognitionRef.current.start();
                setIsListening(true);
                setError("");
            }
        } catch (err) {
            setError(
                "Erreur d'accès au microphone. Veuillez vérifier les permissions."
            );
            stopRecognition();
        }
    };

    if (!isSupported) {
        return (
            <div className="relative">
                <textarea
                    value={transcript}
                    onChange={(e) => setTranscript(e.target.value)}
                    className={className}
                    placeholder={placeholder}
                />
                <div className="absolute -bottom-6 left-0 right-0 text-red-500 text-xs">
                    {error}
                </div>
            </div>
        );
    }

    return (
        <div className="flex gap-2">
            <textarea
                ref={textareaRef}
                value={transcript}
                onChange={(e) => setTranscript(e.target.value)}
                className={className}
                placeholder={placeholder}
            />

            <div className="flex flex-col items-center gap-2">
                <button
                    onClick={toggleListening}
                    className={`p-2 rounded-full transition-colors ${
                        isListening
                            ? "bg-red-100 hover:bg-red-200 text-red-600"
                            : "bg-gray-100 hover:bg-gray-200 text-gray-600"
                    }`}
                >
                    {isListening ? (
                        <MicOff className="w-4 h-4" />
                    ) : (
                        <Mic className="w-4 h-4" />
                    )}
                </button>
                {isListening && (
                    <div className="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded-full">
                        {timeLeft}s
                    </div>
                )}
            </div>

            {error && (
                <div className="absolute -bottom-6 left-0 right-0 text-red-500 text-xs">
                    {error}
                </div>
            )}
        </div>
    );
};

export default SpeechToText;
