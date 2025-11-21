import React from "react";
import { NavLink } from "react-router-dom";
import { cfg } from "../config";

export default function SideMenu() {
  const canModerateOffres = !!(
    cfg.types?.offres?.caps?.can_publish ||
    cfg.types?.offres?.caps?.can_edit_others
  );
  const linkClass = ({ isActive }) => `im-sidelink ${isActive ? "active" : ""}`;

  return (
    <aside className="im-sidebar">
      <div className="im-sidelogo">
        <strong>Mon espace</strong>
      </div>
      <div className="im-sidegroup">
        <NavLink to="/add-offre" className={linkClass} end>
          ➕ Ajouter une offre
        </NavLink>
        <NavLink to="/add-activite" className={linkClass}>
          ➕ Ajouter une activité
        </NavLink>
        <NavLink to="/listing" className={linkClass}>
          📄 Listing (mes contenus)
        </NavLink>
        {canModerateOffres && (
          <NavLink to="/moderation-offres" className={linkClass}>
            ✅ Modération offres
          </NavLink>
        )}
      </div>
    </aside>
  );
}
