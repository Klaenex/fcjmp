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

let menuChevron = document.querySelector(".menu-item-has-children a");

if (menuChevron) {
  let span = document.createElement("span");
  span.classList.add("chevron-icon");

  // Création de l'image
  let img = document.createElement("img");
  img.src = `${themeData.themeUrl}/assets/img/chevrons-down.svg`;
  img.alt = "Chevron";
  let img2 = document.createElement("img");
  img2.src = `${themeData.themeUrl}/assets/img/chevron-down.svg`;
  img2.alt = "Chevron double";

  span.appendChild(img);
  span.appendChild(img2);
  menuChevron.appendChild(span);
}
