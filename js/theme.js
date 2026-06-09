/* ========================================================
   theme.js - Gestion du thème (Phase 3)
   - Changement de charte graphique sans recharger
   - Sauvegarde dans un cookie (1 an)
   - Vérification à chaque chargement
   - STYLES INLINE de secours pour garantir l'affichage
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
        if (THEMES.indexOf(theme) === -1) theme = DEFAULT_THEME;

        const link = document.getElementById('theme-css');
        if (link) {
            link.href = 'css/theme-' + theme + '.css';
        }
        document.documentElement.setAttribute('data-theme', theme);

        // Mise à jour visuelle des boutons actifs (inline pour fonctionner sans CSS)
        document.querySelectorAll('.theme-switcher button').forEach(btn => {
            const isActive = btn.dataset.theme === theme;
            btn.classList.toggle('active', isActive);
            // Styles inline de secours pour l'état actif
            btn.style.background = isActive ? '#E68C7C' : 'transparent';
            btn.style.color = isActive ? '#060B19' : '#E8F1F5';
        });

        setCookie(COOKIE_NAME, theme, COOKIE_DAYS);
    }

    // --- INJECTION DU SWITCHER AVEC STYLES INLINE FORCÉS ---
    function injectSwitcher() {
        if (document.querySelector('.theme-switcher')) return;

        const switcher = document.createElement('div');
        switcher.className = 'theme-switcher';
        // Styles inline pour garantir l'affichage même si le CSS ne charge pas
        switcher.style.cssText = [
            'position:fixed',
            'bottom:20px',
            'left:20px',
            'z-index:9999',
            'display:flex',
            'gap:8px',
            'background:rgba(19,30,58,0.85)',
            'backdrop-filter:blur(10px)',
            '-webkit-backdrop-filter:blur(10px)',
            'border:1px solid rgba(230,140,124,0.5)',
            'border-radius:50px',
            'padding:6px',
            'box-shadow:0 10px 30px rgba(0,0,0,0.4)'
        ].join(';');

        const buttons = [
            { theme: 'dark',  icon: '🌙', title: 'Mode sombre (Sahara nuit)' },
            { theme: 'light', icon: '☀️', title: 'Mode clair (Midi au Maroc)' },
            { theme: 'large', icon: '🔍', title: 'Mode accessibilité (gros caractères)' }
        ];

        buttons.forEach(b => {
            const btn = document.createElement('button');
            btn.dataset.theme = b.theme;
            btn.title = b.title;
            btn.setAttribute('aria-label', b.title);
            btn.textContent = b.icon;
            btn.style.cssText = [
                'background:transparent',
                'border:none',
                'color:#E8F1F5',
                'cursor:pointer',
                'width:38px',
                'height:38px',
                'border-radius:50%',
                'font-size:1.1rem',
                'transition:0.3s',
                'display:flex',
                'align-items:center',
                'justify-content:center',
                'padding:0'
            ].join(';');
            btn.addEventListener('mouseenter', () => {
                if (!btn.classList.contains('active')) btn.style.background = 'rgba(230,140,124,0.3)';
            });
            btn.addEventListener('mouseleave', () => {
                if (!btn.classList.contains('active')) btn.style.background = 'transparent';
            });
            btn.addEventListener('click', () => applyTheme(b.theme));
            switcher.appendChild(btn);
        });

        document.body.appendChild(switcher);
    }

    // --- INITIALISATION ---
    function init() {
        let savedTheme = getCookie(COOKIE_NAME);
        if (!savedTheme || THEMES.indexOf(savedTheme) === -1) {
            savedTheme = DEFAULT_THEME;
        }

        injectSwitcher();
        applyTheme(savedTheme);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Exposer pour debug
    window.LesArcadesTheme = {
        apply: applyTheme,
        current: () => getCookie(COOKIE_NAME) || DEFAULT_THEME
    };
})();
