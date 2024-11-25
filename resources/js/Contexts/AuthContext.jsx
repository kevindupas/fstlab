import React, {
    createContext,
    useCallback,
    useContext,
    useEffect,
    useState,
} from "react";

const AuthContext = createContext({
    user: null,
    isAuthenticated: false,
    isLoading: true,
    error: null,
    refreshAuth: () => {},
});

export function AuthProvider({ children }) {
    const [user, setUser] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState(null);

    const fetchAuthStatus = useCallback(async () => {
        try {
            setIsLoading(true);
            setError(null);

            const response = await fetch("/api/user/auth-status", {
                method: "GET",
                credentials: "include",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content"),
                },
            });

            if (!response.ok) {
                const text = await response.text();
                console.error("Response error:", text);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log("Auth response:", data);

            if (data.isAuthenticated && data.user) {
                setUser(data.user);
            } else {
                setUser(null);
            }
        } catch (err) {
            console.error("Erreur d'authentification:", err);
            setError(err.message);
            setUser(null);
        } finally {
            setIsLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchAuthStatus();
    }, [fetchAuthStatus]);

    const value = {
        user,
        isAuthenticated: !!user,
        isLoading,
        error,
        refreshAuth: fetchAuthStatus,
    };

    return (
        <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
    );
}

export const useAuth = () => useContext(AuthContext);
