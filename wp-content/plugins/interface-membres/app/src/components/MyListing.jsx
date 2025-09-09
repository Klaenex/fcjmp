import React from "react";
import { cfg } from "../config";
import { listMine } from "../api/content";

/** Badge de statut lisible */
function StatusBadge({ s }) {
  let label = "Brouillon";
  if (s === "publish") label = "Publié";
  else if (s === "pending") label = "En attente";
  else if (s === "rejected") label = "Rejeté";
  return <span className="im-badge">{label}</span>;
}

/**
 * MyListing : liste les contenus de l'utilisateur (Offres / Activités)
 * - filtres : type (offres/activites), statut, recherche
 * - pagination simple (←/→), sans total (désactive "Suivant" si < perPage)
 */
export default function MyListing() {
  // Filtres
  const [type, setType] = React.useState(
    cfg.types?.offres ? "offres" : Object.keys(cfg.types || {})[0] || "offres"
  );
  const [status, setStatus] = React.useState("all"); // 'all' | 'pending' | 'publish' | 'rejected' | 'draft'
  const [search, setSearch] = React.useState("");

  // Pagination
  const [page, setPage] = React.useState(1);
  const perPage = 10;

  // Données
  const [state, setState] = React.useState({
    items: [],
    loading: false,
    error: "",
    hasMore: false,
  });

  /** Construit la valeur "status" pour l'API selon le filtre */
  function buildStatusParam() {
    if (status === "all") {
      return ["pending", "publish", "rejected", "draft"];
    }
    return status;
  }

  async function load(p = 1) {
    setState((s) => ({ ...s, loading: true, error: "" }));
    try {
      const data = await listMine(type, {
        page: p,
        perPage,
        status: buildStatusParam(),
        search,
      });
      // hasMore si on a récupéré un plein "perPage"
      const hasMore = Array.isArray(data) && data.length === perPage;
      setState({
        items: Array.isArray(data) ? data : [],
        loading: false,
        error: "",
        hasMore,
      });
    } catch (e) {
      setState({ items: [], loading: false, error: String(e), hasMore: false });
    }
  }

  // recharger quand filtres changent
  React.useEffect(() => {
    setPage(1);
  }, [type, status, search]);

  React.useEffect(() => {
    load(page);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [page, type, status]);

  function onSubmitSearch(e) {
    e.preventDefault();
    setPage(1);
    load(1);
  }

  function ItemCard({ p }) {
    const title = p?.title?.rendered || "(Sans titre)";
    const excerpt = p?.excerpt?.rendered || "";
    const link = p?.link || "#";
    const date = p?.date ? new Date(p.date).toLocaleString() : "";
    const author = p?._embedded?.author?.[0]?.name || "?";

    return (
      <li className="im-card" key={p.id}>
        <div className="im-row" style={{ justifyContent: "space-between" }}>
          <strong dangerouslySetInnerHTML={{ __html: title }} />
          <StatusBadge s={p.status} />
        </div>
        <div className="im-muted">
          #{p.id} · {date} · par {author}
        </div>
        <div
          style={{ marginTop: 6 }}
          dangerouslySetInnerHTML={{ __html: excerpt }}
        />
        <div className="im-actions" style={{ marginTop: 8 }}>
          <a className="im-btn" href={link} target="_blank" rel="noreferrer">
            Ouvrir
          </a>
        </div>
      </li>
    );
  }

  return (
    <section className="im-card" style={{ display: "grid", gap: 12 }}>
      <div className="im-row" style={{ justifyContent: "space-between" }}>
        <h3>Listing — mes contenus</h3>
      </div>

      {/* Filtres */}
      <div className="im-row" style={{ gap: 12 }}>
        <div style={{ minWidth: 220, flex: "0 0 220px" }}>
          <label className="im-muted">Type</label>
          <select
            className="im-input"
            value={type}
            onChange={(e) => setType(e.target.value)}
          >
            {/* N’affiche que Offres et Activités si ce sont les seuls dans cfg */}
            {Object.entries(cfg.types || {}).map(([slug, def]) => (
              <option key={slug} value={slug}>
                {def.label || slug}
              </option>
            ))}
          </select>
        </div>

        <div style={{ minWidth: 200, flex: "0 0 200px" }}>
          <label className="im-muted">Statut</label>
          <select
            className="im-input"
            value={status}
            onChange={(e) => setStatus(e.target.value)}
          >
            <option value="all">Tous</option>
            <option value="pending">En attente</option>
            <option value="publish">Publié</option>
            <option value="rejected">Rejeté</option>
            <option value="draft">Brouillon</option>
          </select>
        </div>

        <form
          onSubmit={onSubmitSearch}
          style={{ display: "flex", gap: 8, alignItems: "end", flex: 1 }}
        >
          <div style={{ width: "100%" }}>
            <label className="im-muted">Recherche (titre/contenu)</label>
            <input
              className="im-input"
              placeholder="ex: animateur, festival, stage…"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
            />
          </div>
          <button
            className="im-btn"
            type="submit"
            style={{ height: 40, alignSelf: "end" }}
          >
            Rechercher
          </button>
        </form>

        <button
          className="im-btn"
          onClick={() => load(page)}
          style={{ height: 40, alignSelf: "end" }}
        >
          Recharger
        </button>
      </div>

      {/* Liste */}
      {state.loading ? <p>Chargement…</p> : null}
      {state.error ? <p style={{ color: "#b00020" }}>{state.error}</p> : null}

      <ul className="im-list">
        {state.items.map((p) => (
          <ItemCard key={p.id} p={p} />
        ))}
      </ul>

      {/* Pagination */}
      <div className="im-row">
        <button
          className="im-btn"
          onClick={() => setPage((n) => Math.max(1, n - 1))}
          disabled={page <= 1 || state.loading}
        >
          ← Précédent
        </button>
        <span>Page {page}</span>
        <button
          className="im-btn"
          onClick={() => setPage((n) => n + 1)}
          disabled={!state.hasMore || state.loading}
        >
          Suivant →
        </button>
      </div>
    </section>
  );
}
