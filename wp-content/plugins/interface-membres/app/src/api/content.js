import { cfg, getRestBaseFor } from "../config";
import { v2, custom, wpFetch } from "./http";

export async function createItem(type, { title, content, meta, status }) {
  const restBase = getRestBaseFor(type);
  return wpFetch(v2(restBase), {
    method: "POST",
    body: JSON.stringify({ title, content, meta, status }),
  });
}

export async function createOffre(payload) {
  const title = payload?.title ?? payload?.titre ?? "";
  const content = payload?.content ?? payload?.contenu ?? "";
  const meta = payload?.meta ?? {};
  const status = payload?.status ?? "pending";
  return createItem("offres", { title, content, meta, status });
}

export async function listMine(
  type,
  {
    page = 1,
    perPage = 10,
    status = ["pending", "publish", "rejected", "draft"],
    search = "",
    order = "desc",
    orderby = "date",
  } = {}
) {
  const restBase = getRestBaseFor(type);
  const statusParam = Array.isArray(status)
    ? status.join(",")
    : String(status || "");
  const params = new URLSearchParams({
    author: String(cfg.currentUser.id),
    status: statusParam,
    page: String(page),
    per_page: String(perPage),
    order,
    orderby,
    _embed: "1",
  });
  if (search && search.trim()) params.set("search", search.trim());
  return wpFetch(v2(restBase, "?" + params.toString()));
}

export async function listPending(type, { page = 1, perPage = 10 } = {}) {
  const restBase = getRestBaseFor(type);
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

export async function acceptItem(type, id) {
  return wpFetch(
    custom(`moderation/${encodeURIComponent(type)}/${id}/accept`),
    {
      method: "POST",
    }
  );
}
export async function rejectItem(type, id) {
  return wpFetch(
    custom(`moderation/${encodeURIComponent(type)}/${id}/reject`),
    {
      method: "POST",
    }
  );
}

// Wrappers pour "offres"
export async function listOffresPending({ page = 1, perPage = 10 } = {}) {
  return listPending("offres", { page, perPage });
}
export async function accepterOffre(id) {
  return acceptItem("offres", id);
}
export async function rejeterOffre(id) {
  return rejectItem("offres", id);
}
