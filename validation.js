/* ========================================================
   validation.js - Validation côté client (Phase 3)
   - Vérification de chaque champ avant envoi
   - Messages d'erreur sans rechargement
   - Compteur de caractères en temps réel
   - Toggle affichage mot de passe (icône œil)
======================================================== */

(function() {
    'use strict';

    // --- RÈGLES DE VALIDATION ---
    const validators = {
        required: (v) => v.trim().length > 0 ? null : 'Ce champ est obligatoire.',

        email: (v) => {
            if (!v) return 'L\'email est obligatoire.';
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(v) ? null : 'Format d\'email invalide (ex : nom@domaine.com).';
        },

        password: (v) => {
            if (!v) return 'Le mot de passe est obligatoire.';
            if (v.length < 4) return 'Au moins 4 caractères requis.';
            if (v.length > 50) return '50 caractères maximum.';
            return null;
        },

        telephone: (v) => {
            if (!v) return 'Le téléphone est obligatoire.';
            const re = /^(?:(?:\+33|0)[1-9])(?:[\s.-]?\d{2}){4}$/;
            return re.test(v.replace(/\s/g, '')) ? null : 'Numéro français invalide (ex : 06 12 34 56 78).';
        },

        nom: (v) => {
            if (!v) return 'Ce champ est obligatoire.';
            if (v.length < 2) return 'Au moins 2 caractères.';
            if (v.length > 50) return '50 caractères maximum.';
            const re = /^[a-zA-ZàâäéèêëïîôöùûüÿçÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇ\s'-]+$/;
            return re.test(v) ? null : 'Caractères non autorisés (lettres uniquement).';
        },

        adresse: (v) => {
            if (!v) return 'L\'adresse est obligatoire.';
            if (v.length < 5) return 'Adresse trop courte.';
            if (v.length > 150) return '150 caractères maximum.';
            return null;
        },

        text: (v) => v && v.trim() ? null : 'Ce champ est obligatoire.'
    };

    // --- AFFICHER UNE ERREUR ---
    function showError(input, message) {
        let errorEl = input.parentElement.querySelector('.field-error');
        if (!errorEl) {
            errorEl = document.createElement('span');
            errorEl.className = 'field-error';
            input.parentElement.appendChild(errorEl);
        }
        errorEl.textContent = message || '';
        input.classList.toggle('invalid', !!message);
        input.classList.toggle('valid', !message && input.value.length > 0);
    }

    // --- VALIDER UN CHAMP ---
    function validateField(input) {
        const rule = input.dataset.validate;
        if (!rule || !validators[rule]) return true;
        const error = validators[rule](input.value);
        showError(input, error);
        return !error;
    }

    // --- COMPTEUR DE CARACTÈRES ---
    function setupCharCounter(input) {
        const maxLength = parseInt(input.dataset.maxlength, 10);
        if (!maxLength) return;

        const counter = document.createElement('div');
        counter.className = 'char-counter';
        input.parentElement.appendChild(counter);

        function update() {
            const len = input.value.length;
            counter.textContent = len + ' / ' + maxLength + ' caractères';
            counter.classList.toggle('near-limit', len >= maxLength * 0.8 && len < maxLength);
            counter.classList.toggle('over-limit', len >= maxLength);
        }
        input.addEventListener('input', update);
        update();
    }

    // --- TOGGLE MOT DE PASSE ---
    function setupPasswordToggle(input) {
        // Envelopper l'input dans un wrapper
        const parent = input.parentElement;
        if (!parent.classList.contains('password-wrapper')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'password-wrapper';
            input.parentNode.insertBefore(wrapper, input);
            wrapper.appendChild(input);

            const toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'password-toggle';
            toggle.innerHTML = '👁';
            toggle.title = 'Afficher/cacher le mot de passe';

            toggle.addEventListener('click', () => {
                const isPwd = input.type === 'password';
                input.type = isPwd ? 'text' : 'password';
                toggle.innerHTML = isPwd ? '🙈' : '👁';
            });

            wrapper.appendChild(toggle);
        }
    }

    // --- INITIALISATION SUR CHARGEMENT ---
    document.addEventListener('DOMContentLoaded', () => {

        // Tous les inputs marqués data-validate
        document.querySelectorAll('[data-validate]').forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => {
                if (input.classList.contains('invalid')) validateField(input);
            });
        });

        // Compteurs de caractères
        document.querySelectorAll('[data-maxlength]').forEach(setupCharCounter);

        // Toggle mots de passe
        document.querySelectorAll('input[type="password"]').forEach(setupPasswordToggle);

        // Intercepter la soumission des formulaires marqués data-validated-form
        document.querySelectorAll('form[data-validated-form]').forEach(form => {
            form.addEventListener('submit', (e) => {
                let allValid = true;
                form.querySelectorAll('[data-validate]').forEach(input => {
                    if (!validateField(input)) allValid = false;
                });
                if (!allValid) {
                    e.preventDefault();
                    notify('Veuillez corriger les erreurs du formulaire.', 'error');
                }
            });
        });
    });
})();
