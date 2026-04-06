import {
  Header,
  Hero,
  ModelSection,
  ServicesSection,
  ImpactSection,
  ContactSection,
  Footer
} from "./components.js";

function renderApp() {
  const app = document.getElementById("app");

  if (!app) {
    return;
  }

  app.innerHTML = [
    Header(),
    "<main>",
    Hero(),
    ModelSection(),
    ServicesSection(),
    ImpactSection(),
    ContactSection(),
    "</main>",
    Footer()
  ].join("\n");
}

function setupMobileMenu() {
  const toggle = document.querySelector("[data-mobile-toggle]");
  const menu = document.querySelector("[data-mobile-menu]");

  if (!toggle || !menu) {
    return;
  }

  toggle.addEventListener("click", () => {
    menu.classList.toggle("is-open");
  });

  menu.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      menu.classList.remove("is-open");
    });
  });
}

function setupRevealAnimation() {
  const revealed = document.querySelectorAll(".reveal");

  if (!revealed.length || !("IntersectionObserver" in window)) {
    revealed.forEach((item) => item.classList.add("is-visible"));
    return;
  }

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }

        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      });
    },
    { threshold: 0.2 }
  );

  revealed.forEach((item) => observer.observe(item));
}

function setupContactForm() {
  const form = document.querySelector("form.contact");

  if (!form) {
    return;
  }

  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const button = form.querySelector("button[type='submit']");

    if (button) {
      button.textContent = "Messaggio inviato";
      button.setAttribute("disabled", "true");
    }
  });
}

renderApp();
setupMobileMenu();
setupRevealAnimation();
setupContactForm();
