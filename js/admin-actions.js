/* ========================================================
   admin-actions.js - Actions admin via AJAX (Phase 3)
   - Bloquer / débloquer un utilisateur (asynchrone obligatoire)
======================================================== */

(function() {
    'use strict';

    async function toggleBlockUser(userId, currentlyBlocked, button) {
        const action = currentlyBlocked ? 'débloquer' : 'bloquer';
        if (!confirm('Voulez-vous vraiment ' + action + ' cet utilisateur ?')) return;

        button.disabled = true;
        button.textContent = '...';

        const result = await apiCall('api/block_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, block: !currentlyBlocked })
        });

        if (result.success) {
            const newBlocked = !currentlyBlocked;
            button.dataset.blocked = newBlocked ? '1' : '0';
            button.textContent = newBlocked ? 'Débloquer' : 'Bloquer';
            button.classList.toggle('blocked-state', newBlocked);

            // Mise à jour visuelle de la ligne
            const row = button.closest('tr');
            if (row) {
                row.style.opacity = newBlocked ? '0.5' : '1';
                const statusCell = row.querySelector('.user-status');
                if (statusCell) statusCell.textContent = newBlocked ? '🚫 Bloqué' : '✓ Actif';
            }

            notify('Utilisateur ' + action + ' avec succès', 'success');
        } else {
            notify(result.message || 'Erreur', 'error');
            button.textContent = currentlyBlocked ? 'Débloquer' : 'Bloquer';
        }
        button.disabled = false;
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.btn-block-user').forEach(btn => {
            btn.addEventListener('click', () => {
                const userId = parseInt(btn.dataset.userId, 10);
                const blocked = btn.dataset.blocked === '1';
                toggleBlockUser(userId, blocked, btn);
            });
        });
    });
})();
