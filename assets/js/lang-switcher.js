(function() {
  const defaultLang = localStorage.getItem('site-language') || 'fr';

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

    localStorage.setItem('site-language', lang);
  }

  document.addEventListener('DOMContentLoaded', () => {
    setLanguage(defaultLang);

    document.querySelectorAll('[data-lang-switch]').forEach((btn) => {
      btn.addEventListener('click', function(event) {
        event.preventDefault();
        setLanguage(this.dataset.langSwitch);
      });
    });
  });
})();
