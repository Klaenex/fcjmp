import React from "react";
import { listOffresPending, accepterOffre, rejeterOffre } from "../api/content";
import { cfg } from "../config";

const canModerate = !!(
  cfg.types?.offres?.caps?.can_publish ||
  cfg.types?.offres?.caps?.can_edit_others
);

export default function ModerationOffres() {
  const [page, setPage] = React.useState(1);
  const [state, setState] = React.useState({
    items: [],
    loading: false,
    error: "",
  });

  async function load(p = 1) {
    if (!canModerate) return;
    setState((s) => ({ ...s, loading: true, error: "" }));
    try {
      const data = await listOffresPending({ page: p, perPage: 10 });
      setState({ items: data, loading: false, error: "" });
    } catch (e) {
      setState({ items: [], loading: false, error: String(e) });
    }
  }

  React.useEffect(() => {
    load(page);
  }, [page]);

  if (!canModerate)
    return (
      <p className="im-muted">
        (La modération des offres est réservée aux rôles autorisés.)
      </p>
    );

  async function doAccept(id) {
    await accepterOffre(id);
    await load(page);
  }
  async function doReject(id) {
    await rejeterOffre(id);
    await load(page);
  }

  return (
    <section className="im-card" style={{ display: "grid", gap: 12 }}>
      <h3>Modération — Offres en attente</h3>
      <div className="im-row">
        <button className="im-btn" onClick={() => load(page)}>
          ⏳ Recharger
        </button>
        {state.loading ? <span>Chargement…</span> : null}
        {state.error ? (
          <span style={{ color: "#b00020" }}>{state.error}</span>
        ) : null}
      </div>

      <ul className="im-list">
        {state.items.map((p) => (
          <li key={p.id} className="im-card">
            <div className="im-row" style={{ justifyContent: "space-between" }}>
              <strong
                dangerouslySetInnerHTML={{
                  __html: p.title?.rendered || "(Sans titre)",
                }}
              />
              <span className="im-muted">
                par {p._embedded?.author?.[0]?.name || "?"} — #{p.id}
              </span>
            </div>
            <div className="im-muted">
              Créé le {new Date(p.date).toLocaleString()}
            </div>
            <div
              style={{ marginTop: 6 }}
              dangerouslySetInnerHTML={{ __html: p.excerpt?.rendered || "" }}
            />
            <div className="im-actions" style={{ marginTop: 8 }}>
              <button className="im-btn" onClick={() => doAccept(p.id)}>
                ✅ Accepter
              </button>
              <button className="im-btn" onClick={() => doReject(p.id)}>
                ⛔ Rejeter
              </button>
            </div>
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
