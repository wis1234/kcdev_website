(function () {
  "use strict";

  const includeElements = document.querySelectorAll('[data-include]');
  if (!includeElements.length) return;

  const isLocalFile = window.location.protocol === 'file:';
  const fallbackHeaderHtml = `
<!-- ANNOUNCEMENT BANNER -->
<div class="announcement-banner">
  <div class="ticker"
    data-lang-fr="🎓 Recrutement ouvert — Enqueteurs & Facilitateurs, Senior Econmiste 2025&nbsp;|&nbsp; Candidatures acceptées jusqu'au 1er Septembre 2026"
    data-lang-en="🎓 Open Recruitment — Enumerators & Facilitators, Senior Economist 2025&nbsp;|&nbsp; Applications accepted until September 1, 2026">
    🎓 Recrutement ouvert — Enqueteurs & Facilitateurs, Senior Econmiste 2025&nbsp;|&nbsp; Candidatures acceptées jusqu'au 1er Septembre 2026</div>
  <a href="careers.html" class="apply-link" data-lang-fr="Postuler" data-lang-en="Apply">Postuler</a>
</div>

<!-- HEADER -->
<header id="header">
  <div class="container">
    <a href="index.html" class="logo">
      <!--<img src="assets/img/logo/LOGO.png" alt="KEMT Center logo" class="logo-mark">-->
      <span class="sitename">KEMT Center</span><span class="dot">.</span>
    </a>
    <nav id="navmenu">
      <ul>
        <li><a href="index.html" class="active" data-lang-fr="Accueil" data-lang-en="Home">Accueil</a></li>
        <li class="dropdown">
          <a href="#" data-lang-fr="Institution" data-lang-en="Institution">Institution</a>
          <ul>
            <li><a href="about.html" data-lang-fr="À propos" data-lang-en="About">À propos</a></li>
            <li><a href="team.html" data-lang-fr="Équipe de recherche" data-lang-en="Research team">Équipe de recherche</a></li>
            <li><a href="partners.html" data-lang-fr="Partenaires" data-lang-en="Partners">Partenaires</a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" data-lang-fr="Programmes" data-lang-en="Programs">Programmes</a>
          <ul>
            <li><a href="programs.html" data-lang-fr="Départements" data-lang-en="Departments">Départements</a></li>
            <li><a href="portfolio.html" data-lang-fr="Projets" data-lang-en="Projects">Projets</a></li>
            <li><a href="publications.html" data-lang-fr="Publications" data-lang-en="Publications">Publications</a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" data-lang-fr="Ressources" data-lang-en="Resources">Ressources</a>
          <ul>
            <li><a href="resources.html" data-lang-fr="Bases de données" data-lang-en="Data portals">Bases de données</a></li>
            <li><a href="docs.html" data-lang-fr="Documentation" data-lang-en="Documentation">Documentation</a></li>
            <li><a href="gallery.html" data-lang-fr="Galerie" data-lang-en="Gallery">Galerie</a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" data-lang-fr="Actualités" data-lang-en="News">Actualités</a>
          <ul>
            <li><a href="events.html" data-lang-fr="Événements" data-lang-en="Events">Événements</a></li>
            <li><a href="careers.html" data-lang-fr="Opportunités" data-lang-en="Opportunities">Opportunités</a></li>
            <li><a href="training.html" data-lang-fr="Formations" data-lang-en="Training">Formations</a></li>
          </ul>
        </li>
        <li><a href="contact.html" data-lang-fr="Contact" data-lang-en="Contact">Contact</a></li>
        <li class="mobile-only-action">
          <a href="member.html" class="btn-researcher-mobile">
            <span data-lang-fr="Espace chercheur" data-lang-en="Researcher portal">Espace chercheur</span>
            <i class="bi bi-person-fill"></i>
          </a>
        </li>
      </ul>
    </nav>
    <div class="header-actions">
      <a href="member.html" class="btn-researcher" data-lang-fr="Espace chercheur"
        data-lang-en="Researcher portal">Espace chercheur</a>
      <div class="lang-switcher" role="navigation" aria-label="Language switcher">
        <div class="lang-select-wrapper">
          <button class="lang-select" type="button" aria-haspopup="listbox" aria-expanded="false"
            aria-label="Switch language">
            <img src="assets/img/flags/fr.svg" alt="Français" class="current-flag">
          </button>
          <ul class="lang-select-menu" role="listbox" aria-label="Language selection">
            <li><button type="button" class="lang-option active d-flex align-items-center" data-lang-switch="fr"
                title="Français"><img src="assets/img/flags/fr.svg" class="flag-icon me-2"
                  alt="Français">Français</button></li>
            <li><button type="button" class="lang-option d-flex align-items-center" data-lang-switch="en"
                title="English"><img src="assets/img/flags/us.svg" class="flag-icon me-2"
                  alt="English">English</button></li>
          </ul>
        </div>
      </div>
    </div>
    <button class="mobile-nav-toggle" id="mobileNavToggle">
      <i class="bi bi-list"></i>
    </button>
  </div>
</header>
`;

  const fallbackFooterHtml = `
<footer id="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <a href="index.html" class="logo">
          <span class="sitename">KEMT Center</span><span class="dot">.</span>
        </a>
        <p data-lang-fr="Centre international de recherche dédié à l'innovation sociale, aux politiques publiques fondées sur des preuves et au développement durable en Afrique."
          data-lang-en="International research center dedicated to social innovation, evidence-based public policies, and sustainable development in Africa.">
          Centre international de recherche dédié à l'innovation sociale, aux politiques publiques fondées sur des
          preuves et au développement durable en Afrique.</p>
        <div class="footer-socials">
          <a href="#" class="footer-social-btn" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
          <a href="#" class="footer-social-btn" aria-label="ResearchGate"><i class="bi bi-journal"></i></a>
          <a href="#" class="footer-social-btn" aria-label="Twitter/X"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="footer-social-btn" aria-label="Email"><i class="bi bi-envelope"></i></a>
        </div>
      </div>

      <div class="footer-col">
        <h4 data-lang-fr="Éthique" data-lang-en="Ethics">Éthique</h4>
        <ul>
          <li><a href="#" data-lang-fr="Protocoles IRB" data-lang-en="IRB Protocols">Protocoles IRB</a></li>
          <li><a href="#" data-lang-fr="Confidentialité des données"
              data-lang-en="Data Confidentiality">Confidentialité des données</a></li>
          <li><a href="#" data-lang-fr="Normes de terrain" data-lang-en="Fieldwork Standards">Normes de terrain</a>
          </li>
          <li><a href="#" data-lang-fr="Code de déontologie" data-lang-en="Code of Conduct">Code de déontologie</a>
          </li>
        </ul>
      </div>

      <div class="footer-col">
        <h4 data-lang-fr="Publications" data-lang-en="Publications">Publications</h4>
        <ul>
          <li><a href="#" data-lang-fr="Documents de travail" data-lang-en="Working Papers">Documents de travail</a>
          </li>
          <li><a href="#" data-lang-fr="Articles scientifiques" data-lang-en="Scientific Articles">Articles
              scientifiques</a></li>
          <li><a href="#" data-lang-fr="Notes de politique" data-lang-en="Policy Briefs">Notes de politique</a></li>
          <li><a href="#" data-lang-fr="Rapports annuels" data-lang-en="Annual Reports">Rapports annuels</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4 data-lang-fr="Actualités" data-lang-en="News">Actualités</h4>
        <p style="font-size:14px;color:rgba(255,255,255,0.4);font-weight:300;margin-bottom:1rem;"
          data-lang-fr="Abonnez-vous pour recevoir nos dernières publications et mises à jour."
          data-lang-en="Subscribe to receive our latest publications and updates.">Abonnez-vous pour
          recevoir nos dernières publications et mises à jour.</p>
        <div class="newsletter-form-footer">
          <input type="email" placeholder="Votre adresse email" data-lang-placeholder-fr="Votre adresse email"
            data-lang-placeholder-en="Your email address">
          <button type="button" data-lang-fr="S'abonner" data-lang-en="Subscribe">S'abonner</button>
        </div>
        <ul class="footer-contact-info" style="list-style:none;padding:0;">
          <li><i class="bi bi-geo-alt"></i> <span data-lang-fr="Abomey-Calavi, TANKPE — Bénin"
              data-lang-en="Abomey-Calavi, TANKPE — Benin">Abomey-Calavi, TANKPE — Bénin</span></li>
          <li><i class="bi bi-envelope"></i> research@kemtcenter.org</li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <span data-lang-fr="© 2025 KEMT Center for Development. Tous droits réservés."
        data-lang-en="© 2025 KEMT Center for Development. All rights reserved.">© 2025 KEMT Center for Development.
        Tous droits réservés.</span>
      <div style="display:flex;gap:1.5rem;flex-wrap:wrap;">
        <a href="privacy-policy.html" data-lang-fr="Politique de confidentialité" data-lang-en="Privacy Policy">Politique de
          confidentialité</a>
        <a href="terms-of-service.html" data-lang-fr="Conditions d'utilisation" data-lang-en="Terms of Service">Conditions
          d'utilisation</a>
        <a href="accessibility.html" data-lang-fr="Accessibilité" data-lang-en="Accessibility">Accessibilité</a>
        <a href="sitemap.html" data-lang-fr="Plan du site" data-lang-en="Sitemap">Plan du site</a>
      </div>
    </div>
  </div>
</footer>
`;

  includeElements.forEach((el) => {
    const src = el.getAttribute('data-include');
    if (!src) return;

    if (isLocalFile && src === 'includes/header.html') {
      el.innerHTML = fallbackHeaderHtml;
      return;
    }

    if (isLocalFile && src === 'includes/footer.html') {
      el.innerHTML = fallbackFooterHtml;
      return;
    }

    try {
      const request = new XMLHttpRequest();
      request.open('GET', src, false);
      request.send();

      if (request.status >= 200 && request.status < 400) {
        el.innerHTML = request.responseText;
      } else {
        console.warn(`Include failed: ${src} (${request.status})`);
        el.remove();
      }
    } catch (error) {
      console.warn(`Include failed: ${src}`, error);
      el.remove();
    }
  });
})();
