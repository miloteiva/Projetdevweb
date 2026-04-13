<?php
session_start();

// --- LOGIQUE DE GESTION DU PANIER ---

// 1. Supprimer un article spécifique via son index dans la session
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['index'])) {
    $index = $_GET['index'];
    if (isset($_SESSION['panier'][$index])) {
        unset($_SESSION['panier'][$index]);
        // Réindexation pour éviter les trous dans le tableau
        $_SESSION['panier'] = array_values($_SESSION['panier']);
    }
    header("Location: panier.php");
    exit();
}

// 2. Vider l'intégralité du panier
if (isset($_GET['action']) && $_GET['action'] === 'vider') {
    unset($_SESSION['panier']);
    header("Location: panier.php");
    exit();
}

// 3. Calcul du montant total
$total = 0;
if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $total += $item['prix'] * $item['quantite'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon Panier - Les Arcades</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <style>
        /* --- SYSTÈME DE COULEURS ET POLICES --- */
        :root {
            --bg-color: #060B19;
            --text-color: #E8F1F5;
            --gold-color: #E68C7C;
            --font-title: 'Playfair Display', serif;
            --font-body: 'Montserrat', sans-serif;
        }

        body {
            background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed;
            color: var(--text-color);
            font-family: var(--font-body);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* --- HEADER --- */
        header {
            padding: 20px 40px;
            border-bottom: 1px solid rgba(230, 140, 124, 0.3);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(6, 11, 25, 0.5);
        }

        .logo {
            font-family: var(--font-title);
            font-size: 1.8rem;
            color: var(--gold-color);
            letter-spacing: 2px;
        }

        .nav-link {
            color: var(--text-color);
            text-decoration: none;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .nav-link:hover { color: var(--gold-color); }

        /* --- CONTENEUR PANIER --- */
        .panier-container {
            max-width: 800px;
            margin: 60px auto;
            padding: 40px;
            background: rgba(19, 30, 58, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(230, 140, 124, 0.2);
            border-radius: 4px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
        }

        h1 {
            font-family: var(--font-title);
            color: var(--gold-color);
            font-size: 2.5rem;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(230, 140, 124, 0.2);
            padding-bottom: 10px;
        }

        /* --- LIGNES D'ARTICLES --- */
        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .item-info { flex: 1; }

        .item-name {
            font-size: 1.2rem;
            font-weight: 500;
            display: block;
            margin-bottom: 5px;
        }

        /* Détails pour les menus personnalisés */
        .item-details {
            list-style: none;
            padding: 0;
            margin: 5px 0 0 15px;
            font-size: 0.85rem;
            color: #8FA3BF;
            border-left: 1px solid var(--gold-color);
            padding-left: 10px;
        }

        .item-price {
            font-family: var(--font-title);
            color: var(--gold-color);
            font-size: 1.3rem;
            font-weight: bold;
            margin-left: 20px;
            min-width: 80px;
            text-align: right;
        }

        .btn-delete {
            color: #8FA3BF;
            text-decoration: none;
            font-size: 1.5rem;
            margin-left: 20px;
            line-height: 1;
            transition: 0.3s;
        }

        .btn-delete:hover { color: var(--gold-color); transform: scale(1.2); }

        /* --- RÉCAPITULATIF FINAL --- */
        .cart-summary {
            margin-top: 40px;
            text-align: right;
        }

        .total-label {
            font-size: 0.9rem;
            text-transform: uppercase;
            color: #8FA3BF;
            letter-spacing: 1px;
        }

        .total-amount {
            display: block;
            font-family: var(--font-title);
            font-size: 2.5rem;
            color: var(--gold-color);
            margin-top: 5px;
        }

        /* --- BOUTONS D'ACTION --- */
        .cart-actions {
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .btn-checkout {
            background: var(--gold-color);
            color: #060B19;
            border: none;
            padding: 18px;
            font-family: var(--font-body);
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: 0.3s;
            text-align: center;
            text-decoration: none;
        }

        .btn-checkout:hover {
            background: #f2a698;
            box-shadow: 0 0 20px rgba(230, 140, 124, 0.4);
            transform: translateY(-2px);
        }

        .btn-clear {
            text-align: center;
            color: #8FA3BF;
            font-size: 0.8rem;
            text-decoration: underline;
        }

        .btn-clear:hover { color: var(--gold-color); }

        .empty-cart {
            text-align: center;
            padding: 50px 0;
            color: #8FA3BF;
        }
    </style>
</head>
<body>

    <header>
        <div class="logo">LES ARCADES</div>
        <a href="menu.php" class="nav-link">← Retourner à la carte</a>
    </header>

    <main class="panier-container">
        <h1>Mon Panier</h1>

        <?php if (empty($_SESSION['panier'])): ?>
            <div class="empty-cart">
                <p>Votre panier est actuellement vide.</p>
                <br>
                <a href="menu.php" class="nav-link" style="color: var(--gold-color); border-bottom: 1px solid;">Découvrir nos spécialités</a>
            </div>
        <?php else: ?>
            <div class="cart-items">
                <?php foreach ($_SESSION['panier'] as $index => $item): ?>
                    <div class="item-row">
                        <div class="item-info">
                            <span class="item-name"><?= $item['quantite'] ?>x <?= htmlspecialchars($item['nom']) ?></span>
                            
                            <?php if (isset($item['details'])): ?>
                                <ul class="item-details">
                                    <?php foreach ($item['details'] as $service => $choix): ?>
                                        <li><strong><?= htmlspecialchars($service) ?> :</strong> <?= htmlspecialchars($choix) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>

                        <div class="item-price">
                            <?= number_format($item['prix'] * $item['quantite'], 2) ?> €
                        </div>

                        <a href="panier.php?action=supprimer&index=<?= $index ?>" class="btn-delete" title="Supprimer cet article">&times;</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary">
                <span class="total-label">Total à régler</span>
                <span class="total-amount"><?= number_format($total, 2) ?> €</span>
            </div>

            <div class="cart-actions">
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="paiement.php" class="btn-checkout">Procéder au paiement (CYBank)</a>
                <?php else: ?>
                    <p style="text-align: center; margin-bottom: 10px;">Veuillez vous connecter pour finaliser votre commande.</p>
                    <a href="connection.php" class="btn-checkout">Se connecter</a>
                <?php endif; ?>
                
                <a href="panier.php?action=vider" class="btn-clear">Vider mon panier</a>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>
