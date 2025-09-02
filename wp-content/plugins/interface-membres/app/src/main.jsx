import React from "react";
import { createRoot } from "react-dom/client";

/* --- Config injectée par le plugin (window.IMAppConfig) --- */
const cfg = window.IMAppConfig || {
  restUrl: "http://localhost:8000/wp-json/",
  nonce: "",
  currentUser: { id: 0, name: "Dev", roles: [] },
  status: {
    draft: "draft",
    pending: "pending",
    publish: "publish",
    rejected: "rejected",
  },
  types: {
    post: {
      label: "Articles",
      rest_base: "posts",
      is_builtin: true,
      supports: ["title", "editor"],
      caps: { can_publish: false, can_edit_others: false },
    },
  },
};

/* --- Helpers REST --- */
function v2(path, params = "") {
  const base = cfg.restUrl.replace(/\/$/, "");
  const p = params ? (params.startsWith("?") ? params : "?" + params) : "";
  return `${base}/wp/v2/${path}${p}`;
}
function custom(path) {
  const base = cfg.restUrl.replace(/\/$/, "");
  return `${base}/im/v1/${path}`;
}
async function wpFetch(url, options = {}) {
  const res = await fetch(url, {
    ...options,
    headers: {
      "X-WP-Nonce": cfg.nonce,
      ...(options.body && !(options.headers && options.headers["Content-Type"])
        ? { "Content-Type": "application/json" }
        : {}),
      ...(options.headers || {}),
    },
    credentials: "same-origin",
  });
  const text = await res.text().catch(() => "");
  if (!res.ok) {
    throw new Error(
      `HTTP ${res.status} ${res.statusText} — ${text || "(vide)"}`
    );
  }
  try {
    return JSON.parse(text);
  } catch {
    return text;
  }
}

/* --- API spécifiques --- */
async function pingMe() {
  return wpFetch(v2("users/me"));
}
async function listMine(type, { limit = 5 } = {}) {
  const restBase = cfg.types[type]?.rest_base || type;
  const params = new URLSearchParams({
    author: String(cfg.currentUser.id),
    status: ["pending", "publish", "rejected", "draft"].join(","),
    per_page: String(limit),
    orderby: "date",
    order: "desc",
    _embed: "1",
  }).toString();
  return wpFetch(v2(restBase, "?" + params));
}
async function createPending(type) {
  const restBase = cfg.types[type]?.rest_base || type;
  const payload = {
    title: `[TEST ${type}] ` + new Date().toLocaleString(),
    content:
      "Contenu de test automatique.\n\nCe contenu doit apparaître en 'pending'.",
    status: cfg.status?.pending || "pending",
  };
  return wpFetch(v2(restBase), {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

/* --- Modération (admin/éditeur) --- */
async function listPending(type, { page = 1, perPage = 10 } = {}) {
  const restBase = cfg.types[type]?.rest_base || type;
  const params = new URLSearchParams({
    status: "pending",
    page: String(page),
    per_page: String(perPage),
    orderby: "date",
    order: "desc",
    _embed: "1",
  }).toString();
  return wpFetch(v2(restBase, "?" + params));
}
async function acceptItem(type, id) {
  return wpFetch(
    custom(`moderation/${encodeURIComponent(type)}/${id}/accept`),
    { method: "POST" }
  );
}
async function rejectItem(type, id) {
  return wpFetch(
    custom(`moderation/${encodeURIComponent(type)}/${id}/reject`),
    { method: "POST" }
  );
}

/* --- UI --- */
function App() {
  const [type, setType] = React.useState(Object.keys(cfg.types)[0] || "post");
  const [log, setLog] = React.useState("");
  const [pending, setPending] = React.useState({
    items: [],
    loading: false,
    error: "",
  });

  function println(msg) {
    setLog((prev) => (prev ? prev + "\n" : "") + msg);
  }

  /* --- Boutons Smoke Test --- */
  async function onPing() {
    try {
      println("→ GET /wp/v2/users/me");
      const data = await pingMe();
      println(
        "← OK users/me : " +
          JSON.stringify(
            { id: data.id, name: data.name, roles: data.roles },
            null,
            2
          )
      );
    } catch (e) {
      println("✗ users/me ERR: " + e.message);
    }
  }

  async function onListMine() {
    try {
      const restBase = cfg.types[type]?.rest_base || type;
      println(
        `→ GET /wp/v2/${restBase}?author=me&status=pending,publish,rejected,draft`
      );
      const data = await listMine(type, { limit: 5 });
      println(
        "← OK list (count=" +
          data.length +
          ")\n" +
          JSON.stringify(
            data.map((p) => ({
              id: p.id,
              status: p.status,
              title: p.title?.rendered,
            })),
            null,
            2
          )
      );
    } catch (e) {
      println("✗ list ERR: " + e.message);
    }
  }

  async function onCreate() {
    try {
      const restBase = cfg.types[type]?.rest_base || type;
      const payload = {
        title: `[TEST ${type}] ` + new Date().toLocaleString(),
        content:
          "Contenu de test automatique.\n\nCe contenu doit apparaître en 'pending'.",
        status: cfg.status?.pending || "pending",
      };
      println(`→ POST /wp/v2/${restBase} payload=` + JSON.stringify(payload));
      const data = await createPending(type);
      println(
        "← OK create id=" +
          data.id +
          " status=" +
          data.status +
          " title=" +
          (data.title?.rendered || data.title)
      );
    } catch (e) {
      println("✗ create ERR: " + e.message);
    }
  }

  /* --- Modération --- */
  const canModerate = !!(
    cfg.types?.[type]?.caps?.can_publish ||
    cfg.types?.[type]?.caps?.can_edit_others
  );

  async function loadPending() {
    if (!canModerate) return;
    setPending((s) => ({ ...s, loading: true, error: "" }));
    try {
      const data = await listPending(type, { page: 1, perPage: 10 });
      setPending({ items: data, loading: false, error: "" });
    } catch (e) {
      setPending({ items: [], loading: false, error: String(e) });
    }
  }

  async function onAccept(id) {
    try {
      await acceptItem(type, id);
      await loadPending();
      println(`✓ Accepté #${id} (${type})`);
    } catch (e) {
      println("✗ accept ERR: " + e.message);
      alert(e.message);
    }
  }
  async function onReject(id) {
    try {
      await rejectItem(type, id);
      await loadPending();
      println(`✓ Rejeté #${id} (${type})`);
    } catch (e) {
      println("✗ reject ERR: " + e.message);
      alert(e.message);
    }
  }

  React.useEffect(() => {
    // recharge la file d’attente quand on change de type si on peut modérer
    if (canModerate) loadPending();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [type, canModerate]);

  return (
    <div
      style={{
        maxWidth: 920,
        margin: "32px auto",
        fontFamily: "system-ui, -apple-system, Segoe UI, Roboto, sans-serif",
        padding: "0 16px",
        display: "grid",
        gap: 20,
      }}
    >
      <h2>Espace membre</h2>

      <section
        style={{
          display: "flex",
          gap: 8,
          alignItems: "center",
          flexWrap: "wrap",
        }}
      >
        <label>
          <strong>Type :</strong>
        </label>
        <select
          value={type}
          onChange={(e) => setType(e.target.value)}
          style={{ padding: 6 }}
        >
          {Object.entries(cfg.types).map(([slug, def]) => (
            <option key={slug} value={slug}>
              {def.label || slug}
            </option>
          ))}
        </select>
        <span style={{ fontSize: 12, color: "#666" }}>
          REST base : <code>{cfg.types[type]?.rest_base || type}</code>
        </span>
      </section>

      {/* Smoke test */}
      <section style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
        <button onClick={onPing}>🔐 Ping REST (users/me)</button>
        <button onClick={onListMine}>📄 Lister mes contenus</button>
        <button onClick={onCreate}>
          ➕ Créer un contenu de test (pending)
        </button>
      </section>

      {/* Modération (admin/éditeur) */}
      {canModerate ? (
        <section style={{ display: "grid", gap: 12 }}>
          <h3>Modération — {cfg.types[type]?.label || type}</h3>
          <div style={{ display: "flex", gap: 8, alignItems: "center" }}>
            <button onClick={loadPending}>
              ⏳ Recharger la file d’attente
            </button>
            {pending.loading ? <span>Chargement…</span> : null}
            {pending.error ? (
              <span style={{ color: "#b00020" }}>{pending.error}</span>
            ) : null}
          </div>

          <ul
            style={{ listStyle: "none", padding: 0, display: "grid", gap: 12 }}
          >
            {pending.items.map((p) => (
              <li
                key={p.id}
                style={{
                  border: "1px solid #ddd",
                  borderRadius: 8,
                  padding: 12,
                  display: "grid",
                  gap: 6,
                }}
              >
                <div
                  style={{
                    display: "flex",
                    justifyContent: "space-between",
                    gap: 8,
                  }}
                >
                  <strong
                    dangerouslySetInnerHTML={{
                      __html: p.title?.rendered || "(Sans titre)",
                    }}
                  />
                  <span style={{ fontSize: 12, color: "#666" }}>
                    par {p._embedded?.author?.[0]?.name || "?"} — id #{p.id}
                  </span>
                </div>
                <div style={{ fontSize: 12, color: "#666" }}>
                  Créé le {new Date(p.date).toLocaleString()}
                </div>
                <div
                  style={{ marginTop: 4 }}
                  dangerouslySetInnerHTML={{
                    __html: p.excerpt?.rendered || "",
                  }}
                />
                <div style={{ display: "flex", gap: 8, marginTop: 8 }}>
                  <button onClick={() => onAccept(p.id)}>✅ Accepter</button>
                  <button onClick={() => onReject(p.id)}>⛔ Rejeter</button>
                </div>
              </li>
            ))}
          </ul>
        </section>
      ) : (
        <p style={{ fontSize: 12, color: "#777" }}>
          (Section modération visible uniquement pour les rôles ayant la
          capacité de publication sur ce type.)
        </p>
      )}

      <section>
        <h3>Console</h3>
        <pre
          style={{
            background: "#f6f8fa",
            padding: 12,
            minHeight: 220,
            whiteSpace: "pre-wrap",
            overflowX: "auto",
            border: "1px solid #eee",
            borderRadius: 8,
          }}
        >
          {log}
        </pre>
      </section>

      <div style={{ fontSize: 12, color: "#777" }}>
        Connecté en tant que <strong>{cfg.currentUser?.name || "?"}</strong> (ID{" "}
        {cfg.currentUser?.id ?? "?"})
      </div>
    </div>
  );
}

const rootEl = document.getElementById("im-app-root");
if (rootEl) {
  createRoot(rootEl).render(<App />);
}
