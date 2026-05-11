/* ========================================================
   common.js - Utilitaires JS partagés
   - Notifications visuelles
   - Vérification périodique du blocage utilisateur
======================================================== */

(function() {
    'use strict';

    // --- NOTIFICATION TOAST ---
    window.notify = function(message, type) {
        type = type || 'info';
        const div = document.createElement('div');
        div.className = 'js-notif ' + type;
        div.textContent = message;
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
    // Conformément à la phase 3 : si l'admin bloque un user, sa session se termine sur-le-champ
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

    // N'active la vérification que pour les pages connectées (pas login/inscription)
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
        banner.innerHTML = `
            <div class="cookie-content">
                <p>🍪 <strong>Cookies & vie privée</strong><br>
                Les Arcades utilise des cookies pour mémoriser votre choix de thème, votre panier et votre connexion.
                Aucune donnée n'est partagée avec des tiers.</p>
                <div class="cookie-actions">
                    <button class="cookie-accept">J'accepte</button>
                    <button class="cookie-refuse">Refuser</button>
                </div>
            </div>
        `;
        document.body.appendChild(banner);

        banner.querySelector('.cookie-accept').addEventListener('click', () => {
            setConsent('accepted');
            banner.style.transition = 'opacity 0.4s';
            banner.style.opacity = '0';
            setTimeout(() => banner.remove(), 400);
        });
        banner.querySelector('.cookie-refuse').addEventListener('click', () => {
            setConsent('refused');
            banner.style.transition = 'opacity 0.4s';
            banner.style.opacity = '0';
            setTimeout(() => banner.remove(), 400);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', showCookieBanner);
    } else {
        showCookieBanner();
    }
})();
