(function() {
  const defaultLang = localStorage.getItem('site-language') || 'fr';

  function closeLanguageMenu() {
    document.querySelectorAll('.lang-select-wrapper.open').forEach((wrapper) => {
      wrapper.classList.remove('open');
      const button = wrapper.querySelector('.lang-select');
      if (button) {
        button.setAttribute('aria-expanded', 'false');
      }
    });
  }

  function updateLanguageButton(lang) {
    const flagButton = document.querySelector('.lang-switcher .current-flag');
    if (!flagButton) return;

    flagButton.src = lang === 'en' ? 'assets/img/flags/us.svg' : 'assets/img/flags/fr.svg';
    flagButton.alt = lang === 'en' ? 'English' : 'Français';

    document.querySelectorAll('.lang-switcher .lang-option').forEach((btn) => {
      btn.classList.toggle('active', btn.dataset.langSwitch === lang);
    });
  }

  function setLanguage(lang) {
    document.documentElement.lang = lang;
    document.body.classList.toggle('lang-en', lang === 'en');
    document.body.classList.toggle('lang-fr', lang === 'fr');

    document.querySelectorAll('[data-lang]').forEach((el) => {
      if (el.dataset.lang === lang) {
        el.classList.remove('d-none');
      } else {
        el.classList.add('d-none');
      }
    });

    document.querySelectorAll('[data-lang-fr], [data-lang-en]').forEach((el) => {
      const translation = el.getAttribute(`data-lang-${lang}`);
      if (translation !== null) {
        el.innerHTML = translation;
      }
    });

    document.querySelectorAll('[data-lang-alt-fr], [data-lang-alt-en]').forEach((el) => {
      const altText = el.getAttribute(`data-lang-alt-${lang}`);
      if (altText !== null) {
        el.setAttribute('alt', altText);
      }
    });

    document.querySelectorAll('[data-lang-placeholder-fr], [data-lang-placeholder-en]').forEach((el) => {
      const placeholder = el.getAttribute(`data-lang-placeholder-${lang}`);
      if (placeholder !== null) {
        el.setAttribute('placeholder', placeholder);
      }
    });

    document.querySelectorAll('[data-lang-content-fr], [data-lang-content-en]').forEach((el) => {
      const contentText = el.getAttribute(`data-lang-content-${lang}`);
      if (contentText !== null) {
        el.setAttribute('content', contentText);
      }
    });

    document.querySelectorAll('[data-lang-switch]').forEach((btn) => {
      btn.classList.toggle('active', btn.dataset.langSwitch === lang);
    });

    updateLanguageButton(lang);
    localStorage.setItem('site-language', lang);
  }

  function initLanguageSwitcher() {
    setLanguage(defaultLang);

    document.querySelectorAll('[data-lang-switch]').forEach((btn) => {
      btn.addEventListener('click', function(event) {
        event.preventDefault();
        setLanguage(this.dataset.langSwitch);
        closeLanguageMenu();
      });
    });

    document.querySelectorAll('.lang-select').forEach((button) => {
      button.addEventListener('click', function(event) {
        event.preventDefault();
        const wrapper = button.closest('.lang-select-wrapper');
        const expanded = button.getAttribute('aria-expanded') === 'true';
        closeLanguageMenu();

        if (!expanded && wrapper) {
          wrapper.classList.add('open');
          button.setAttribute('aria-expanded', 'true');
        }
      });
    });

    document.addEventListener('click', (event) => {
      if (!event.target.closest('.lang-select-wrapper')) {
        closeLanguageMenu();
      }
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') {
        closeLanguageMenu();
      }
    });
  }

  // Initialize immediately if DOM is already ready, otherwise wait for DOMContentLoaded
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLanguageSwitcher);
  } else {
    initLanguageSwitcher();
  }
})();
