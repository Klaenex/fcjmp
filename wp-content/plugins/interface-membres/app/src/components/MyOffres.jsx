import React from "react";
import { listMesOffres } from "../api/content";

function Status({ s }) {
  const label =
    s === "publish"
      ? "Publié"
      : s === "pending"
      ? "En attente"
      : s === "rejected"
      ? "Rejeté"
      : "Brouillon";
  return <span className="im-badge">{label}</span>;
}

export default function MyOffres() {
  const [page, setPage] = React.useState(1);
  const [state, setState] = React.useState({
    items: [],
    loading: false,
    error: "",
  });

  async function load(p = 1) {
    setState((s) => ({ ...s, loading: true, error: "" }));
    try {
      const data = await listMesOffres({ page: p, perPage: 10 });
      setState({ items: data, loading: false, error: "" });
    } catch (e) {
      setState({ items: [], loading: false, error: String(e) });
    }
  }

  React.useEffect(() => {
    load(page);
  }, [page]);

  return (
    <section className="im-card" style={{ display: "grid", gap: 12 }}>
      <h3>Mes offres</h3>
      {state.loading ? <p>Chargement…</p> : null}
      {state.error ? <p style={{ color: "#b00020" }}>{state.error}</p> : null}

      <ul className="im-list">
        {state.items.map((p) => (
          <li key={p.id} className="im-card">
            <div className="im-row" style={{ justifyContent: "space-between" }}>
              <strong
                dangerouslySetInnerHTML={{
                  __html: p.title?.rendered || "(Sans titre)",
                }}
              />
              <Status s={p.status} />
            </div>
            <div className="im-muted">
              Créé le {new Date(p.date).toLocaleString()}
            </div>
            <div
              style={{ marginTop: 6 }}
              dangerouslySetInnerHTML={{ __html: p.excerpt?.rendered || "" }}
            />
          </li>
        ))}
      </ul>

      <div className="im-row">
        <button
          className="im-btn"
          onClick={() => setPage((p) => Math.max(1, p - 1))}
          disabled={page <= 1}
        >
          ← Précédent
        </button>
        <span>Page {page}</span>
        <button className="im-btn" onClick={() => setPage((p) => p + 1)}>
          Suivant →
        </button>
      </div>
    </section>
  );
}
