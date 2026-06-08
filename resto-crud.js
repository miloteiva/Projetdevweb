/* ========================================================
   resto-crud.js - CRUD plats & menus restaurateur (Phase 4)
======================================================== */
(function() {
    'use strict';

    let currentTarget = null;
    let currentEditId = null;

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s || '';
        return d.innerHTML.replace(/"/g, '&quot;');
    }

    function tagCheckbox(tag, plat) {
        const checked = plat && plat.tags && plat.tags.includes(tag) ? 'checked' : '';
        return `<label style="display:inline-flex;gap:6px;align-items:center;cursor:pointer;background:rgba(230,140,124,0.1);padding:5px 10px;border-radius:12px;font-size:0.82rem;margin:3px;">
            <input type="checkbox" name="tag" value="${tag}" ${checked}> ${tag}
        </label>`;
    }

    function openPlatModal(plat) {
        currentTarget = 'plat';
        currentEditId = plat ? plat.id : null;
        const isEdit = !!plat;
        const html = `<div class="modal-overlay" id="crud-modal">
          <div class="modal-box">
            <h2>${isEdit ? 'Modifier le plat' : 'Nouveau plat'}</h2>
            <div class="input-group"><label>Nom</label><input id="f-nom" type="text" maxlength="80" value="${plat ? esc(plat.nom) : ''}"></div>
            <div class="input-group"><label>Catégorie</label>
              <select id="f-categorie">
                <option value="Préludes"     ${plat&&plat.categorie==='Préludes'?'selected':''}>Préludes (entrées)</option>
                <option value="Cœurs de Fête"${plat&&plat.categorie==='Cœurs de Fête'?'selected':''}>Cœurs de Fête (plats)</option>
                <option value="Douceurs"     ${plat&&plat.categorie==='Douceurs'?'selected':''}>Douceurs (desserts)</option>
              </select>
            </div>
            <div class="input-group"><label>Description</label><textarea id="f-desc" maxlength="250" style="min-height:70px;width:100%;box-sizing:border-box;">${plat ? esc(plat.desc) : ''}</textarea></div>
            <div class="input-group"><label>Ingrédients</label><textarea id="f-ingredients" maxlength="300" style="min-height:60px;width:100%;box-sizing:border-box;">${plat ? esc(plat.ingredients||'') : ''}</textarea></div>
            <div class="input-group"><label>Prix (€)</label><input id="f-prix" type="number" min="1" max="999" step="0.5" value="${plat ? plat.prix : ''}"></div>
            <div class="input-group"><label>Allergènes (ex: 1,3,8)</label><input id="f-allergenes" type="text" maxlength="30" value="${plat ? esc(plat.allergenes||'') : ''}" placeholder="1,7,8"></div>
            <div class="input-group"><label>Tags</label><div style="display:flex;flex-wrap:wrap;gap:4px;">
              ${['vegetarien','vegan','halal','sans-gluten','epice','sucre'].map(t=>tagCheckbox(t,plat)).join('')}
            </div></div>
            <div class="modal-actions">
              <button class="btn-action" id="modal-cancel">Annuler</button>
              <button class="btn-action saving" id="modal-save">${isEdit ? 'Enregistrer' : 'Créer'}</button>
            </div>
          </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', html);
        wireModal();
    }

    function openMenuModal(menu) {
        currentTarget = 'menu';
        currentEditId = menu ? menu.id : null;
        const isEdit = !!menu;
        const html = `<div class="modal-overlay" id="crud-modal">
          <div class="modal-box">
            <h2>${isEdit ? 'Modifier le menu' : 'Nouveau menu'}</h2>
            <div class="input-group"><label>Nom</label><input id="f-nom" type="text" maxlength="60" value="${menu ? esc(menu.nom) : ''}"></div>
            <div class="input-group"><label>Description</label><textarea id="f-desc" maxlength="300" style="min-height:80px;width:100%;box-sizing:border-box;">${menu ? esc(menu.desc) : ''}</textarea></div>
            <div class="input-group"><label>Prix (€)</label><input id="f-prix" type="number" min="5" max="999" step="1" value="${menu ? menu.prix : ''}"></div>
            <div class="input-group"><label>Nombre de services</label>
              <select id="f-nb-services">
                <option value="3" ${menu&&menu.nb_services===3?'selected':''}>3 services</option>
                <option value="5" ${menu&&menu.nb_services===5?'selected':''}>5 services</option>
                <option value="7" ${menu&&menu.nb_services===7?'selected':''}>7 services</option>
              </select>
            </div>
            <div class="modal-actions">
              <button class="btn-action" id="modal-cancel">Annuler</button>
              <button class="btn-action saving" id="modal-save">${isEdit ? 'Enregistrer' : 'Créer'}</button>
            </div>
          </div>
        </div>`;
        document.body.insertAdjacentHTML('beforeend', html);
        wireModal();
    }

    function closeModal() {
        const m = document.getElementById('crud-modal');
        if (m) m.remove();
    }

    function wireModal() {
        document.getElementById('modal-cancel').addEventListener('click', closeModal);
        document.getElementById('crud-modal').addEventListener('click', e => { if (e.target.id==='crud-modal') closeModal(); });
        document.getElementById('modal-save').addEventListener('click', saveCurrent);
    }

    async function saveCurrent() {
        const btn = document.getElementById('modal-save');
        const payload = { id: currentEditId };

        if (currentTarget === 'plat') {
            payload.nom         = document.getElementById('f-nom').value.trim();
            payload.categorie   = document.getElementById('f-categorie').value;
            payload.desc        = document.getElementById('f-desc').value.trim();
            payload.ingredients = document.getElementById('f-ingredients').value.trim();
            payload.prix        = parseFloat(document.getElementById('f-prix').value);
            payload.allergenes  = document.getElementById('f-allergenes').value.trim();
            payload.tags        = Array.from(document.querySelectorAll('#crud-modal input[name="tag"]:checked')).map(i=>i.value);
            if (payload.nom.length < 2) { notify('Nom trop court', 'error'); return; }
            if (isNaN(payload.prix) || payload.prix <= 0) { notify('Prix invalide', 'error'); return; }
        } else {
            payload.nom         = document.getElementById('f-nom').value.trim();
            payload.desc        = document.getElementById('f-desc').value.trim();
            payload.prix        = parseFloat(document.getElementById('f-prix').value);
            payload.nb_services = parseInt(document.getElementById('f-nb-services').value, 10);
            if (payload.nom.length < 2 || isNaN(payload.prix) || payload.prix <= 0) { notify('Données invalides', 'error'); return; }
        }

        btn.disabled = true; btn.textContent = '...';
        const url = currentTarget === 'plat' ? 'api/save_plat.php' : 'api/save_menu.php';
        const result = await apiCall(url, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
        if (result.success) {
            notify(result.message || 'Enregistré', 'success');
            closeModal();
            setTimeout(() => location.reload(), 600);
        } else {
            notify(result.message || 'Erreur', 'error');
            btn.disabled = false;
            btn.textContent = currentEditId ? 'Enregistrer' : 'Créer';
        }
    }

    async function toggleItem(target, id, btn) {
        if (!confirm('Activer/désactiver ?')) return;
        btn.disabled = true;
        const url = target === 'plat' ? 'api/toggle_plat.php' : 'api/toggle_menu.php';
        const result = await apiCall(url, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id}) });
        if (result.success) { notify('Statut mis à jour', 'success'); setTimeout(()=>location.reload(),500); }
        else { notify(result.message||'Erreur', 'error'); btn.disabled=false; }
    }

    async function deletePlat(id) {
        if (!confirm('Supprimer ce plat ? S\'il a déjà été commandé, il sera désactivé.')) return;
        const result = await apiCall('api/delete_plat.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id}) });
        if (result.success) { notify(result.message||'Supprimé', 'success'); setTimeout(()=>location.reload(),500); }
        else notify(result.message||'Erreur', 'error');
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('btn-new-plat')?.addEventListener('click', () => openPlatModal(null));
        document.getElementById('btn-new-menu')?.addEventListener('click', () => openMenuModal(null));
        document.querySelectorAll('.btn-edit-plat').forEach(b => b.addEventListener('click', () => openPlatModal(JSON.parse(b.dataset.plat))));
        document.querySelectorAll('.btn-edit-menu').forEach(b => b.addEventListener('click', () => openMenuModal(JSON.parse(b.dataset.menu))));
        document.querySelectorAll('.btn-toggle-plat').forEach(b => b.addEventListener('click', () => toggleItem('plat', parseInt(b.dataset.id),b)));
        document.querySelectorAll('.btn-toggle-menu').forEach(b => b.addEventListener('click', () => toggleItem('menu', parseInt(b.dataset.id),b)));
        document.querySelectorAll('.btn-delete-plat').forEach(b => b.addEventListener('click', () => deletePlat(parseInt(b.dataset.id))));
    });
})();
