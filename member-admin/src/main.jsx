import React from "react";
import { createRoot } from "react-dom/client";
import App from "./App";
import "./index.css";

const el = document.getElementById("react-espace-membre");
const root = createRoot(el);
root.render(<App />);
