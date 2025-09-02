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
  if (!res.ok) {
    const text = await res.text().catch(() => "");
    throw new Error(`HTTP ${res.status} ${res.statusText} — ${text}`);
  }
  return res;
}

export async function createItem(type, { title, content, status }) {
  const restBase = cfg.types[type]?.rest_base || type;
  const res = await wpFetch(v2(restBase), {
    method: "POST",
    body: JSON.stringify({ title, content, status }),
  });
  return res.json();
}

export async function getMyItems(
  type,
  {
    page = 1,
    perPage = 10,
    statuses = ["pending", "publish", "rejected", "draft"],
  } = {}
) {
  const restBase = cfg.types[type]?.rest_base || type;
  const params = new URLSearchParams({
    author: String(cfg.currentUser.id),
    status: statuses.join(","),
    page: String(page),
    per_page: String(perPage),
    orderby: "date",
    order: "desc",
    _embed: "1",
  }).toString();
  const res = await wpFetch(v2(restBase, "?" + params));
  const total = Number(res.headers.get("X-WP-Total") || 0);
  const totalPages = Number(res.headers.get("X-WP-TotalPages") || 0);
  const data = await res.json();
  return { data, total, totalPages };
}

export async function getPendingItems(type, { page = 1, perPage = 10 } = {}) {
  const restBase = cfg.types[type]?.rest_base || type;
  const params = new URLSearchParams({
    status: "pending",
    page: String(page),
    per_page: String(perPage),
    orderby: "date",
    order: "desc",
    _embed: "1",
  }).toString();
  const res = await wpFetch(v2(restBase, "?" + params));
  const total = Number(res.headers.get("X-WP-Total") || 0);
  const totalPages = Number(res.headers.get("X-WP-TotalPages") || 0);
  const data = await res.json();
  return { data, total, totalPages };
}

export async function acceptItem(type, id) {
  const res = await wpFetch(
    custom(`moderation/${encodeURIComponent(type)}/${id}/accept`),
    { method: "POST" }
  );
  return res.json();
}

export async function rejectItem(type, id) {
  const res = await wpFetch(
    custom(`moderation/${encodeURIComponent(type)}/${id}/reject`),
    { method: "POST" }
  );
  return res.json();
}

export function getConfig() {
  return cfg;
}
export function getTypes() {
  return cfg.types || {};
}
