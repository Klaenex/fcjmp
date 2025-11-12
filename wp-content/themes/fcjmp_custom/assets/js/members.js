// FCJMP – front : consomme /wp-json/fcjmp/v1/members
// - Construction DOM sûre (pas d'innerHTML avec contenu externe)
// - Gestion chargement / erreur
// - Lazy-load images
// - Compatibilité avec la localisation wp_localize_script déjà présente (themeData) et la proposition (FCJMP)

(function () {
  "use strict";

  // Récup infos localisées (compat: FCJMP ou themeData)
  const THEME_URL =
    (window.FCJMP && FCJMP.themeUrl) ||
    (window.themeData && themeData.themeUrl) ||
    "";
  const REST_BASE = (window.FCJMP && FCJMP.restUrl) || "/wp-json/fcjmp/v1/";
  const WP_NONCE = (window.FCJMP && FCJMP.nonce) || null;

  // Sélecteurs d'ancres par région (ids déjà présents dans ton template)
  const regionsEls = {
    1: document.getElementById("memberListBruxelles"),
    2: document.getElementById("memberListHainaut"),
    3: document.getElementById("memberListBrabantWallon"),
    4: document.getElementById("memberListLiege"),
    5: document.getElementById("memberListLuxembourg"),
    6: document.getElementById("memberListNamur"),
  };

  function clearRegions() {
    Object.values(regionsEls).forEach((list) => {
      if (list) list.innerHTML = "";
    });
  }

  function createEl(tag, props = {}, children = []) {
    const el = document.createElement(tag);
    Object.entries(props).forEach(([k, v]) => {
      if (v == null) return;
      if (k === "text") el.textContent = String(v);
      else if (k === "html")
        el.innerHTML = String(v); // éviter si données externes
      else if (k in el) el[k] = v;
      else el.setAttribute(k, v);
    });
    children.forEach((child) => child && el.appendChild(child));
    return el;
  }

  function renderMemberCard(m) {
    const card = createEl("div", { className: "card-member" });

    const span1 = createEl("span");
    if (m.avatar) {
      const img = createEl("img", {
        src: m.avatar,
        alt: m.name || "Membre",
        loading: "lazy",
        decoding: "async",
      });
      span1.appendChild(img);
    }
    span1.appendChild(createEl("h3", { text: m.name || "Membre" }));

    const span2 = createEl("span");

    if (m.phone) {
      span2.appendChild(createEl("p", { text: `Téléphone: ${m.phone}` }));
    }

    if (m.location) {
      const aMap = createEl(
        "a",
        {
          href: `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
            m.location
          )}`,
          target: "_blank",
          rel: "noopener noreferrer",
          title: "Ouvrir dans Google Maps",
        },
        [
          createEl("img", {
            src: `${THEME_URL}/assets/img/maps.svg`,
            alt: "Google Maps",
            loading: "lazy",
            decoding: "async",
          }),
        ]
      );
      span2.appendChild(aMap);
    }

    if (m.email) {
      const aMail = createEl(
        "a",
        { href: `mailto:${m.email}`, title: "Envoyer un e-mail" },
        [
          createEl("img", {
            src: `${THEME_URL}/assets/img/mail.svg`,
            alt: "E-mail",
            loading: "lazy",
            decoding: "async",
          }),
        ]
      );
      span2.appendChild(aMail);
    }

    card.appendChild(span1);
    card.appendChild(span2);
    return card;
  }

  async function fetchMembers() {
    const headers = {};
    if (WP_NONCE) headers["X-WP-Nonce"] = WP_NONCE;

    const res = await fetch(`${REST_BASE}members`, {
      headers,
      credentials: "same-origin",
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    // data = tableau normalisé par le plugin
    return Array.isArray(data) ? data : [];
  }

  function mountStatus(elParent) {
    const p = createEl("p", {
      className: "members-status",
      text: "Chargement...",
    });
    elParent && elParent.prepend(p);
    return p;
  }

  function getContentRoot() {
    return document.querySelector(".content") || document.body;
  }

  document.addEventListener("DOMContentLoaded", async function () {
    const root = getContentRoot();
    const statusEl = mountStatus(root);

    try {
      clearRegions();
      const members = await fetchMembers();

      // Répartition par région si une cible existe
      members.forEach((m) => {
        const target = regionsEls[m.region];
        if (!target) return;
        target.appendChild(renderMemberCard(m));
      });

      statusEl.remove();
    } catch (err) {
      statusEl.textContent =
        "Une erreur est survenue lors du chargement des membres.";
      // Optionnel: logger plus de détails en dev
      // console.error(err);
    }
  });
})();
