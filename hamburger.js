/* ========================================================
   hamburger.js - Menu hamburger mobile
   S'active automatiquement sur toutes les pages
======================================================== */
(function () {
    'use strict';

    function initHamburger() {
        const navbar = document.querySelector('.navbar, nav.navbar');
        const topBar = document.querySelector('.top-bar');
        if (!navbar || !topBar) return;

        // Créer le bouton hamburger
        const btn = document.createElement('button');
        btn.className = 'hamburger';
        btn.setAttribute('aria-label', 'Ouvrir le menu');
        btn.setAttribute('aria-expanded', 'false');
        btn.innerHTML = '<span></span><span></span><span></span>';

        // Créer l'overlay (fond sombre derrière le menu)
        const overlay = document.createElement('div');
        overlay.className = 'nav-overlay';
        document.body.appendChild(overlay);

        // Insérer le bouton dans le header (à droite du logo)
        topBar.appendChild(btn);

        function open() {
            navbar.classList.add('open');
            overlay.classList.add('open');
            btn.classList.add('open');
            btn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        }

        function close() {
            navbar.classList.remove('open');
            overlay.classList.remove('open');
            btn.classList.remove('open');
            btn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        }

        btn.addEventListener('click', () => {
            navbar.classList.contains('open') ? close() : open();
        });
        overlay.addEventListener('click', close);

        // Fermer si on clique sur un lien
        navbar.querySelectorAll('a').forEach(a => {
            a.addEventListener('click', close);
        });

        // Fermer si on redimensionne au-delà de 768px
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) close();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHamburger);
    } else {
        initHamburger();
    }
})();
