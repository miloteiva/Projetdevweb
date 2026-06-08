/* ========================================================
   random-menu.js - Menu aléatoire (Phase 4)
======================================================== */
(function() {
    'use strict';

    async function rollMenu() {
        const btn = document.getElementById('btn-random-menu');
        const result = document.getElementById('random-result');
        if (!btn || !result) return;
        btn.disabled = true;
        const orig = btn.textContent;
        btn.textContent = '🎲 Tirage en cours...';
        result.innerHTML = '<p style="text-align:center;color:#8FA3BF;margin-top:20px;">Le Chef réfléchit...</p>';

        const data = await apiCall('api/random_menu.php');
        if (!data.success) {
            notify(data.message || 'Erreur', 'error');
            btn.disabled = false; btn.textContent = orig; return;
        }

        let i = 0;
        const alts = data.alternatives || [];
        const interval = setInterval(() => {
            i++;
            const tmp = alts[i % alts.length];
            if (tmp) renderChoice(tmp, true, result);
            if (i >= 8) {
                clearInterval(interval);
                renderChoice(data.choice, false, result);
                btn.disabled = false;
                btn.textContent = '🎲 Retenter ma chance';
            }
        }, 130);
    }

    function renderChoice(c, rolling, result) {
        const csrf = (document.querySelector('meta[name="csrf-token"]') || {content:''}).content;
        result.innerHTML = `
            <div style="background:rgba(230,140,124,0.08);border-left:3px solid #E68C7C;padding:20px 25px;margin-top:20px;display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;opacity:${rolling?0.5:1};transition:opacity 0.2s;">
                <div>
                    <h4 style="margin:0 0 8px;color:#E68C7C;font-family:'Playfair Display',serif;font-size:1.2rem;">Suggestion du Chef · ${c.total.toFixed(2)} €</h4>
                    <p style="margin:0;color:#8FA3BF;font-size:0.9rem;">🥗 ${esc(c.entree.nom)} &nbsp;·&nbsp; 🍽 ${esc(c.plat.nom)} &nbsp;·&nbsp; 🍰 ${esc(c.dessert.nom)}</p>
                </div>
                ${!rolling ? `<form method="POST" action="menu.php" style="margin:0;">
                    <input type="hidden" name="csrf_token" value="${esc(csrf)}">
                    <input type="hidden" name="random_pick" value="1">
                    <input type="hidden" name="id_entree"  value="${c.entree.id}">
                    <input type="hidden" name="id_plat"    value="${c.plat.id}">
                    <input type="hidden" name="id_dessert" value="${c.dessert.id}">
                    <button class="btn-action" style="white-space:nowrap;">Ajouter au panier</button>
                </form>` : ''}
            </div>`;
    }

    function esc(s) { const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('btn-random-menu')?.addEventListener('click', rollMenu);
    });
})();
