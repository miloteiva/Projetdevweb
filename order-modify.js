/* ========================================================
   order-modify.js - Modifier une commande payée (Phase 3)
   - Ajouter / enlever des produits sur une commande payée
     mais pas encore en préparation
   - Mise à jour automatique du total
   - Paiement additionnel si plus cher
   - Ticket de réduction si moins cher (au choix)
======================================================== */

(function() {
    'use strict';

    let originalTotal = 0;
    let currentTotal = 0;
    let orderId = null;

    function recalcTotal() {
        let total = 0;
        document.querySelectorAll('.modif-line').forEach(line => {
            const prix = parseFloat(line.dataset.prix);
            const qte = parseInt(line.querySelector('.qte-input').value, 10) || 0;
            total += prix * qte;
            line.querySelector('.line-total').textContent = (prix * qte).toFixed(2) + ' €';
        });
        currentTotal = total;
        document.getElementById('current-total').textContent = currentTotal.toFixed(2) + ' €';

        const diff = currentTotal - originalTotal;
        const diffEl = document.getElementById('diff-display');
        if (diffEl) {
            if (diff > 0) {
                diffEl.innerHTML = '<strong style="color:#ffa500">Supplément à payer : +' + diff.toFixed(2) + ' €</strong>';
            } else if (diff < 0) {
                diffEl.innerHTML = '<strong style="color:#6bcf7f">Crédit (ticket de réduction) : ' + Math.abs(diff).toFixed(2) + ' €</strong>';
            } else {
                diffEl.innerHTML = '<span style="color:var(--text-muted)">Aucun changement</span>';
            }
        }
    }

    async function saveModifications() {
        const articles = [];
        document.querySelectorAll('.modif-line').forEach(line => {
            const qte = parseInt(line.querySelector('.qte-input').value, 10) || 0;
            if (qte > 0) {
                articles.push({
                    nom: line.dataset.nom,
                    prix: parseFloat(line.dataset.prix),
                    quantite: qte,
                    type: 'plat'
                });
            }
        });

        if (articles.length === 0) {
            notify('La commande ne peut pas être vide', 'error');
            return;
        }

        const result = await apiCall('api/update_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, articles: articles, total: currentTotal })
        });

        if (result.success) {
            const diff = currentTotal - originalTotal;
            if (diff > 0) {
                notify('Redirection vers paiement complémentaire (' + diff.toFixed(2) + ' €)...', 'success');
                setTimeout(() => window.location.href = 'paiement.php?complement=' + orderId, 1500);
            } else {
                notify('Commande modifiée avec succès', 'success');
                setTimeout(() => window.location.href = 'moncompte.php', 1200);
            }
        } else {
            notify(result.message || 'Erreur', 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const container = document.getElementById('modif-container');
        if (!container) return;

        orderId = parseInt(container.dataset.orderId, 10);
        originalTotal = parseFloat(container.dataset.originalTotal);

        // Boutons +/- et input direct
        document.querySelectorAll('.qte-plus').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.parentElement.querySelector('.qte-input');
                input.value = (parseInt(input.value, 10) || 0) + 1;
                recalcTotal();
            });
        });
        document.querySelectorAll('.qte-moins').forEach(btn => {
            btn.addEventListener('click', () => {
                const input = btn.parentElement.querySelector('.qte-input');
                const v = parseInt(input.value, 10) || 0;
                if (v > 0) input.value = v - 1;
                recalcTotal();
            });
        });
        document.querySelectorAll('.qte-input').forEach(inp => inp.addEventListener('input', recalcTotal));

        const saveBtn = document.getElementById('btn-save-modifs');
        if (saveBtn) saveBtn.addEventListener('click', saveModifications);

        recalcTotal();
    });
})();
