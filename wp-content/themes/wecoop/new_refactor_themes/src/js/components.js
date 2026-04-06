const navItems = [
  { href: "#home", label: "Home" },
  { href: "#modello", label: "Modello WECOOP" },
  { href: "#servizi", label: "Servizi" },
  { href: "#impatto", label: "Impatto" },
  { href: "#contatti", label: "Contatti" }
];

const services = [
  {
    icon: "CT",
    title: "Centro Territoriale",
    text: "Accoglienza, orientamento e presa in carico con percorsi personalizzati."
  },
  {
    icon: "FD",
    title: "Formazione Digitale",
    text: "Laboratori pratici per competenze professionali e alfabetizzazione digitale."
  },
  {
    icon: "RL",
    title: "Rete Lavoro",
    text: "Collegamento diretto con imprese e cooperative per opportunita concrete."
  },
  {
    icon: "AP",
    title: "App WECOOP",
    text: "Prenotazioni, notifiche e monitoraggio degli obiettivi in un solo spazio."
  },
  {
    icon: "ME",
    title: "Mediazione",
    text: "Supporto linguistico, culturale e amministrativo per i nuovi cittadini."
  },
  {
    icon: "PA",
    title: "Partnership",
    text: "Sinergie con comuni, scuole e terzo settore per impatto duraturo."
  }
];

const impacts = [
  { value: "400+", label: "Persone accompagnate" },
  { value: "120", label: "Inserimenti lavorativi" },
  { value: "35", label: "Partner territoriali attivi" },
  { value: "87%", label: "Utenti che completano il percorso" }
];

export function Header() {
  const links = navItems
    .map((item) => `<li><a href="${item.href}">${item.label}</a></li>`)
    .join("");

  return `
    <header class="site-header">
      <div class="container site-header__inner">
        <a class="brand" href="#home" aria-label="WECOOP Home">
          <span class="brand__dot" aria-hidden="true"></span>
          WECOOP
        </a>
        <button class="mobile-toggle" type="button" data-mobile-toggle aria-label="Apri menu">☰</button>
        <ul class="nav-list" data-mobile-menu>
          ${links}
        </ul>
        <div class="header-actions">
          <a class="button button--primary header-cta" href="#contatti">Collabora con noi</a>
        </div>
      </div>
    </header>
  `;
}

export function Hero() {
  return `
    <section id="home" class="hero">
      <div class="container hero__layout">
        <div class="reveal">
          <span class="eyebrow">Ecosistema WECOOP</span>
          <h1>Inclusione sociale e <span>opportunita reali</span> in un unico modello.</h1>
          <p>
            WECOOP integra servizi territoriali, formazione e strumenti digitali per
            connettere persone vulnerabili, imprese e comunita.
          </p>
          <div>
            <a class="button button--primary" href="#modello">Scopri il modello</a>
            <a class="button button--ghost" href="#contatti">Parla con il team</a>
          </div>
          <div class="hero-kpi">
            <strong>+48%</strong> di autonomia lavorativa nei percorsi completati nel 2025.
          </div>
        </div>
        <aside class="hero-card reveal" aria-label="Visione WECOOP">
          <h2>Fisico + Digitale</h2>
          <p>
            Un hub territoriale supportato da una piattaforma semplice, accessibile e
            multilingua.
          </p>
          <div class="band">
            <strong>PASSAPAROLA</strong>
            <p>Persone, connessioni e opportunita per un territorio piu coeso.</p>
          </div>
        </aside>
      </div>
    </section>
  `;
}

export function ModelSection() {
  return `
    <section id="modello" class="section">
      <div class="container">
        <span class="eyebrow">Il modello</span>
        <h2 class="section-title">Una struttura collaborativa orientata ai risultati</h2>
        <p class="section-subtitle">
          Il sistema WECOOP si basa su prossimita, accompagnamento e tecnologia utile.
          Ogni percorso e costruito intorno alla persona.
        </p>
        <div class="grid grid-3" style="margin-top: 1.4rem;">
          <article class="card reveal">
            <span class="card__icon">1</span>
            <h3>Accesso guidato</h3>
            <p>Sportello unico con presa in carico rapida e orientamento personalizzato.</p>
          </article>
          <article class="card reveal">
            <span class="card__icon" style="background: var(--wecoop-secondary);">2</span>
            <h3>Competenze</h3>
            <p>Formazione pratica su lavoro, digitale e strumenti di cittadinanza attiva.</p>
          </article>
          <article class="card reveal">
            <span class="card__icon" style="background: var(--wecoop-accent);">3</span>
            <h3>Opportunita</h3>
            <p>Matching con aziende e rete sociale per inserimento stabile e monitorato.</p>
          </article>
        </div>
      </div>
    </section>
  `;
}

export function ServicesSection() {
  const cards = services
    .map(
      (service) => `
      <article class="card reveal">
        <span class="card__icon">${service.icon}</span>
        <h4>${service.title}</h4>
        <p>${service.text}</p>
      </article>
    `
    )
    .join("");

  return `
    <section id="servizi" class="section">
      <div class="container">
        <span class="eyebrow">Componenti operativi</span>
        <h2 class="section-title">Servizi modulari per il territorio WECOOP</h2>
        <div class="grid grid-3" style="margin-top: 1.5rem;">
          ${cards}
        </div>
      </div>
    </section>
  `;
}

export function ImpactSection() {
  const items = impacts
    .map(
      (item) => `
      <article class="card reveal">
        <h3 style="font-size: 2rem; color: var(--wecoop-primary); margin: 0;">${item.value}</h3>
        <p>${item.label}</p>
      </article>
    `
    )
    .join("");

  return `
    <section id="impatto" class="section">
      <div class="container">
        <span class="eyebrow">Impatto sociale</span>
        <h2 class="section-title">Numeri che raccontano trasformazione</h2>
        <p class="section-subtitle">
          I dati mostrano una crescita costante di occupabilita, fiducia e partecipazione.
        </p>
        <div class="grid grid-2" style="margin-top: 1.4rem;">
          ${items}
        </div>
      </div>
    </section>
  `;
}

export function ContactSection() {
  return `
    <section id="contatti" class="section">
      <div class="container">
        <span class="eyebrow">Contatti</span>
        <h2 class="section-title">Costruiamo insieme la prossima fase di WECOOP</h2>
        <div class="grid grid-2" style="margin-top: 1.4rem; align-items: start;">
          <article class="card reveal">
            <h3>Entra nella rete</h3>
            <p>
              Scrivici per partnership, attivazione servizi o coprogettazione territoriale.
            </p>
            <p><strong>Email:</strong> contatti@wecoop.it</p>
            <p><strong>Telefono:</strong> +39 000 123 4567</p>
            <p><strong>Sede:</strong> Torino, Italia</p>
          </article>
          <form class="contact reveal" aria-label="Modulo contatti">
            <div class="form-grid">
              <label>
                Nome e cognome
                <input type="text" name="name" placeholder="Mario Rossi" required />
              </label>
              <label>
                Email
                <input type="email" name="email" placeholder="nome@email.it" required />
              </label>
              <label>
                Messaggio
                <textarea name="message" placeholder="Raccontaci la tua proposta"></textarea>
              </label>
              <button class="button button--primary" type="submit">Invia richiesta</button>
            </div>
          </form>
        </div>
      </div>
    </section>
  `;
}

export function Footer() {
  return `
    <footer class="site-footer">
      <div class="container">
        WECOOP Institutional Website - Homepage HTML/CSS/JavaScript · 2026
      </div>
    </footer>
  `;
}
