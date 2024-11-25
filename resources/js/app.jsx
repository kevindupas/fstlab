import React from "react";
import ReactDOM from "react-dom/client";
import { Route, BrowserRouter as Router, Routes } from "react-router-dom";
import "../css/app.css";
import { Layout } from "./Layouts/Layout";
import { AuthProvider } from "./Contexts/AuthContext";
import { ExperimentsProvider } from "./Contexts/ExperimentsContext";
import { SessionProvider } from "./Contexts/SessionContext";
import ExperimentList from "./Pages/ExperimentList";
import ExperimentSession from "./Pages/ExperimentSession";
import Home from "./Pages/Home";
import Login from "./Pages/Login";
import Result from "./Pages/Result";
import HomeTwo from "./Pages/HomeTwo";

function App() {
    return (
        <Router>
            <AuthProvider>
                <ExperimentsProvider>
                    <SessionProvider>
                        <Layout>
                            <Routes>
                                <Route path="/" element={<HomeTwo />} />
                                <Route
                                    path="/login/:sessionId"
                                    element={<Login />}
                                />
                                <Route
                                    path="/experiments/"
                                    element={<ExperimentList />}
                                />
                                <Route
                                    path="/experiment/:sessionId"
                                    element={<ExperimentSession />}
                                />
                                <Route path="/results" element={<Result />} />
                            </Routes>
                        </Layout>
                    </SessionProvider>
                </ExperimentsProvider>
            </AuthProvider>
        </Router>
    );
}

ReactDOM.createRoot(document.getElementById("root")).render(<App />);
