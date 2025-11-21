const globalCfg = window?.IMAppConfig || {};
export const cfg = {
  apiBase: (globalCfg.restUrl || "/wp-json").replace(/\/$/, ""),
  nonce: globalCfg.nonce || "",
  currentUser: globalCfg.currentUser || { id: 0 },
  status: globalCfg.status || {
    pending: "pending",
    draft: "draft",
    publish: "publish",
    rejected: "rejected",
  },
  restNamespace: globalCfg.restNamespace || "im/v1",
};

export function getRestBaseFor(type) {
  const map = { offres: "offres", posts: "posts", pages: "pages" };
  return map[type] || type;
}
