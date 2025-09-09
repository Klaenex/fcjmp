const injected = typeof window !== "undefined" ? window.IMAppConfig : null;

export const cfg = injected || {
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
    offres: {
      label: "Offres",
      rest_base: "offres",
      caps: { can_publish: false, can_edit_others: false },
    },
    activites: {
      label: "Activités",
      rest_base: "activites",
      caps: { can_publish: false, can_edit_others: false },
    },
  },
  siteUrl: "/",
};

export function getRestBaseFor(type) {
  return cfg.types?.[type]?.rest_base || type;
}
export function canModerate(type) {
  const caps = cfg.types?.[type]?.caps || {};
  return !!(caps.can_publish || caps.can_edit_others);
}
