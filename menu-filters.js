/* ========================================================
   menu-filters.js - Filtres et tris dynamiques (Phase 3)
   - Tris locaux (sur données déjà chargées)
   - Filtres via requêtes asynchrones
======================================================== */

(function() {
    'use strict';

    let currentSort = 'default';
    let currentFilters = { categorie: 'all', regime: [], gout: [] };

    function getCheckedValues(name) {
        return Array.from(document.querySelectorAll('input[name="' + name + '"]:checked'))
            .map(el => el.value);
    }

    // --- TRI LOCAL ---
    function sortItems() {
        const grid = document.getElementById('plats-grid');
        if (!grid) return;
        const items = Array.from(grid.querySelectorAll('.menu-item'));

        items.sort((a, b) => {
            const priceA = parseFloat(a.dataset.prix);
            const priceB = parseFloat(b.dataset.prix);
            const popA = parseInt(a.dataset.popularite, 10) || 0;
            const popB = parseInt(b.dataset.popularite, 10) || 0;

            if (currentSort === 'prix-asc')  return priceA - priceB;
            if (currentSort === 'prix-desc') return priceB - priceA;
            if (currentSort === 'populaire') return popB - popA;
            return 0;
        });
        items.forEach(item => grid.appendChild(item));
    }

    // --- FILTRES AJAX ---
    async function applyFilters() {
        currentFilters.categorie = document.querySelector('select[name="categorie-filter"]')?.value || 'all';
        currentFilters.regime = getCheckedValues('regime');
        currentFilters.gout = getCheckedValues('gout');

        const params = new URLSearchParams();
        params.set('categorie', currentFilters.categorie);
        currentFilters.regime.forEach(r => params.append('regime[]', r));
        currentFilters.gout.forEach(g => params.append('gout[]', g));

        const grid = document.getElementById('plats-grid');
        if (grid) grid.style.opacity = '0.4';

        try {
            const data = await apiCall('api/filter_plats.php?' + params.toString());
            if (data.success) {
                renderPlats(data.plats);
                sortItems();
            }
        } catch (err) {
            notify('Erreur lors du filtrage', 'error');
        }
        if (grid) grid.style.opacity = '1';
    }

    // --- RENDU DES PLATS ---
    function renderPlats(plats) {
        const grid = document.getElementById('plats-grid');
        if (!grid) return;

        if (!plats.length) {
            grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:var(--text-muted);padding:40px;">Aucun plat ne correspond à vos critères.</p>';
            return;
        }

        grid.innerHTML = plats.map(p => `
            <div class="menu-item" data-prix="${p.prix}" data-popularite="${p.nb_commandes || 0}" data-categorie="${p.categorie}">
                <h3>${escapeHtml(p.nom)} ${p.allergenes ? '<sub>' + escapeHtml(p.allergenes) + '</sub>' : ''}</h3>
                <p style="color:var(--text-muted);font-size:0.9rem;">${escapeHtml(p.desc)}</p>
                <span class="price">${p.prix}€</span>
                <form method="POST" action="menu.php">
                    <input type="hidden" name="id_prod" value="${p.id}">
                    <input type="hidden" name="type_prod" value="plat">
                    <button type="submit" class="btn-add">Ajouter au panier</button>
                </form>
            </div>
        `).join('');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    // --- INIT ---
    document.addEventListener('DOMContentLoaded', () => {
        const sortSelect = document.getElementById('sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                currentSort = e.target.value;
                sortItems();
            });
        }

        document.querySelectorAll('select[name="categorie-filter"], input[name="regime"], input[name="gout"]')
            .forEach(el => el.addEventListener('change', applyFilters));
    });
})();
