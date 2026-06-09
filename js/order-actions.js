/* ========================================================
   order-actions.js - Cycle de vie des commandes (Phase 3)
   - Restaurateur : payée -> préparation -> prête -> assignation livreur
   - Livreur : marquer livraison effectuée
======================================================== */

(function() {
    'use strict';

    // --- RESTAURATEUR : Changer statut ---
    async function changeOrderStatus(orderId, newStatus, button) {
        button.disabled = true;
        const orig = button.textContent;
        button.textContent = '...';

        const result = await apiCall('api/change_order_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, status: newStatus })
        });

        if (result.success) {
            notify('Statut mis à jour : ' + newStatus, 'success');
            // Recharger la section (simple) pour refléter le changement
            setTimeout(() => window.location.reload(), 800);
        } else {
            notify(result.message || 'Erreur', 'error');
            button.textContent = orig;
            button.disabled = false;
        }
    }

    // --- RESTAURATEUR : Assigner un livreur ---
    async function assignLivreur(orderId, livreurId, button) {
        button.disabled = true;
        const result = await apiCall('api/change_order_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, status: 'En livraison', livreur_id: livreurId })
        });

        if (result.success) {
            notify('Livreur assigné', 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            notify(result.message || 'Erreur', 'error');
            button.disabled = false;
        }
    }

    // --- LIVREUR : Marquer livré ---
    async function markDelivered(orderId, button) {
        if (!confirm('Confirmer la livraison de cette commande ?')) return;

        button.disabled = true;
        button.textContent = 'En cours...';

        const result = await apiCall('api/mark_delivered.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId })
        });

        if (result.success) {
            notify('Livraison validée ! Merci.', 'success');
            const card = button.closest('.delivery-card');
            if (card) {
                card.style.transition = 'opacity 0.5s';
                card.style.opacity = '0.3';
            }
            setTimeout(() => window.location.reload(), 1200);
        } else {
            notify(result.message || 'Erreur', 'error');
            button.disabled = false;
            button.textContent = 'Valider la livraison';
        }
    }

    // --- LIVREUR : Abandonner livraison ---
    async function abandonDelivery(orderId, button) {
        if (!confirm('Abandonner cette livraison (adresse introuvable ou autre raison) ?')) return;
        button.disabled = true;

        const result = await apiCall('api/change_order_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, status: 'Abandonnée' })
        });

        if (result.success) {
            notify('Livraison abandonnée', 'success');
            setTimeout(() => window.location.reload(), 800);
        } else {
            notify(result.message || 'Erreur', 'error');
            button.disabled = false;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {

        // Boutons restaurateur : changement de statut
        document.querySelectorAll('.btn-status-change').forEach(btn => {
            btn.addEventListener('click', () => {
                const orderId = parseInt(btn.dataset.orderId, 10);
                const status = btn.dataset.status;
                changeOrderStatus(orderId, status, btn);
            });
        });

        // Restaurateur : assignation livreur
        document.querySelectorAll('.btn-assign-livreur').forEach(btn => {
            btn.addEventListener('click', () => {
                const orderId = parseInt(btn.dataset.orderId, 10);
                const select = document.querySelector('select[data-order-id="' + orderId + '"]');
                const livreurId = select ? parseInt(select.value, 10) : null;
                if (!livreurId) { notify('Sélectionnez un livreur', 'error'); return; }
                assignLivreur(orderId, livreurId, btn);
            });
        });

        // Livreur : terminer livraison
        document.querySelectorAll('.btn-deliver').forEach(btn => {
            btn.addEventListener('click', () => {
                markDelivered(parseInt(btn.dataset.orderId, 10), btn);
            });
        });

        // Livreur : abandonner
        document.querySelectorAll('.btn-abandon').forEach(btn => {
            btn.addEventListener('click', () => {
                abandonDelivery(parseInt(btn.dataset.orderId, 10), btn);
            });
        });
    });
})();
