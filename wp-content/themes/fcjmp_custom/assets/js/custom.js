let burger = document.querySelector(".nav_burger");
let body = document.querySelector("body");
if (burger) {
  burger.addEventListener("click", function () {
    let menu = document.querySelector(".nav_custom");

    if (menu) {
      menu.classList.toggle("nav_custom--open");
      burger.classList.toggle("nav_burger--open");
      body.classList.toggle("body--noscroll");
    }
  });
}

//////// MONDAY
console.log("yo");
document.addEventListener("DOMContentLoaded", function () {
  console.log("yo!");
  if (typeof apiKey === "undefined") {
    console.error("API Key is not defined.");
    return;
  }

  console.log("API Key in JS:", apiKey); // Vérifiez si la clé API est définie

  const boardId = "3180263669"; // Remplacez par l'ID de votre tableau Monday.com

  async function fetchMembers() {
    const query = `
          query {
              boards(ids: [${boardId}]) {
                  items {
                      name
                      column_values {
                          title
                          text
                      }
                  }
              }
          }
      `;

    const response = await fetch("https://api.monday.com/v2", {
      method: "POST",
      headers: {
        Authorization: apiKey,
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ query }),
    });

    const data = await response.json();
    displayMembers(data.data.boards[0].items);
  }

  function displayMembers(members) {
    const memberList = document.getElementById("memberList");
    if (!memberList) return;

    memberList.innerHTML = "";
    members.forEach((member) => {
      const row = document.createElement("tr");
      row.innerHTML = `
              <td>${member.name}</td>
              <td>${
                member.column_values[2]?.text || ""
              }</td> <!-- Adaptez l'index selon votre configuration -->
              <td>${member.column_values[3]?.text || ""}</td>
              <td>${member.column_values[4]?.text || ""}</td>
              <td>${member.column_values[5]?.text || ""}</td>
          `;
      memberList.appendChild(row);
    });
  }

  fetchMembers();
});
