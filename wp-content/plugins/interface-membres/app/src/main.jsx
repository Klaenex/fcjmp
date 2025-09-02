import React from "react";
import { createRoot } from "react-dom/client";

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

function v2(path, params = "") {
  const base = cfg.restUrl.replace(/\/$/, "");
  const p = params ? (params.startsWith("?") ? params : "?" + params) : "";
  return `${base}/wp/v2/${path}${p}`;
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

function App() {
  const [type, setType] = React.useState(Object.keys(cfg.types)[0] || "post");
  const [log, setLog] = React.useState("");

  function println(msg) {
    setLog((prev) => (prev ? prev + "\n" : "") + msg);
  }

  async function ping() {
    try {
      println("→ GET /wp/v2/users/me");
      const data = await wpFetch(v2("users/me"));
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

  async function listMine() {
    try {
      const restBase = cfg.types[type]?.rest_base || type;
      const params = new URLSearchParams({
        author: String(cfg.currentUser.id),
        status: ["pending", "publish", "rejected", "draft"].join(","),
        per_page: "5",
        orderby: "date",
        order: "desc",
        _embed: "1",
      }).toString();

      println(`→ GET /wp/v2/${restBase}?${params}`);
      const data = await wpFetch(v2(restBase, "?" + params));
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

  async function createPending() {
    try {
      const restBase = cfg.types[type]?.rest_base || type;
      const payload = {
        title: `[TEST ${type}] ` + new Date().toLocaleString(),
        content:
          "Contenu de test automatique.\n\nCe contenu doit apparaître en 'pending'.",
        status: cfg.status?.pending || "pending",
      };

      println(`→ POST /wp/v2/${restBase} payload=` + JSON.stringify(payload));
      const data = await wpFetch(v2(restBase), {
        method: "POST",
        body: JSON.stringify(payload),
      });
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

  const types = cfg.types;

  return (
    <div
      style={{
        maxWidth: 920,
        margin: "32px auto",
        fontFamily: "system-ui, sans-serif",
        padding: "0 16px",
        display: "grid",
        gap: 16,
      }}
    >
      <h2>Interface Membres — Smoke Test</h2>

      <div
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
          {Object.entries(types).map(([slug, def]) => (
            <option key={slug} value={slug}>
              {def.label || slug}
            </option>
          ))}
        </select>
        <span style={{ fontSize: 12, color: "#666" }}>
          REST base : <code>{types[type]?.rest_base || type}</code>
        </span>
      </div>

      <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
        <button onClick={ping}>🔐 Ping REST (users/me)</button>
        <button onClick={listMine}>📄 Lister mes contenus</button>
        <button onClick={createPending}>
          ➕ Créer un contenu de test (pending)
        </button>
      </div>

      <div>
        <h3>Console</h3>
        <pre
          style={{
            background: "#f6f8fa",
            padding: 12,
            minHeight: 220,
            whiteSpace: "pre-wrap",
            overflowX: "auto",
          }}
        >
          {log}
        </pre>
      </div>

      <div style={{ fontSize: 12, color: "#777" }}>
        Utilisateur courant : <strong>{cfg.currentUser?.name || "?"}</strong>{" "}
        (ID {cfg.currentUser?.id ?? "?"})
      </div>
    </div>
  );
}

const rootEl = document.getElementById("im-app-root");
if (rootEl) {
  createRoot(rootEl).render(<App />);
}
