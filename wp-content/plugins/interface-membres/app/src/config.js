export const cfg = {
  apiBase: window?.fcjmpApiBase || "/wp-json",
  nonce: window?.fcjmpNonce || "",
  currentUser: window?.fcjmpCurrentUser || { id: 0 },
  // Ajout d’un mapping de statuts pour éviter cfg.status.undefined
  status: {
    pending: "pending",
    draft: "draft",
    publish: "publish",
    rejected: "rejected",
  },
};

export function getRestBaseFor(type) {
  const map = {
    offres: "offres",
    posts: "posts",
    pages: "pages",
  };
  return map[type] || type;
}
