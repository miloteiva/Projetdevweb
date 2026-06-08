/* ========================================================
   profile-edit.js - Modification du profil (Phase 3)
   - Bouton "modifier" rend les champs éditables
   - Validation des nouvelles données
   - Envoi en connexion ASYNCHRONE obligatoire
======================================================== */

(function() {
    'use strict';

    let isEditing = false;
    const FIELDS = ['nom', 'prenom', 'email', 'telephone', 'adresse'];

    function getCard(field) {
        return document.querySelector('.editable-field[data-field="' + field + '"]');
    }

    function enterEditMode() {
        isEditing = true;
        FIELDS.forEach(field => {
            const box = getCard(field);
            if (!box) return;
            const current = box.textContent.trim();
            box.dataset.original = current;
            box.innerHTML = '';

            const input = document.createElement('input');
            input.type = field === 'email' ? 'email' : (field === 'telephone' ? 'tel' : 'text');
            input.value = (current === 'Non renseigné' || current === 'Non renseignée') ? '' : current;
            input.dataset.field = field;
            input.dataset.validate = (field === 'email' ? 'email' :
                                     field === 'telephone' ? 'telephone' :
                                     field === 'adresse' ? 'adresse' : 'nom');
            input.style.cssText = 'width:100%;padding:8px;background:transparent;border:none;color:inherit;font-size:inherit;outline:none;';
            box.appendChild(input);
            box.classList.add('editing');
        });

        const btn = document.getElementById('btn-edit-profile');
        if (btn) {
            btn.textContent = 'Enregistrer';
            btn.classList.add('saving');
        }

        let cancelBtn = document.getElementById('btn-cancel-edit');
        if (!cancelBtn && btn) {
            cancelBtn = document.createElement('button');
            cancelBtn.id = 'btn-cancel-edit';
            cancelBtn.className = 'btn-action';
            cancelBtn.style.marginLeft = '10px';
            cancelBtn.textContent = 'Annuler';
            btn.parentElement.appendChild(cancelBtn);
            cancelBtn.addEventListener('click', cancelEdit);
        }
    }

    function cancelEdit() {
        FIELDS.forEach(field => {
            const box = getCard(field);
            if (!box) return;
            box.classList.remove('editing');
            box.textContent = box.dataset.original;
        });
        const btn = document.getElementById('btn-edit-profile');
        if (btn) { btn.textContent = 'Modifier mon profil'; btn.classList.remove('saving'); }
        const cancel = document.getElementById('btn-cancel-edit');
        if (cancel) cancel.remove();
        isEditing = false;
    }

    async function saveProfile() {
        // Récupérer + valider tous les champs
        const data = {};
        let allValid = true;

        FIELDS.forEach(field => {
            const box = getCard(field);
            if (!box) return;
            const input = box.querySelector('input');
            if (input) {
                data[field] = input.value.trim();
                // Validation
                if (input.dataset.validate && window.validateInput) {
                    if (!window.validateInput(input)) allValid = false;
                }
            }
        });

        if (!allValid) { notify('Corrigez les erreurs du formulaire.', 'error'); return; }

        // Envoi asynchrone
        try {
            const result = await apiCall('api/update_profile.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            if (result.success) {
                FIELDS.forEach(field => {
                    const box = getCard(field);
                    if (!box) return;
                    box.classList.remove('editing');
                    box.textContent = data[field] || (field === 'telephone' ? 'Non renseigné' : 'Non renseignée');
                });
                const btn = document.getElementById('btn-edit-profile');
                if (btn) { btn.textContent = 'Modifier mon profil'; btn.classList.remove('saving'); }
                const cancel = document.getElementById('btn-cancel-edit');
                if (cancel) cancel.remove();
                isEditing = false;
                notify('Profil mis à jour avec succès', 'success');
            } else {
                notify(result.message || 'Erreur lors de la mise à jour', 'error');
            }
        } catch (err) {
            notify('Erreur réseau', 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btn-edit-profile');
        if (!btn) return;

        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (isEditing) saveProfile();
            else enterEditMode();
        });
    });
})();
