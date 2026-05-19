(function () {
  "use strict";

  const includeElements = document.querySelectorAll('[data-include]');
  if (!includeElements.length) return;

  const isLocalFile = window.location.protocol === 'file:';
  const fallbackHeaderHtml = `
<header id="header" class="header d-flex align-items-center sticky-top">
  <div class="container d-flex align-items-center justify-content-between">
    <a href="index.html" class="logo d-flex align-items-center me-auto">
      <h1 class="sitename m-0">KEMT Center</h1><span class="ms-2">.</span>
    </a>

    <nav id="navmenu" class="navmenu">
      <ul>
        <li><a href="index.html" class="active"><span data-lang="fr">Accueil</span><span data-lang="en" class="d-none">Home</span></a></li>
        <li class="dropdown">
          <a href="#"><span data-lang="fr">Institution</span><span data-lang="en" class="d-none">Institution</span></a>
          <ul>
            <li><a href="about.html"><span data-lang="fr">À propos</span><span data-lang="en" class="d-none">About</span></a></li>
            <li><a href="team.html"><span data-lang="fr">Équipe de recherche</span><span data-lang="en" class="d-none">Research team</span></a></li>
            <li><a href="partners.html"><span data-lang="fr">Partenaires</span><span data-lang="en" class="d-none">Partners</span></a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#"><span data-lang="fr">Programmes</span><span data-lang="en" class="d-none">Programs</span></a>
          <ul>
            <li><a href="programs.html"><span data-lang="fr">Départements</span><span data-lang="en" class="d-none">Departments</span></a></li>
            <li><a href="portfolio.html"><span data-lang="fr">Projets</span><span data-lang="en" class="d-none">Projects</span></a></li>
            <li><a href="publications.html"><span data-lang="fr">Publications</span><span data-lang="en" class="d-none">Publications</span></a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#"><span data-lang="fr">Ressources</span><span data-lang="en" class="d-none">Resources</span></a>
          <ul>
            <li><a href="resources.html"><span data-lang="fr">Bases de données</span><span data-lang="en" class="d-none">Databases</span></a></li>
            <li><a href="docs.html"><span data-lang="fr">Documentation</span><span data-lang="en" class="d-none">Documentation</span></a></li>
            <li><a href="gallery.html"><span data-lang="fr">Galerie</span><span data-lang="en" class="d-none">Gallery</span></a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#"><span data-lang="fr">Engagement</span><span data-lang="en" class="d-none">Engagement</span></a>
          <ul>
            <li><a href="events.html"><span data-lang="fr">Événements</span><span data-lang="en" class="d-none">Events</span></a></li>
            <li><a href="careers.html"><span data-lang="fr">Opportunités</span><span data-lang="en" class="d-none">Careers</span></a></li>
            <li><a href="training.html"><span data-lang="fr">Formations</span><span data-lang="en" class="d-none">Training</span></a></li>
          </ul>
        </li>
        <li><a href="contact.html"><span data-lang="fr">Contact</span><span data-lang="en" class="d-none">Contact</span></a></li>
      </ul>
      <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
    </nav>

      <div class="header-actions d-flex align-items-center">
      <a href="member.html" class="btn btn-outline-primary btn-sm me-2"><span data-lang="fr">Espace chercheur</span><span data-lang="en" class="d-none">Researcher area</span></a>
      <div class="lang-switcher ms-3 d-none d-lg-inline-block">
        <div class="dropdown">
          <button class="lang-select dropdown-toggle" type="button" id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="assets/img/flags/fr.svg" alt="Français" class="current-flag">
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="langDropdown">
            <li><a class="dropdown-item d-flex align-items-center lang-option active" href="#" data-lang-switch="fr" title="Français"><img src="assets/img/flags/fr.svg" class="flag-icon me-2" alt="Français">Français</a></li>
            <li><a class="dropdown-item d-flex align-items-center lang-option" href="#" data-lang-switch="en" title="English"><img src="assets/img/flags/us.svg" class="flag-icon me-2" alt="English">English</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</header>
`;

  const fallbackFooterHtml = `
<footer id="footer" class="footer dark-background">
  <div class="container footer-top py-5">
    <div class="row gy-4">
      <div class="col-lg-4 col-md-6 footer-about">
        <a href="index.html" class="logo d-flex align-items-center mb-3">
          <span class="sitename">KEMT Center</span>
        </a>
        <p><span data-lang="fr">KEMT Center est un centre de recherche dédié à l’analyse des politiques publiques et au développement durable en Afrique.</span><span data-lang="en" class="d-none">KEMT Center is a research hub focused on public policy analysis and sustainable development in Africa.</span></p>
        <div class="footer-contact pt-3">
          <p><strong><span data-lang="fr">Adresse</span><span data-lang="en" class="d-none">Address</span>:</strong> Abomey-Calavi, Bénin</p>
          <p><strong>Email:</strong> <a href="mailto:research@kemt.com">research@kemt.com</a></p>
          <p><strong><span data-lang="fr">Téléphone</span><span data-lang="en" class="d-none">Phone</span>:</strong> +229 90 00 00 00</p>
        </div>
      </div>

      <div class="col-lg-2 col-md-3 footer-links">
        <h4><span data-lang="fr">Liens rapides</span><span data-lang="en" class="d-none">Quick links</span></h4>
        <ul>
          <li><i class="bi bi-chevron-right"></i> <a href="about.html"><span data-lang="fr">À propos</span><span data-lang="en" class="d-none">About</span></a></li>
          <li><i class="bi bi-chevron-right"></i> <a href="programs.html"><span data-lang="fr">Programmes</span><span data-lang="en" class="d-none">Programs</span></a></li>
          <li><i class="bi bi-chevron-right"></i> <a href="publications.html"><span data-lang="fr">Publications</span><span data-lang="en" class="d-none">Publications</span></a></li>
          <li><i class="bi bi-chevron-right"></i> <a href="contact.html"><span data-lang="fr">Contact</span><span data-lang="en" class="d-none">Contact</span></a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-3 footer-links">
        <h4><span data-lang="fr">Ressources</span><span data-lang="en" class="d-none">Resources</span></h4>
        <ul>
          <li><i class="bi bi-chevron-right"></i> <a href="docs.html"><span data-lang="fr">Documentation</span><span data-lang="en" class="d-none">Documentation</span></a></li>
          <li><i class="bi bi-chevron-right"></i> <a href="gallery.html"><span data-lang="fr">Galerie</span><span data-lang="en" class="d-none">Gallery</span></a></li>
          <li><i class="bi bi-chevron-right"></i> <a href="events.html"><span data-lang="fr">Événements</span><span data-lang="en" class="d-none">Events</span></a></li>
        </ul>
      </div>

      <div class="col-lg-3 col-md-12 footer-newsletter">
        <h4><span data-lang="fr">Restons en contact</span><span data-lang="en" class="d-none">Stay connected</span></h4>
        <p><span data-lang="fr">Recevez les dernières actualités et publications de KEMT Center.</span><span data-lang="en" class="d-none">Receive the latest news and publications from KEMT Center.</span></p>
        <form action="forms/newsletter.php" method="post" class="php-email-form">
          <input type="email" name="email" placeholder="Email">
          <button type="submit" class="btn btn-primary btn-sm mt-2"><span data-lang="fr">S’inscrire</span><span data-lang="en" class="d-none">Subscribe</span></button>
        </form>
        <div class="social-links d-flex gap-2 mt-4">
          <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a>
          <a href="#" class="twitter"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="youtube"><i class="bi bi-envelope"></i></a>
        </div>
      </div>
    </div>
  </div>

  <div class="container copyright text-center py-3 border-top border-secondary">
    <p class="mb-0"><strong>KEMT Center</strong> &copy; <span id="footer-year"></span>. <span data-lang="fr">Tous droits réservés.</span><span data-lang="en" class="d-none">All rights reserved.</span></p>
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
