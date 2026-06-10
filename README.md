# 🍽️ Les Arcades

**Rapport de projet — Restaurant Oriental**

| | |
|---:|:---|
| **Équipe** | Milo & Adam |
| **Filière** | préING2 — Groupe MEF2-4 |
| **Établissement** | CY Tech — 2025/2026 |
| **Module** | Informatique 4 |

---

## 🤝 Notre méthode

Projet réalisé en binôme. Chacun a pris en charge environ **50%** du code : Milo plutôt sur HTML/CSS, JavaScript visuel, Adam plutôt sur le back-end PHP, JSON, API. Mais **chaque livraison de phase a été relue, testée et validée par les deux**. Nous assumons collectivement le résultat final et cela nous a permis de connaître encore mieux tous les détails de notre site.

Communication par Discord, Snapchat + réunions hebdomadaires à CY Tech. Dépôt GitHub partagé avec commits réguliers.

---

## 🚀 Lancer le site

Télécharger les fichiers de GitHub sur votre PC :

```bash
git clone https://github.com/miloteiva/Projetdevweb.git
cd Projetdevweb
php -S localhost:8080
```

Puis ouvrir dans le navigateur : **http://localhost:8080/restaurant.php**

---

## 🔑 Comptes de test

Tous les comptes utilisent le mot de passe **`1234`** :

| Rôle | Email | Mot de passe |
|:---|:---|:---:|
| Administrateur | `admin1@arcades.fr` | `1234` |
| Administrateur | `admin2@arcades.fr` | `1234` |
| Restaurateur | `resto@arcades.fr` | `1234` |
| Livreur | `livreur@arcades.fr` | `1234` |
| Client | `client1@test.com` | `1234` |
| Client | `client2@test.com` | `1234` |
| Client | `client3@test.com` | `1234` |
| Client | `client4@test.com` | `1234` |
| Client | `client5@test.com` | `1234` |

---

## 📐 Phase 1 — Maquettes HTML / CSS

**Objectif :** Définir le thème (Les Arcades, restaurant marocain), la charte graphique, et produire toutes les pages statiques.

### Répartition

| Tâche | Milo | Adam | Commun |
|:---|:---:|:---:|:---:|
| Choix du thème + charte graphique | | | ✓ |
| Pages accueil / carte / livraison / notation | ✓ | | |
| Pages inscription / connexion / profil / admin | | ✓ | |
| Page commandes restaurateur | | ✓ | |
| CSS commun + responsive mobile | ✓ | | |
| Relecture croisée | | | ✓ |

### Difficultés
- Premiers brouillons graphiquement incohérents (polices, espacements).
- Page livreur.

### Solutions
- Création d'un fichier de **variables CSS communes** (couleurs, polices).
- Session design pour harmoniser tous les écrans.

---

## ⚙️ Phase 2 — Côté serveur PHP

**Objectif :** Rendre le site dynamique. Inscription, connexion, paiement CYBank, actions des 4 rôles. Stockage en **JSON** (un fichier `data.json`).

### Répartition

| Tâche | Milo | Adam | Commun |
|:---|:---:|:---:|:---:|
| Schéma JSON + données de test (9 users, 15 plats) | | | ✓ |
| Inscription + connexion + sessions PHP | | ✓ | |
| Paiement CYBank + algorithme de Luhn | | ✓ | |
| Pages dynamiques accueil / menu / panier | ✓ | | |
| Kanban restaurateur + livraison (Maps/Waze) | ✓ | | |
| Tests croisés sur chaque rôle | | | ✓ |

### Difficultés
- Sessions PHP : un client pouvait accéder à `admin.php` sans être bloqué.
- Commande différée (immédiate / plus tard) non prévue dans la 1ʳᵉ version.

### Solutions
- En-tête de protection commun en haut de chaque page (vérif rôle + redirection).
- Ajout dans le paiement d'un choix « Maintenant / Plus tard » avec date+heure.

---

## 💻 Phase 3 — JavaScript & AJAX

**Objectif :** Interface dynamique en JavaScript pur. Changement de thème, validation côté client, requêtes asynchrones, modification de commande payée.

### Répartition

| Tâche | Milo | Adam | Commun |
|:---|:---:|:---:|:---:|
| 3 thèmes (sombre/clair/accessibilité) + cookie | ✓ | | |
| Bannière cookies RGPD (sessionStorage) | ✓ | | |
| Validation formulaires + œil + compteur | | ✓ | |
| Profil client modifiable en AJAX | | ✓ | |
| Filtres/tris produits + menu hamburger | ✓ | | |
| Modifier commande payée + complément paiement | | ✓ | |
| Cycle restaurateur + blocage admin AJAX | | ✓ | |
| Notation client (étoiles + commentaire) | ✓ | | |

### Difficultés
- Bannière cookies : cookie classique trop persistant, ne réapparaissait jamais.
- User bloqué pouvait continuer à naviguer jusqu'à déconnexion manuelle.

---

## 🔒 Phase 4 — Sécurité & bonnes pratiques

**Objectif :** Sécuriser le site, organiser le code, ajouter les fonctionnalités bonus du cahier des charges.

### Répartition

| Tâche | Milo | Adam | Commun |
|:---|:---:|:---:|:---:|
| Bibliothèque commune `includes/functions.php` | | ✓ | |
| Sécurité : Luhn serveur, XSS, mots de passe forts | | ✓ | |
| Logs incidents (connexion, paiement, blocage, livraison) | | ✓ | |
| CRUD plats + menus restaurateur | ✓ | | |
| Plats populaires + menu aléatoire (page accueil) | ✓ | | |
| Recommander une ancienne commande | | ✓ | |
| Audit final + chasse aux bugs | | | ✓ |

### Difficultés
- Code dupliqué : `addLog()`, `loadData()` répétés dans plusieurs fichiers.
- Espace livreur intestable car aucune commande « En livraison » dans le JSON par défaut.

### Solutions
- Création de **`includes/functions.php`** avec addLog, loadData, saveData.
- Ajout de commandes de démo dans `data.json` (Payée, Préparation, Prête, Livraison, différée).

---

## 🎯 Conclusion

### Ce qui a bien marché
- **Le binôme.** Communication directe, décisions rapides.
- **La revue croisée systématique** — chacun teste le code de l'autre avant livraison.
- **Le thème graphique fort** (Les Arcades) qui rend chaque page cohérente.
- **Git régulier** : aucune perte de travail, possibilité de revenir en arrière.

### Ce qu'on a appris
- Bien estimer le temps de chaque tâche (les premières phases nous ont mis sous pression).
- La **sécurité ne s'ajoute pas à la fin** — il faut la penser dès la phase 2.
- Le JS sans framework demande de la **rigueur** (fichiers nommés par fonctionnalité).

### Répartition globale de l'investissement
**Milo : 50% — Adam : 50%.** Répartition équilibrée tout au long du semestre. Spécialisations naturelles (Milo front, Adam back) mais résultat final assumé collectivement.

---

## 📁 Structure du projet
arcades_final/
├── *.php           → Pages principales (vues)
├── api/            → Endpoints AJAX
├── includes/       → Bibliothèque PHP commune (functions.php)
├── js/             → Scripts JavaScript
├── css/            → 3 thèmes + mobile responsive
└── data.json       → Stockage (users, plats, menus, commandes, logs)

---

## ✨ Fonctionnalité innovante (pour la soutenance)

- **Menu surprise du Chef** : tirage aléatoire avec animation de roulette sur la page d'accueil.
- **Notation libre** : possibilité de laisser un avis général sans avoir de commande (en plus de la notation par commande).
- **Bouton Waze** en plus de Google Maps pour le livreur.

---

*Milo & Adam — préING2 MEF2-4 — CY Tech 2025/2026*
