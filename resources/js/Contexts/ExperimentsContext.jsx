import React, { createContext, useContext, useEffect, useState } from "react";

const ExperimentsContext = createContext({
    experiments: [],
    isLoading: true,
    error: null,
    refetchExperiments: () => {},
});

export function ExperimentsProvider({ children }) {
    const [experiments, setExperiments] = useState([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchExperiments = async () => {
        try {
            setIsLoading(true);
            const response = await fetch("/api/experiments");
            const data = await response.json();
            setExperiments(data);
            setError(null);
        } catch (err) {
            setError(
                "Une erreur s'est produite lors du chargement des expériences."
            );
            console.error("Error fetching experiments:", err);
        } finally {
            setIsLoading(false);
        }
    };

    useEffect(() => {
        fetchExperiments();
    }, []);

    const value = {
        experiments,
        isLoading,
        error,
        refetchExperiments: fetchExperiments,
    };

    return (
        <ExperimentsContext.Provider value={value}>
            {children}
        </ExperimentsContext.Provider>
    );
}

export const useExperiments = () => useContext(ExperimentsContext);
