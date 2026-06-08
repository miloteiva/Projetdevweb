/* ========================================================
   common.js - Utilitaires JS partagés (Phase 3+4)
   FIX BANNIERE COOKIES : utilisation robuste de sessionStorage
   La bannière s'affiche UNE FOIS par session navigateur.
   Quand le navigateur est fermé puis rouvert, elle réapparaît.
======================================================== */
(function() {
    'use strict';

    // --- NOTIFICATIONS ---
    window.notify = function(message, type) {
        type = type || 'info';
        const div = document.createElement('div');
        div.className = 'js-notif ' + type;
        div.textContent = message;
        const bgColors = { success: '#6bcf7f', error: '#ff6b6b', info: '#E68C7C' };
        div.style.cssText = [
            'position:fixed','top:20px','right:20px',
            'background:' + (bgColors[type] || '#E68C7C'),
            'color:#060B19','padding:15px 25px','border-radius:4px',
            'z-index:99999','box-shadow:0 10px 30px rgba(0,0,0,0.5)',
            'font-family:Montserrat,sans-serif','font-weight:500',
            'animation:slideIn 0.3s ease-out','max-width:380px'
        ].join(';');
        document.body.appendChild(div);
        setTimeout(() => { div.style.opacity='0'; div.style.transition='opacity 0.3s'; setTimeout(()=>div.remove(),300); }, 3500);
    };

    // --- CSRF & API ---
    function getCsrfToken() {
        const m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    window.apiCall = async function(url, options) {
        options = options || {};
        options.headers = options.headers || {};
        options.headers['X-Requested-With'] = 'XMLHttpRequest';
        if (options.body && typeof options.body === 'string') {
            try {
                const p = JSON.parse(options.body);
                if (!p.csrf_token) { p.csrf_token = getCsrfToken(); options.body = JSON.stringify(p); }
            } catch(e) {}
        }
        try {
            const r = await fetch(url, options);
            return await r.json();
        } catch(e) { return { success: false, message: 'Erreur réseau' }; }
    };

    // --- CHECK BLOCKED (sauf sur pages publiques) ---
    const isAuthPage = /(?:connection|inscription|restaurant|allergenes)\.php/.test(window.location.pathname);
    if (!isAuthPage) {
        setInterval(function() {
            fetch('api/check_blocked.php', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json()).then(d => {
                    if (d.blocked) { alert('Votre compte a été bloqué.'); window.location.href = 'logout.php'; }
                }).catch(()=>{});
        }, 30000);
    }

    // ============================================================
    // BANNIÈRE COOKIES - VERSION ROBUSTE
    // ============================================================
    // Principe : sessionStorage est vidé à la fermeture du navigateur
    // -> 1er chargement d'une session navigateur = bannière visible
    // -> Tant qu'on n'a pas cliqué sur "J'accepte" ou "Refuser", la
    //    bannière réapparaît à chaque navigation (page suivante)
    // -> Une fois validée, elle disparaît jusqu'à la fermeture du
    //    navigateur
    // ============================================================
    const CONSENT_KEY = 'arcades_consent_v2';
    const BANNER_ID   = 'arcades-cookie-banner';

    function hasConsented() {
        try {
            const v = sessionStorage.getItem(CONSENT_KEY);
            return v === 'accepted' || v === 'refused';
        } catch (e) { return false; }
    }

    function saveConsent(value) {
        try { sessionStorage.setItem(CONSENT_KEY, value); } catch (e) {}
    }

    function removeBanner() {
        const existing = document.getElementById(BANNER_ID);
        if (existing) {
            existing.style.transition = 'opacity 0.4s';
            existing.style.opacity = '0';
            setTimeout(() => existing.remove(), 400);
        }
    }

    function showBanner() {
        // Ne pas afficher si l'utilisateur a déjà choisi (dans la session)
        if (hasConsented()) return;
        // Empêcher les doublons
        if (document.getElementById(BANNER_ID)) return;
        // Vérifier que body existe
        if (!document.body) return;

        const b = document.createElement('div');
        b.id = BANNER_ID;
        b.className = 'cookie-banner';
        b.style.cssText = 'position:fixed;bottom:0;left:0;right:0;background:rgba(6,11,25,0.96);backdrop-filter:blur(15px);-webkit-backdrop-filter:blur(15px);border-top:2px solid #E68C7C;padding:18px 25px;z-index:9998;box-shadow:0 -10px 30px rgba(0,0,0,0.5);font-family:Montserrat,sans-serif;color:#E8F1F5;animation:slideUpBanner 0.4s ease-out;';
        b.innerHTML = '<div class="cookie-content" style="max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:30px;flex-wrap:wrap;">' +
            '<p style="margin:0;flex:1;min-width:280px;font-size:0.9rem;">🍪 <strong style="color:#E68C7C;">Cookies &amp; vie privée</strong><br>Les Arcades utilise des cookies pour mémoriser votre thème, panier et connexion. Aucune donnée partagée avec des tiers.</p>' +
            '<div class="cookie-actions" style="display:flex;gap:12px;">' +
                '<button type="button" id="cb-accept-btn" style="padding:10px 22px;font-family:inherit;font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;cursor:pointer;border-radius:4px;background:#E68C7C;color:#060B19;border:none;font-weight:bold;">J\'accepte</button>' +
                '<button type="button" id="cb-refuse-btn" style="padding:10px 22px;font-family:inherit;font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;cursor:pointer;border-radius:4px;background:transparent;color:#8FA3BF;border:1px solid #8FA3BF;">Refuser</button>' +
            '</div>' +
        '</div>';

        document.body.appendChild(b);

        const acceptBtn = document.getElementById('cb-accept-btn');
        const refuseBtn = document.getElementById('cb-refuse-btn');

        if (acceptBtn) {
            acceptBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                saveConsent('accepted');
                removeBanner();
            });
        }
        if (refuseBtn) {
            refuseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                saveConsent('refused');
                removeBanner();
            });
        }
    }

    // Lancer la bannière dès que possible
    function initBanner() {
        if (document.body) {
            showBanner();
        } else {
            // Si body pas encore chargé, attendre
            document.addEventListener('DOMContentLoaded', showBanner);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBanner);
    } else {
        initBanner();
    }

    // Permettre la réinitialisation pour debug
    window.LesArcades = window.LesArcades || {};
    window.LesArcades.resetCookieConsent = function() {
        try { sessionStorage.removeItem(CONSENT_KEY); } catch (e) {}
        location.reload();
    };
    window.LesArcades.getCookieStatus = function() {
        try { return sessionStorage.getItem(CONSENT_KEY) || 'non choisi'; } catch (e) { return 'erreur'; }
    };
})();
