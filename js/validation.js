/* ========================================================
   validation.js - Validation côté client (Phase 3+4)
   + Indicateur force mot de passe
   + Validation carte bancaire (Luhn + expiry + CVC)
======================================================== */
(function() {
    'use strict';

    const validators = {
        required: v => v.trim().length > 0 ? null : 'Ce champ est obligatoire.',
        email: v => {
            if (!v) return "L'email est obligatoire.";
            return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) ? null : "Format d'email invalide (ex : nom@domaine.com).";
        },
        password: v => {
            if (!v) return 'Le mot de passe est obligatoire.';
            if (v.length < 4) return 'Au moins 4 caractères requis.';
            return null;
        },
        telephone: v => {
            if (!v) return 'Le téléphone est obligatoire.';
            return /^(?:(?:\+33|0)[1-9])(?:[\s.\-]?\d{2}){4}$/.test(v.replace(/\s/g,'')) ? null : 'Numéro français invalide (ex : 06 12 34 56 78).';
        },
        nom: v => {
            if (!v) return 'Ce champ est obligatoire.';
            if (v.length < 2) return 'Au moins 2 caractères.';
            if (v.length > 50) return '50 caractères maximum.';
            return /^[a-zA-ZàâäéèêëïîôöùûüÿçÀÂÄÉÈÊËÏÎÔÖÙÛÜŸÇ\s'\-]+$/.test(v) ? null : 'Lettres uniquement.';
        },
        adresse: v => {
            if (!v) return "L'adresse est obligatoire.";
            if (v.length < 5) return 'Adresse trop courte.';
            if (v.length > 150) return '150 caractères maximum.';
            return null;
        },
        text: v => v && v.trim() ? null : 'Ce champ est obligatoire.',

        // Carte bancaire : Luhn
        card_number: v => {
            const n = v.replace(/\s+/g,'');
            if (!/^\d{13,19}$/.test(n)) return 'Numéro de carte invalide (13 à 19 chiffres).';
            let sum = 0, alt = false;
            for (let i = n.length-1; i >= 0; i--) {
                let d = parseInt(n[i],10);
                if (alt) { d *= 2; if (d > 9) d -= 9; }
                sum += d; alt = !alt;
            }
            return sum % 10 === 0 ? null : 'Numéro de carte invalide.';
        },
        cvc: v => /^\d{3,4}$/.test(v) ? null : 'CVC invalide (3 ou 4 chiffres).',
        exp: v => {
            const m = v.match(/^(\d{2})\/(\d{2})$/);
            if (!m) return 'Format MM/AA attendu.';
            const mm = parseInt(m[1],10), yy = parseInt(m[2],10);
            if (mm < 1 || mm > 12) return 'Mois invalide.';
            const now = new Date(), cy = now.getFullYear()%100, cm = now.getMonth()+1;
            if (yy < cy || (yy === cy && mm < cm)) return 'Carte expirée.';
            return null;
        }
    };

    function showError(input, message) {
        let el = input.parentElement.querySelector('.field-error');
        if (!el) {
            el = document.createElement('span');
            el.className = 'field-error';
            input.parentElement.appendChild(el);
        }
        el.textContent = message || '';
        input.classList.toggle('invalid', !!message);
        input.classList.toggle('valid', !message && input.value.length > 0);
    }

    function validateField(input) {
        const rule = input.dataset.validate;
        if (!rule || !validators[rule]) return true;
        const err = validators[rule](input.value);
        showError(input, err);
        return !err;
    }
    window.validateInput = validateField;

    function setupCharCounter(input) {
        const max = parseInt(input.dataset.maxlength, 10);
        if (!max) return;
        const c = document.createElement('div');
        c.className = 'char-counter';
        input.parentElement.appendChild(c);
        function update() {
            const l = input.value.length;
            c.textContent = l + ' / ' + max + ' caractères';
            c.classList.toggle('near-limit', l >= max*0.8 && l < max);
            c.classList.toggle('over-limit', l >= max);
        }
        input.addEventListener('input', update); update();
    }

    function setupPasswordToggle(input) {
        const parent = input.parentElement;
        if (parent.classList.contains('password-wrapper')) return;
        const w = document.createElement('div');
        w.className = 'password-wrapper';
        input.parentNode.insertBefore(w, input);
        w.appendChild(input);
        const btn = document.createElement('button');
        btn.type = 'button'; btn.className = 'password-toggle';
        btn.innerHTML = '👁'; btn.title = 'Afficher/cacher';
        btn.addEventListener('click', () => {
            const isPwd = input.type === 'password';
            input.type = isPwd ? 'text' : 'password';
            btn.innerHTML = isPwd ? '🙈' : '👁';
        });
        w.appendChild(btn);
    }

    // Indicateur de force du mot de passe
    function setupStrengthMeter(input) {
        // Insérer après le wrapper si présent
        function insertAfter(ref) {
            const bar = document.createElement('div');
            bar.className = 'password-strength';
            bar.innerHTML = '<div class="password-strength-bar"></div>';
            const lbl = document.createElement('div');
            lbl.className = 'password-strength-label';
            const target = input.closest('.password-wrapper') || input;
            target.parentNode.insertBefore(bar, target.nextSibling);
            target.parentNode.insertBefore(lbl, bar.nextSibling);
            return { bar: bar.querySelector('.password-strength-bar'), lbl };
        }
        // Attendre que le toggle soit créé
        setTimeout(() => {
            const { bar, lbl } = insertAfter(input);
            input.addEventListener('input', () => {
                const v = input.value;
                let s = 0;
                if (v.length >= 8) s++;
                if (v.length >= 12) s++;
                if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
                if (/[0-9]/.test(v)) s++;
                if (/[^a-zA-Z0-9]/.test(v)) s++;
                const classes = ['','weak','fair','good','strong','strong'];
                const labels  = ['','Faible','Moyen','Bon','Excellent','Excellent'];
                bar.className = 'password-strength-bar ' + (v ? classes[s] || 'weak' : '');
                lbl.textContent = v ? 'Sécurité : ' + labels[s] : '';
            });
        }, 50);
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-validate]').forEach(input => {
            input.addEventListener('blur', () => validateField(input));
            input.addEventListener('input', () => { if (input.classList.contains('invalid')) validateField(input); });
        });
        document.querySelectorAll('[data-maxlength]').forEach(setupCharCounter);
        document.querySelectorAll('input[type="password"]').forEach(setupPasswordToggle);
        document.querySelectorAll('input[data-strength]').forEach(setupStrengthMeter);

        document.querySelectorAll('form[data-validated-form]').forEach(form => {
            form.addEventListener('submit', e => {
                let ok = true;
                form.querySelectorAll('[data-validate]').forEach(i => { if (!validateField(i)) ok = false; });
                if (!ok) { e.preventDefault(); notify('Veuillez corriger les erreurs.', 'error'); }
            });
        });

        // Formatage carte : espaces tous les 4 chiffres
        document.querySelectorAll('input[data-validate="card_number"]').forEach(inp => {
            inp.addEventListener('input', () => {
                let v = inp.value.replace(/\s+/g,'').replace(/\D/g,'');
                inp.value = v.replace(/(\d{4})(?=\d)/g,'$1 ').slice(0,23);
            });
        });
        // Formatage MM/AA
        document.querySelectorAll('input[data-validate="exp"]').forEach(inp => {
            inp.addEventListener('input', () => {
                let v = inp.value.replace(/\D/g,'').slice(0,4);
                if (v.length > 2) v = v.slice(0,2) + '/' + v.slice(2);
                inp.value = v;
            });
        });
    });
})();
