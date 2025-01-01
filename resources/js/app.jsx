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
import Login from "./Pages/Login";
import Result from "./Pages/Result";
import ExperimentDetail from "./Pages/ExperimentDetail";
import HowItWork from "./Pages/HowItWork";
import Changelog from "./Pages/Changelog";
import { LanguageProvider } from "./Contexts/LanguageContext";
import Home from "./Pages/Home";
import ThankYou from "./Pages/ThankYou";
import { ExperimentStatusProvider } from "./Contexts/ExperimentStatusContext.jsx";
import ExperimentError from "./Pages/ExperimentError.jsx";
import { DisableInspectProvider } from "./Contexts/DisableInspectContext.jsx";

function App() {
    return (
        <Router>
            {/* <DisableInspectProvider> */}
            <LanguageProvider>
                <AuthProvider>
                    <ExperimentsProvider>
                        <ExperimentStatusProvider>
                            <SessionProvider>
                                <Layout>
                                    <Routes>
                                        <Route path="/" element={<Home />} />
                                        <Route
                                            path="/experiments/"
                                            element={<ExperimentList />}
                                        />
                                        <Route
                                            path="/how-it-work"
                                            element={<HowItWork />}
                                        />

                                        <Route
                                            path="/changelog"
                                            element={<Changelog />}
                                        />

                                        <Route
                                            path="/login/:sessionId"
                                            element={<Login />}
                                        />
                                        <Route
                                            path="/experiment-detail/:id"
                                            element={<ExperimentDetail />}
                                        />
                                        <Route
                                            path="/experiment/:sessionId"
                                            element={<ExperimentSession />}
                                        />
                                        <Route
                                            path="/results"
                                            element={<Result />}
                                        />
                                        <Route
                                            path="/experiment-error"
                                            element={<ExperimentError />}
                                        />
                                        <Route
                                            path="/thank-you"
                                            element={<ThankYou />}
                                        />
                                    </Routes>
                                </Layout>
                            </SessionProvider>
                        </ExperimentStatusProvider>
                    </ExperimentsProvider>
                </AuthProvider>
            </LanguageProvider>
            {/* </DisableInspectProvider> */}
        </Router>
    );
}

ReactDOM.createRoot(document.getElementById("root")).render(<App />);
