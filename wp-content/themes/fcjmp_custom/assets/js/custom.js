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
