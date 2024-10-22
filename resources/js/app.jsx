import React from "react";
import ReactDOM from "react-dom/client";
import { Route, BrowserRouter as Router, Routes } from "react-router-dom";
import "../css/app.css";
import ExperimentSession from "./Pages/ExperimentSession";
import Home from "./Pages/Home";
import Login from "./Pages/Login";
import Result from "./Pages/Result";

function App() {
    return (
        <Router>
            <Routes>
                <Route path="/" element={<Home />} />
                <Route path="/login/:sessionId" element={<Login />} />
                <Route
                    path="/experiment/:sessionId"
                    element={<ExperimentSession />}
                />
                <Route path="/results" element={<Result />} />
            </Routes>
        </Router>
    );
}

ReactDOM.createRoot(document.getElementById("root")).render(<App />);
