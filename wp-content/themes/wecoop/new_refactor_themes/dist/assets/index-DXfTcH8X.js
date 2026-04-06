(function(){const e=document.createElement("link").relList;if(e&&e.supports&&e.supports("modulepreload"))return;for(const i of document.querySelectorAll('link[rel="modulepreload"]'))r(i);new MutationObserver(i=>{for(const o of i)if(o.type==="childList")for(const n of o.addedNodes)n.tagName==="LINK"&&n.rel==="modulepreload"&&r(n)}).observe(document,{childList:!0,subtree:!0});function a(i){const o={};return i.integrity&&(o.integrity=i.integrity),i.referrerPolicy&&(o.referrerPolicy=i.referrerPolicy),i.crossOrigin==="use-credentials"?o.credentials="include":i.crossOrigin==="anonymous"?o.credentials="omit":o.credentials="same-origin",o}function r(i){if(i.ep)return;i.ep=!0;const o=a(i);fetch(i.href,o)}})();const s=[{href:"#home",label:"Home"},{href:"#modello",label:"Modello WECOOP"},{href:"#servizi",label:"Servizi"},{href:"#impatto",label:"Impatto"},{href:"#contatti",label:"Contatti"}],c=[{icon:"CT",title:"Centro Territoriale",text:"Accoglienza, orientamento e presa in carico con percorsi personalizzati."},{icon:"FD",title:"Formazione Digitale",text:"Laboratori pratici per competenze professionali e alfabetizzazione digitale."},{icon:"RL",title:"Rete Lavoro",text:"Collegamento diretto con imprese e cooperative per opportunita concrete."},{icon:"AP",title:"App WECOOP",text:"Prenotazioni, notifiche e monitoraggio degli obiettivi in un solo spazio."},{icon:"ME",title:"Mediazione",text:"Supporto linguistico, culturale e amministrativo per i nuovi cittadini."},{icon:"PA",title:"Partnership",text:"Sinergie con comuni, scuole e terzo settore per impatto duraturo."}],l=[{value:"400+",label:"Persone accompagnate"},{value:"120",label:"Inserimenti lavorativi"},{value:"35",label:"Partner territoriali attivi"},{value:"87%",label:"Utenti che completano il percorso"}];function p(){return`
    <header class="site-header">
      <div class="container site-header__inner">
        <a class="brand" href="#home" aria-label="WECOOP Home">
          <span class="brand__dot" aria-hidden="true"></span>
          WECOOP
        </a>
        <button class="mobile-toggle" type="button" data-mobile-toggle aria-label="Apri menu">☰</button>
        <ul class="nav-list" data-mobile-menu>
          ${s.map(e=>`<li><a href="${e.href}">${e.label}</a></li>`).join("")}
        </ul>
        <div class="header-actions">
          <a class="button button--primary header-cta" href="#contatti">Collabora con noi</a>
        </div>
      </div>
    </header>
  `}function d(){return`
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
  `}function u(){return`
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
  `}function m(){return`
    <section id="servizi" class="section">
      <div class="container">
        <span class="eyebrow">Componenti operativi</span>
        <h2 class="section-title">Servizi modulari per il territorio WECOOP</h2>
        <div class="grid grid-3" style="margin-top: 1.5rem;">
          ${c.map(e=>`
      <article class="card reveal">
        <span class="card__icon">${e.icon}</span>
        <h4>${e.title}</h4>
        <p>${e.text}</p>
      </article>
    `).join("")}
        </div>
      </div>
    </section>
  `}function v(){return`
    <section id="impatto" class="section">
      <div class="container">
        <span class="eyebrow">Impatto sociale</span>
        <h2 class="section-title">Numeri che raccontano trasformazione</h2>
        <p class="section-subtitle">
          I dati mostrano una crescita costante di occupabilita, fiducia e partecipazione.
        </p>
        <div class="grid grid-2" style="margin-top: 1.4rem;">
          ${l.map(e=>`
      <article class="card reveal">
        <h3 style="font-size: 2rem; color: var(--wecoop-primary); margin: 0;">${e.value}</h3>
        <p>${e.label}</p>
      </article>
    `).join("")}
        </div>
      </div>
    </section>
  `}function b(){return`
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
  `}function g(){return`
    <footer class="site-footer">
      <div class="container">
        WECOOP Institutional Website - Homepage HTML/CSS/JavaScript · 2026
      </div>
    </footer>
  `}function f(){const t=document.getElementById("app");t&&(t.innerHTML=[p(),"<main>",d(),u(),m(),v(),b(),"</main>",g()].join(`
`))}function h(){const t=document.querySelector("[data-mobile-toggle]"),e=document.querySelector("[data-mobile-menu]");!t||!e||(t.addEventListener("click",()=>{e.classList.toggle("is-open")}),e.querySelectorAll("a").forEach(a=>{a.addEventListener("click",()=>{e.classList.remove("is-open")})}))}function y(){const t=document.querySelectorAll(".reveal");if(!t.length||!("IntersectionObserver"in window)){t.forEach(a=>a.classList.add("is-visible"));return}const e=new IntersectionObserver(a=>{a.forEach(r=>{r.isIntersecting&&(r.target.classList.add("is-visible"),e.unobserve(r.target))})},{threshold:.2});t.forEach(a=>e.observe(a))}function O(){const t=document.querySelector("form.contact");t&&t.addEventListener("submit",e=>{e.preventDefault();const a=t.querySelector("button[type='submit']");a&&(a.textContent="Messaggio inviato",a.setAttribute("disabled","true"))})}f();h();y();O();
