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
import { PageWrapper } from "./Utils/PageWrapper.jsx";

const ScrollPage = ({ children }) => (
    <PageWrapper allowScroll={true}>{children}</PageWrapper>
);

const NoScrollPage = ({ children }) => (
    <PageWrapper allowScroll={false}>{children}</PageWrapper>
);

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
                                        {/* Scroll */}
                                        <Route
                                            path="/"
                                            element={
                                                <ScrollPage>
                                                    <Home />
                                                </ScrollPage>
                                            }
                                        />
                                        <Route
                                            path="/experiments/"
                                            element={
                                                <ScrollPage>
                                                    <ExperimentList />
                                                </ScrollPage>
                                            }
                                        />
                                        <Route
                                            path="/experiment-detail/:id"
                                            element={
                                                <ScrollPage>
                                                    <ExperimentDetail />
                                                </ScrollPage>
                                            }
                                        />
                                        <Route
                                            path="/how-it-work"
                                            element={
                                                <ScrollPage>
                                                    <HowItWork />
                                                </ScrollPage>
                                            }
                                        />

                                        <Route
                                            path="/changelog"
                                            element={
                                                <ScrollPage>
                                                    <Changelog />
                                                </ScrollPage>
                                            }
                                        />
                                        {/* No Scroll */}
                                        <Route
                                            path="/login/:sessionId"
                                            element={
                                                <NoScrollPage>
                                                    <Login />
                                                </NoScrollPage>
                                            }
                                        />
                                        <Route
                                            path="/experiment/:sessionId"
                                            element={
                                                <NoScrollPage>
                                                    <ExperimentSession />
                                                </NoScrollPage>
                                            }
                                        />
                                        <Route
                                            path="/results"
                                            element={
                                                <NoScrollPage>
                                                    <Result />
                                                </NoScrollPage>
                                            }
                                        />
                                        <Route
                                            path="/experiment-error"
                                            element={
                                                <NoScrollPage>
                                                    <ExperimentError />
                                                </NoScrollPage>
                                            }
                                        />
                                        <Route
                                            path="/thank-you"
                                            element={
                                                <NoScrollPage>
                                                    <ThankYou />
                                                </NoScrollPage>
                                            }
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
