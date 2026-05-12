# Les Arcades - Phase 3 (CYTech préING2 2025-2026)

Site web du restaurant marocain "Les Arcades", thème **Un Thé au Sahara**.
Réécriture complète intégrant **toutes les fonctionnalités de la phase 3**.

---

## Structure du projet

```
site/
├── *.php                  → Pages principales (vues)
├── css/
│   ├── theme-dark.css     → Thème sombre Sahara (par défaut)
│   ├── theme-light.css    → Thème clair sable/terracotta
│   └── theme-large.css    → Thème accessibilité (gros caractères)
├── js/
│   ├── theme.js           → Gestion thème via cookies (Phase 3)
│   ├── common.js          → Notifications + vérif blocage périodique
│   ├── validation.js      → Validation client + œil mot de passe + compteur
│   ├── menu-filters.js    → Filtres AJAX + tris locaux
│   ├── profile-edit.js    → Édition profil en AJAX
│   ├── admin-actions.js   → Bloquer/débloquer user en AJAX
│   ├── order-actions.js   → Changement statut commandes
│   └── order-modify.js    → Modifier commande payée
├── api/                   → Endpoints AJAX (PHP)
│   ├── block_user.php
│   ├── change_order_status.php
│   ├── check_blocked.php
│   ├── filter_plats.php
│   ├── mark_delivered.php
│   ├── save_rating.php
│   ├── update_order.php
│   └── update_profile.php
└── data.json              → Données (users, plats, menus, commandes)
```

## Comment tester

1. Démarrer un serveur PHP : `php -S localhost:8000` dans le dossier `site/`
2. Ouvrir `http://localhost:8000/restaurant.php`

### Comptes pré-configurés (mot de passe : `1234` pour tous)

| Rôle | Email | Test |
|------|-------|------|
| Admin | admin1@arcades.fr | Bloquer/débloquer users |
| Admin | admin2@arcades.fr | Stats globales |
| Restaurateur | resto@arcades.fr | Cuisine en direct |
| Livreur | livreur@arcades.fr | Voir livraisons assignées |
| Client | client1@test.com | Commande livrée à noter |
| Client | client2@test.com | Commande Payée modifiable |

## Checklist Phase 3 (cahier des charges)

- ✅ **Changement charte graphique** : 3 thèmes (sombre / clair / accessibilité), bouton en bas à gauche, sauvegarde cookie 1 an, vérif au chargement
- ✅ **Validation client tous formulaires** : email, password, téléphone FR, nom, adresse - sans rechargement
- ✅ **Toggle œil mot de passe** : icône 👁/🙈 sur tous les champs password
- ✅ **Compteur de caractères temps réel** : tous les champs marqués `data-maxlength`
- ✅ **Édition profil AJAX** : bouton "Modifier mon profil" sur `moncompte.php`
- ✅ **Filtres AJAX produits** : catégorie, régime (vegan/halal/sans-gluten...), goût (épicé/sucré)
- ✅ **Tris locaux** : prix croissant/décroissant, les plus commandés
- ✅ **Modifier commande payée** : `modifier_commande.php` - recalcul auto, paiement complément si plus cher, ticket réduction si moins cher
- ✅ **Cycle restaurateur** : Payée → En préparation → Prête → Assignation livreur (tout en AJAX)
- ✅ **Blocage user en AJAX** : admin peut bloquer/débloquer instantanément
- ✅ **Session terminée sur-le-champ** si user bloqué : vérification toutes les 30s via `api/check_blocked.php`
- ✅ **Livreur valide livraison** : bouton AJAX, +5 points fidélité au client
- ✅ **Notation une seule fois** : refusée si déjà notée, étoiles produit + livraison + commentaire

## Scénarios de démonstration

### Scénario 1 : cycle complet d'une commande
1. Se connecter en `client2@test.com` → voir la commande #2 en statut **Payée**
2. Cliquer sur **Modifier** → ajouter/enlever des plats, sauvegarder
3. Se déconnecter, se connecter en `resto@arcades.fr` → faire passer en **En préparation**
4. Marquer **Prête**, assigner à livreur Express
5. Se connecter en `livreur@arcades.fr` → **Valider la livraison**
6. Se reconnecter en `client2@test.com` → **Noter** la commande (une seule fois)

### Scénario 2 : blocage admin
1. Se connecter en `admin1@arcades.fr` dans Chrome
2. Se connecter en `client3@test.com` dans Firefox
3. Dans Chrome → bloquer client3
4. Dans Firefox → dans les 30 secondes, le client3 est éjecté automatiquement

### Scénario 3 : thème
1. Cliquer sur les icônes en bas à gauche (🌙 ☀️ 🔍)
2. Le thème change sans rechargement
3. Fermer le navigateur, rouvrir → le choix est conservé (cookie 1 an)

---

**Auteur** : Étudiant préING2 CYTech 2025-2026
