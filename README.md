# Les Arcades - Restaurant Oriental · Phase 1 → Phase 4

## Lancer le site
```bash
cd arcades_final
php -S localhost:8000
# Ouvrir : http://localhost:8000/restaurant.php
```

## Comptes de test (mot de passe : 1234)
| Rôle        | Email                | Ce qu'on peut tester                         |
|-------------|----------------------|----------------------------------------------|
| Admin       | admin1@arcades.fr    | Bloquer users, voir logs d'incidents          |
| Restaurateur| resto@arcades.fr     | Kanban cuisine + commandes différées + CRUD  |
| Livreur     | livreur@arcades.fr   | Commande #4 en livraison + Maps/Waze         |
| Client      | client1@test.com     | Commande livrée + recommander + noter        |
| Client      | client2@test.com     | Commande Payée à modifier + cmd différée #10 |

## Phase 1 ✅
- Charte graphique (3 thèmes)
- Toutes les pages créées
- CSS commun (theme-dark + mobile.css)

## Phase 2 ✅
- Inscription + connexion fonctionnelles
- 9 users (5 clients, 2 admins, 1 restaurateur, 1 livreur)
- 15 plats, 3 menus
- **Commande immédiate OU différée** (choix au paiement)
- Paiement CYBank avec validation Luhn
- Données dans data.json (arborescence séparée des PHP)
- **Bibliothèque commune** : includes/functions.php (addLog, loadData, saveData, verifierLuhn, requireRole)

## Phase 3 ✅
- 3 thèmes CSS (sombre / clair / accessibilité) avec cookie
- Bannière cookies : sessionStorage, réapparaît à chaque nouvelle session navigateur
- Validation JS tous formulaires + œil mot de passe + compteur caractères
- Profil client modifiable en AJAX (icône crayon ✏️)
- Filtres produits asynchrones + tris locaux
- Modifier commande payée (complément paiement ou ticket réduction)
- Cycle restaurateur Payée→Préparation→Prête→Livraison
- Blocage admin AJAX + session terminée dans les 30s
- Livreur valide livraison (+5 pts fidélité)
- Notation client (mode commande OU mode libre, multiple)

## Phase 4 ✅
- **Sécurité** : Luhn côté serveur, validation stricte, mots de passe robustes, XSS protégé
- **Logs d'incidents complets** : connexions OK/échec/bloqué, paiements OK/échec, blocages user, livraisons
- **CRUD plats et menus** depuis configurer_menu.php
- **Plats populaires** sur la page d'accueil (top 4)
- **Menu aléatoire** : tirage au sort entrée + plat + dessert
- **Recommander une commande** : remet une ancienne commande dans le panier

## Nouveaux mots de passe (inscription)
- 8 caractères minimum
- 1 majuscule + 1 minuscule + 1 chiffre + 1 caractère spécial
- Indicateur de force en temps réel

## Structure
```
arcades_final/
├── *.php           → Pages principales (vues)
├── api/            → Endpoints AJAX
├── includes/       → Bibliothèque PHP commune (functions.php)
├── js/             → Scripts JavaScript
├── css/            → 3 thèmes + mobile responsive
└── data.json       → Stockage (users, plats, menus, commandes, logs, avis, tickets)
```

## Fonctionnalité innovante (pour la soutenance)
- **Menu surprise du Chef** : tirage aléatoire avec animation de roulette sur la page d'accueil
- **Notation libre** : possibilité de laisser un avis général sans avoir de commande (en plus de la notation par commande)
- **Bouton Waze** en plus de Google Maps pour le livreur
