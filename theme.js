/* ========================================================
   theme.js - Gestion du thème (Phase 3)
   - Changement de charte graphique sans recharger la page
   - Sauvegarde dans un cookie
   - Vérification à chaque chargement
======================================================== */

(function() {
    'use strict';

    const THEMES = ['dark', 'light', 'large'];
    const DEFAULT_THEME = 'dark';
    const COOKIE_NAME = 'arcades_theme';
    const COOKIE_DAYS = 365;

    // --- COOKIES ---
    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/;SameSite=Lax';
    }

    function getCookie(name) {
        const parts = document.cookie.split(';');
        for (let i = 0; i < parts.length; i++) {
            const c = parts[i].trim();
            if (c.indexOf(name + '=') === 0) return c.substring(name.length + 1);
        }
        return null;
    }

    // --- APPLICATION DU THÈME ---
    function applyTheme(theme) {
        // Vérification de cohérence (PDF: si valeur incohérente => défaut)
        if (THEMES.indexOf(theme) === -1) theme = DEFAULT_THEME;

        const link = document.getElementById('theme-css');
        if (link) {
            link.href = 'css/theme-' + theme + '.css';
        }
        document.documentElement.setAttribute('data-theme', theme);

        // Mise à jour des boutons actifs
        document.querySelectorAll('.theme-switcher button').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.theme === theme);
        });

        setCookie(COOKIE_NAME, theme, COOKIE_DAYS);
    }

    // --- INITIALISATION ---
    function init() {
        let savedTheme = getCookie(COOKIE_NAME);
        if (!savedTheme || THEMES.indexOf(savedTheme) === -1) {
            savedTheme = DEFAULT_THEME;
        }

        // Injection du bouton de changement de thème si pas déjà présent
        if (!document.querySelector('.theme-switcher')) {
            const switcher = document.createElement('div');
            switcher.className = 'theme-switcher';
            switcher.innerHTML = `
                <button data-theme="dark"  title="Mode sombre (Sahara nuit)">🌙</button>
                <button data-theme="light" title="Mode clair (Sahara jour)">☀️</button>
                <button data-theme="large" title="Mode accessibilité (gros caractères)">🔍</button>
            `;
            document.body.appendChild(switcher);

            switcher.querySelectorAll('button').forEach(btn => {
                btn.addEventListener('click', () => applyTheme(btn.dataset.theme));
            });
        }

        applyTheme(savedTheme);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Exposer pour debug éventuel
    window.LesArcadesTheme = { apply: applyTheme, current: () => getCookie(COOKIE_NAME) || DEFAULT_THEME };
})();
