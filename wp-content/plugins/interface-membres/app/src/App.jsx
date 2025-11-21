import React from "react";
import {
  createHashRouter,
  RouterProvider,
  Navigate,
  Outlet,
} from "react-router-dom";
import { cfg } from "./config";

import SideMenu from "./components/SideMenu";
import AddOffrePage from "./pages/AddOffrePage";
import AddActivitePage from "./pages/AddActivitePage";
import ListingPage from "./pages/ListingPage";
import ModerationOffresPage from "./pages/ModerationOffresPage";

function Layout() {
  return (
    <div className="im-app">
      <div className="im-shell">
        <SideMenu />
        <main className="im-main">
          <header
            className="im-card im-row"
            style={{ justifyContent: "space-between" }}
          >
            <div>
              Connecté :{" "}
              <strong>{cfg.currentUser?.name || "Utilisateur"}</strong>
            </div>
          </header>

          <Outlet />

          <div className="im-muted" style={{ marginTop: 12 }}>
            Statuts : <code>pending</code>, <code>publish</code>,{" "}
            <code>rejected</code>, <code>draft</code>.
          </div>
        </main>
      </div>
    </div>
  );
}

export default function App() {
  const canModerateOffres = !!(
    cfg.types?.offres?.caps?.can_publish ||
    cfg.types?.offres?.caps?.can_edit_others
  );

  const childRoutes = [
    { index: true, element: <Navigate to="add-offre" replace /> },
    { path: "add-offre", element: <AddOffrePage /> },
    { path: "add-activite", element: <AddActivitePage /> },
    { path: "listing", element: <ListingPage /> },
  ];

  if (canModerateOffres)
    childRoutes.push({
      path: "moderation-offres",
      element: <ModerationOffresPage />,
    });

  const router = createHashRouter([
    { path: "/", element: <Layout />, children: childRoutes },
    { path: "*", element: <Navigate to="/" replace /> },
  ]);

  return <RouterProvider router={router} />;
}
