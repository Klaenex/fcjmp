import React from "react";
import { cfg } from "./config";

import SideMenu from "./components/SideMenu.jsx";
import OffresForm from "./components/OffresForm.jsx";
import ActivitesForm from "./components/ActivitesForm.jsx";
import MyListing from "./components/MyListing.jsx";
import ModerationOffres from "./components/ModerationOffres.jsx";

export default function App() {
  const canModerateOffres = !!(
    cfg.types?.offres?.caps?.can_publish ||
    cfg.types?.offres?.caps?.can_edit_others
  );

  // Si l’utilisateur n’a pas la modération, on ne propose pas cette vue comme défaut
  const [view, setView] = React.useState("add-offre"); // 'add-offre' | 'add-activite' | 'listing' | 'moderation-offres'

  // Sécurité : si quelqu'un tente d’accéder à 'moderation-offres' sans droits
  React.useEffect(() => {
    if (view === "moderation-offres" && !canModerateOffres) {
      setView("listing");
    }
  }, [view, canModerateOffres]);

  return (
    <div className="im-app">
      <div className="im-shell">
        <SideMenu current={view} onChange={setView} />

        <main className="im-main">
          <header
            className="im-card im-row"
            style={{ justifyContent: "space-between" }}
          >
            <h2>Espace membre</h2>
            <div>
              Connecté :{" "}
              <strong>{cfg.currentUser?.name || "Utilisateur"}</strong>
            </div>
          </header>

          {view === "add-offre" && (
            <OffresForm
              onCreated={() => {
                /* éventuellement rechargements */
              }}
            />
          )}

          {view === "add-activite" && (
            <ActivitesForm
              onCreated={() => {
                /* idem */
              }}
            />
          )}

          {view === "listing" && <MyListing />}

          {view === "moderation-offres" && canModerateOffres && (
            <ModerationOffres />
          )}

          <div className="im-muted">
            Statuts : <code>pending</code> (relecture), <code>publish</code>{" "}
            (publié), <code>rejected</code> (refusé), <code>draft</code>{" "}
            (brouillon).
          </div>
        </main>
      </div>
    </div>
  );
}
