/* ========================================================
   common.js - Utilitaires JS partagés
   - Notifications visuelles
   - Vérification périodique du blocage utilisateur
   - Bannière cookies RGPD (avec styles inline de secours)
======================================================== */

(function() {
    'use strict';

    // --- NOTIFICATION TOAST ---
    window.notify = function(message, type) {
        type = type || 'info';
        const div = document.createElement('div');
        div.className = 'js-notif ' + type;
        div.textContent = message;
        // Styles inline de secours (au cas où le CSS n'aurait pas chargé)
        const bgColors = { success: '#6bcf7f', error: '#ff6b6b', info: '#E68C7C' };
        div.style.cssText = [
            'position:fixed',
            'top:20px',
            'right:20px',
            'background:' + (bgColors[type] || '#E68C7C'),
            'color:#060B19',
            'padding:15px 25px',
            'border-radius:4px',
            'z-index:99999',
            'box-shadow:0 10px 30px rgba(0,0,0,0.5)',
            'font-family:Montserrat,sans-serif',
            'font-weight:500',
            'animation:slideIn 0.3s ease-out'
        ].join(';');
        document.body.appendChild(div);
        setTimeout(() => {
            div.style.opacity = '0';
            div.style.transition = 'opacity 0.3s';
            setTimeout(() => div.remove(), 300);
        }, 3000);
    };

    // --- WRAPPER FETCH JSON ---
    window.apiCall = async function(url, options) {
        options = options || {};
        options.headers = options.headers || {};
        options.headers['X-Requested-With'] = 'XMLHttpRequest';
        try {
            const response = await fetch(url, options);
            return await response.json();
        } catch (err) {
            return { success: false, message: 'Erreur réseau' };
        }
    };

    // --- VÉRIFICATION DU BLOCAGE (toutes les 30s) ---
    function checkBlocked() {
        fetch('api/check_blocked.php', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.blocked) {
                    alert('Votre compte a été bloqué par un administrateur. Vous allez être déconnecté.');
                    window.location.href = 'logout.php';
                }
            })
            .catch(() => {/* silencieux */});
    }

    const isAuthPage = /(?:connection|inscription|restaurant|allergenes)\.php/.test(window.location.pathname);
    if (!isAuthPage) {
        setInterval(checkBlocked, 30000);
    }

    // --- BANNIÈRE DE CONSENTEMENT COOKIES (RGPD) ---
    function getConsent() {
        const parts = document.cookie.split(';');
        for (let i = 0; i < parts.length; i++) {
            const c = parts[i].trim();
            if (c.indexOf('arcades_consent=') === 0) return c.substring(16);
        }
        return null;
    }
    function setConsent(value) {
        const exp = new Date();
        exp.setTime(exp.getTime() + 365 * 24 * 60 * 60 * 1000);
        document.cookie = 'arcades_consent=' + value + ';expires=' + exp.toUTCString() + ';path=/;SameSite=Lax';
    }

    function showCookieBanner() {
        if (getConsent()) return; // choix déjà fait

        const banner = document.createElement('div');
        banner.className = 'cookie-banner';
        // Styles inline de secours pour garantir l'affichage
        banner.style.cssText = [
            'position:fixed',
            'bottom:0',
            'left:0',
            'right:0',
            'background:rgba(6,11,25,0.96)',
            'backdrop-filter:blur(15px)',
            '-webkit-backdrop-filter:blur(15px)',
            'border-top:2px solid #E68C7C',
            'padding:18px 25px',
            'z-index:9998',
            'box-shadow:0 -10px 30px rgba(0,0,0,0.5)',
            'font-family:Montserrat,sans-serif',
            'color:#E8F1F5'
        ].join(';');

        banner.innerHTML = `
            <div class="cookie-content" style="max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:30px;flex-wrap:wrap;">
                <p style="margin:0;flex:1;min-width:280px;font-size:0.9rem;color:#E8F1F5;">
                    🍪 <strong style="color:#E68C7C;">Cookies &amp; vie privée</strong><br>
                    Les Arcades utilise des cookies pour mémoriser votre choix de thème, votre panier et votre connexion.
                    Aucune donnée n'est partagée avec des tiers.
                </p>
                <div class="cookie-actions" style="display:flex;gap:12px;">
                    <button class="cookie-accept" style="padding:10px 22px;font-family:inherit;font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;cursor:pointer;border-radius:4px;background:#E68C7C;color:#060B19;border:none;font-weight:bold;">J'accepte</button>
                    <button class="cookie-refuse" style="padding:10px 22px;font-family:inherit;font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;cursor:pointer;border-radius:4px;background:transparent;color:#8FA3BF;border:1px solid #8FA3BF;">Refuser</button>
                </div>
            </div>
        `;
        document.body.appendChild(banner);

        const close = () => {
            banner.style.transition = 'opacity 0.4s, transform 0.4s';
            banner.style.opacity = '0';
            banner.style.transform = 'translateY(20px)';
            setTimeout(() => banner.remove(), 400);
        };

        banner.querySelector('.cookie-accept').addEventListener('click', () => {
            setConsent('accepted');
            close();
        });
        banner.querySelector('.cookie-refuse').addEventListener('click', () => {
            setConsent('refused');
            close();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', showCookieBanner);
    } else {
        showCookieBanner();
    }

    // --- Outil debug : réinitialiser le consentement cookies ---
    // (utile pour tester la bannière à nouveau, depuis la console : LesArcades.resetCookieConsent())
    window.LesArcades = window.LesArcades || {};
    window.LesArcades.resetCookieConsent = function() {
        document.cookie = 'arcades_consent=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
        location.reload();
    };
})();
