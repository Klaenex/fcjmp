import { cfg } from "../config";

// Nettoie trailing slash
const base = (cfg.apiBase || "/wp-json").replace(/\/$/, "");

export const v2 = (path, query = "") => `${base}/wp/v2/${path}${query || ""}`;
export const custom = (path, query = "") =>
  `${base}/${cfg.restNamespace}/${path}${query || ""}`;

export async function wpFetch(url, options = {}) {
  const res = await fetch(url, {
    method: options.method || "GET",
    credentials: "include",
    headers: {
      "Content-Type": "application/json",
      "X-WP-Nonce": cfg.nonce || "",
      ...(options.headers || {}),
    },
    body: options.body,
  });

  if (!res.ok) {
    let msg = `${res.status} ${res.statusText}`;
    try {
      const data = await res.json();
      if (data && (data.message || data.code)) {
        msg += ` — ${data.message || data.code}`;
      }
    } catch (_) {}
    throw new Error(msg);
  }

  const ct = res.headers.get("content-type") || "";
  return ct.includes("application/json") ? res.json() : res.text();
}
