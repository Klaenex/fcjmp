import React from "react";
import { cfg } from "../config";

export default function SideMenu({ current, onChange }) {
  const canModerateOffres = !!(
    cfg.types?.offres?.caps?.can_publish ||
    cfg.types?.offres?.caps?.can_edit_others
  );

  return (
    <aside className="im-sidebar">
      <div className="im-sidelogo">
        <strong>Mon espace</strong>
      </div>

      <div className="im-sidegroup">
        <button
          className={`im-sidelink ${current === "add-offre" ? "active" : ""}`}
          onClick={() => onChange("add-offre")}
          type="button"
        >
          ➕ Ajouter une offre
        </button>

        <button
          className={`im-sidelink ${
            current === "add-activite" ? "active" : ""
          }`}
          onClick={() => onChange("add-activite")}
          type="button"
        >
          ➕ Ajouter une activité
        </button>

        <button
          className={`im-sidelink ${current === "listing" ? "active" : ""}`}
          onClick={() => onChange("listing")}
          type="button"
        >
          📄 Listing (mes contenus)
        </button>

        {canModerateOffres && (
          <button
            className={`im-sidelink ${
              current === "moderation-offres" ? "active" : ""
            }`}
            onClick={() => onChange("moderation-offres")}
            type="button"
          >
            ✅ Modération offres
          </button>
        )}
      </div>
    </aside>
  );
}
