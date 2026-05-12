# Corrections Phase 3 - Les Arcades

## 📁 Où placer chaque fichier

```
site/
├── configurer_menu.php     ← À LA RACINE (remplace l'ancien)
├── notation.php            ← À LA RACINE (remplace l'ancien)
├── data.json               ← À LA RACINE (remplace l'ancien)
├── js/
│   ├── theme.js            ← DANS js/  (remplace l'ancien)
│   └── common.js           ← DANS js/  (remplace l'ancien)
```

Les autres fichiers ne changent pas.

---

## ✅ Ce qui a été corrigé

### 1. Les 3 menus sont maintenant vraiment différents

| Menu | Prix | Services | Composition |
|------|------|----------|-------------|
| **Sahara** | 45€ | 3 | Entrée + Plat + Dessert |
| **Quintessence** | 75€ | 5 | Amuse-bouche + Entrée + Plat + Trou normand + Dessert |
| **L'Éclipse** | 110€ | 7 | Mise en bouche + Entrée froide + Entrée chaude + Sorbet + Plat (accord mets/vins) + Plateau de douceurs + Mignardises &amp; Thé |

Le formulaire de `configurer_menu.php` génère dynamiquement le bon nombre de services selon le menu choisi. Certains services ont un choix libre (radio), d'autres sont fixés par le Chef (mise en bouche, sorbet, mignardises…).

### 2. Bouton de changement de thème toujours visible

Le `theme.js` a été refait avec des **styles inline forcés** dans le JS : même si la CSS ne charge pas (ex : mauvais chemin), le bouton apparaît quand même en bas à gauche avec ses 3 icônes 🌙 ☀️ 🔍. Plus de dépendance fragile.

### 3. Bannière cookies RGPD toujours visible

Pareil dans `common.js` : la bannière est stylée inline. Si tu ne la vois pas, c'est probablement parce que tu as déjà cliqué "Accepter" ou "Refuser" dans une session précédente. Pour la retester, ouvre la console du navigateur (F12) et tape :

```js
LesArcades.resetCookieConsent()
```

Ça efface le cookie de consentement et recharge la page.

### 4. Livreur reçoit les commandes

J'ai ajouté **2 nouvelles commandes de démo** dans `data.json` pour que la chaîne soit testable du premier coup :

- **Commande #3** : statut `En livraison`, assignée à **livreur Express (id=4)** → le livreur la voit immédiatement après connexion.
- **Commande #4** : statut `Prête` → le restaurateur peut tout de suite l'assigner à un livreur depuis sa page Cuisine.

### 5. Bonus : notation maintenant vraiment sauvegardée

L'ancienne `notation.php` affichait "Merci !" sans rien enregistrer dans `data.json`. La nouvelle version :
- vérifie que la commande appartient bien au client et qu'elle est `Livrée`
- refuse si déjà notée (conforme à la phase 3 : *une seule fois*)
- envoie la note **en AJAX** vers `api/save_rating.php`
- affiche le message de remerciement sans recharger la page

---

## 🔄 Cycle complet d'une commande (à tester)

> 🔑 **Tous les mots de passe : `1234`**

C'est la chaîne **conforme au cahier des charges** :

1. **Client** (`client2@test.com`) ajoute des plats au panier → paie via `paiement.php` → commande créée en statut `Payée`
2. **Restaurateur** (`resto@arcades.fr`) la voit dans la colonne "À démarrer" → clique **Démarrer la préparation** → passe en `En préparation`
3. **Restaurateur** clique **Marquer prête** → passe en `Prête`
4. **Restaurateur** choisit un livreur dans la liste déroulante → **Assigner &amp; livrer** → passe en `En livraison`
5. **Livreur** (`livreur@arcades.fr`) la voit dans ses livraisons → clique **Valider la livraison** → passe en `Livrée` (+5 points fidélité au client)
6. **Client** retourne sur "Mon Compte" → bouton **Noter** sur la commande livrée → envoie sa note

Tu peux aussi tester **directement chaque étape** grâce aux commandes pré-remplies (#3 déjà en livraison pour le livreur, #4 déjà prête à assigner pour le restaurateur).
