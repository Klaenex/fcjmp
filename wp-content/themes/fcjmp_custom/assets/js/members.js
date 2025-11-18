document.addEventListener("DOMContentLoaded", function () {
  console.log("members.js chargé");

  // Données envoyées par wp_localize_script dans functions.php
  const endpoint = fcjmpMembers.endpoint;
  const templateDirectoryUri = fcjmpMembers.templateDirectoryUri;

  // Références aux conteneurs de régions (les DIV .card à l'intérieur des wrappers)
  const regions = {
    1: document.getElementById("memberListBruxelles"),
    2: document.getElementById("memberListHainaut"),
    3: document.getElementById("memberListBrabantWallon"),
    4: document.getElementById("memberListLiege"),
    5: document.getElementById("memberListLuxembourg"),
    6: document.getElementById("memberListNamur"),
  };

  // === FILTRE PAR RÉGION ===
  const regionFilter = document.getElementById("filterRegion");

  if (regionFilter) {
    regionFilter.addEventListener("change", function () {
      const value = this.value;

      Object.entries(regions).forEach(([code, element]) => {
        if (!element) return;

        // On remonte jusqu'au wrapper de la région
        const container = element.closest(".region-wrapper") || element;

        if (value === "all") {
          container.style.display = "block";
        } else {
          container.style.display = value === code ? "block" : "none";
        }
      });
    });
  } else {
    console.warn("⚠️ Aucun élément avec l'id #filterRegion trouvé dans le DOM");
  }

  // === UTILITAIRES ===
  function setLoading(isLoading) {
    const content = document.querySelector(".content");
    if (!content) return;

    let loader = document.getElementById("members-loader");

    if (isLoading) {
      if (!loader) {
        loader = document.createElement("p");
        loader.id = "members-loader";
        loader.textContent = "Chargement des membres...";
        content.prepend(loader);
      }
    } else if (loader) {
      loader.remove();
    }
  }

  function afficherMessageErreur() {
    const content = document.querySelector(".content");
    if (!content) return;

    const msg = document.createElement("p");
    msg.classList.add("error-message");
    msg.textContent =
      "Impossible de charger la liste des membres pour le moment.";
    content.appendChild(msg);
  }

  function resetRegions() {
    Object.values(regions).forEach((list) => {
      if (list) list.innerHTML = "";
    });
  }

  // === AFFICHAGE DES MEMBRES ===
  function displayMembers(members) {
    resetRegions();

    members.forEach((member) => {
      const regionCode = member.region;

      if (!regionCode || !regions[regionCode]) return;

      const list = regions[regionCode];
      const listItem = document.createElement("div");
      listItem.classList.add("card-member");

      const avatarSrc =
        member.avatar ||
        `${templateDirectoryUri}/assets/img/default-avatar.svg`;

      listItem.innerHTML = `
        <span class="card-member-header">
          <img src="${avatarSrc}" alt="Photo de ${member.name}" />
          <h3>${member.name}</h3>
        </span>
        <span class="card-member-body">
          ${
            member.phone
              ? `<p>Téléphone : <a href="tel:${member.phone}">${member.phone}</a></p>`
              : `<p>Téléphone : N/A</p>`
          }
          ${
            member.location
              ? `<a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
                  member.location
                )}"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Ouvrir l'adresse de ${
                      member.name
                    } dans Google Maps">
                    <img src="${templateDirectoryUri}/assets/img/maps.svg" class="icon" alt="Google Maps" />
                 </a>`
              : ""
          }
          ${
            member.email
              ? `<a href="mailto:${member.email}" aria-label="Envoyer un mail à ${member.name}">
                    <img src="${templateDirectoryUri}/assets/img/mail.svg" class="icon" alt="Envoyer un mail" />
                 </a>`
              : ""
          }
          ${
            member.site1
              ? `<a href="${member.site1}"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Page Facebook de ${member.name}">
                    <img src="${templateDirectoryUri}/assets/img/instagram.svg" class="icon" alt="Facebook" />
                 </a>`
              : ""
          }
          ${
            member.site2
              ? `<a href="${member.site2}"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Page Instagram de ${member.name}">
                    <img src="${templateDirectoryUri}/assets/img/facebook.svg" class="icon" alt="Instagram" />
                 </a>`
              : ""
          }
        </span>
      `;

      list.appendChild(listItem);
    });

    // Message "Aucun membre" si une région est vide
    Object.entries(regions).forEach(([code, list]) => {
      if (!list) return;
      if (!list.children.length) {
        const p = document.createElement("p");
        p.textContent = "Aucun membre pour le moment.";
        list.appendChild(p);
      }
    });
  }

  // === RÉCUPÉRATION DES MEMBRES ===
  async function fetchMembers() {
    setLoading(true);
    try {
      const response = await fetch(endpoint);
      const data = await response.json();
      console.log("Membres reçus :", data);
      displayMembers(data);
    } catch (error) {
      console.error("Erreur lors du chargement des membres :", error);
      afficherMessageErreur();
    } finally {
      setLoading(false);
    }
  }

  fetchMembers();
});
