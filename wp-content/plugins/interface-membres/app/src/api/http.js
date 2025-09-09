import { cfg } from "../config";

// Construit une URL wp/v2: v2('offres'), v2('offres', '?page=2')
export function v2(resource, suffix = "") {
  const base = cfg.restUrl?.replace(/\/+$/, "");
  return `${base}/wp/v2/${resource}${suffix}`;
}

// Namespace REST custom (pour /im/v1/…)
export function custom(path) {
  const base = cfg.restUrl?.replace(/\/+$/, "");
  return `${base}/im/v1/${path}`;
}

// Fetch avec nonce + JSON
export async function wpFetch(url, init = {}) {
  const headers = new Headers(init.headers || {});
  headers.set("Content-Type", "application/json");
  if (cfg?.nonce) headers.set("X-WP-Nonce", cfg.nonce);

  const res = await fetch(url, { ...init, headers, credentials: "include" });
  if (!res.ok) {
    let msg = `HTTP ${res.status}`;
    try {
      const data = await res.json();
      if (data?.message) msg = data.message;
    } catch (_) {}
    throw new Error(msg);
  }
  const contentType = res.headers.get("content-type") || "";
  if (contentType.includes("application/json")) return res.json();
  return res.text();
}
