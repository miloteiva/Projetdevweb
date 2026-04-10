<?php
session_start();

// --- LOGIQUE DE SUPPRESSION ---

// 1. Supprimer un article spécifique
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['index'])) {
    $index = $_GET['index'];
    if (isset($_SESSION['panier'][$index])) {
        unset($_SESSION['panier'][$index]);
    }
    // Redirection pour nettoyer l'URL
    header("Location: panier.php");
    exit();
}

// 2. Vider tout le panier
if (isset($_GET['action']) && $_GET['action'] === 'vider') {
    unset($_SESSION['panier']);
    header("Location: panier.php");
    exit();
}

// Calcul du total
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; }
        
        header { padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); letter-spacing: 2px; }
        
        .panier-container { max-width: 700px; margin: 60px auto; padding: 40px; background: rgba(19, 30, 58, 0.6); border: 1px solid rgba(230, 140, 124, 0.2); backdrop-filter: blur(10px); }
        h1 { font-family: var(--font-title); color: var(--gold-color); font-size: 2.5rem; margin-bottom: 30px; }
        
        .item-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 20px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.05); position: relative; }
        .item-info { flex-grow: 1; }
        .item-name { font-weight: 500; font-size: 1.1rem; display: block; }
        .item-details { font-size: 0.8rem; color: #8FA3BF; list-style: none; padding-left: 10px; border-left: 1px solid var(--gold-color); margin: 5px 0 0 5px; }
        
        .item-price { font-family: var(--font-title); color: var(--gold-color); font-weight: bold; margin-left: 20px; }
        
        /* Bouton Supprimer individuel */
        .btn-remove { color: #8FA3BF; text-decoration: none; font-size: 1.2rem; margin-left: 20px; transition: 0.3s; padding: 0 5px; }
        .btn-remove:hover { color: var(--gold-color); transform: scale(1.2); }

        .total-section { margin-top: 40px; text-align: right; }
        .total-label { font-size: 0.9rem; text-transform: uppercase; color: #8FA3BF; letter-spacing: 1px; }
        .total-price { display: block; font-family: var(--font-title); font-size: 2.2rem; color: var(--gold-color); margin-top: 5px; }

        .actions-footer { margin-top: 40px; display: flex; flex-direction: column; gap: 15px; }
        .btn-pay { background: var(--gold-color); color: #060B19; border: none; padding: 18px; width: 100%; cursor: pointer; text-transform: uppercase; font-weight: bold; font-family: var(--font-body); letter-spacing: 1px; transition: 0.3s; text-align: center; text-decoration: none; }
        .btn-pay:hover { background: #F2A698; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(230, 140, 124, 0.3); }
        
        .btn-empty-cart { color: #8FA3BF; font-size: 0.8rem; text-decoration: underline; text-align: center; display: block; }
        .btn-empty-cart:hover { color: var(--gold-color); }

        .empty-msg { text-align: center; padding: 40px 0; color: #8FA3BF; }
        .back-link { color: var(--gold-color); text-decoration: none; font-weight: 500; }
    </style>
</head>
<body>
    <header>
        <div class="logo">LES ARCADES</div>
        <nav>
            <a href="menu.php" style="color:var(--text-color); text-decoration:none; font-size:0.8rem; text-transform:uppercase;">← Retour à la carte</a>
        </nav>
    </header>

    <main class="panier-container">
        <h1>Votre Panier</h1>

        <?php if (empty($_SESSION['panier'])): ?>
            <div class="empty-msg">
                <p>Votre panier est actuellement vide.</p>
                <a href="menu.php" class="back-link">Découvrir notre carte</a>
            </div>
        <?php else: ?>
            <div class="items-list">
                <?php foreach ($_SESSION['panier'] as $index => $item): ?>
                    <div class="item-row">
                        <div class="item-info">
                            <span class="item-name"><?= $item['quantite'] ?>x <?= htmlspecialchars($item['nom']) ?></span>
                            <?php if (isset($item['details'])): ?>
                                <ul class="item-details">
                                    <?php foreach ($item['details'] as $cat => $val): ?>
                                        <li><?= $cat ?> : <?= htmlspecialchars($val) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="item-price"><?= $item['prix'] * $item['quantite'] ?> €</div>
                        <a href="panier.php?action=supprimer&index=<?= $index ?>" class="btn-remove" title="Supprimer l'article">×</a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="total-section">
                <span class="total-label">Montant Total</span>
                <span class="total-price"><?= $total ?> €</span>
            </div>

            <div class="actions-footer">
                <?php if (isset($_SESSION['user'])): ?>
                    <form action="paiement.php" method="POST">
                        <button type="submit" class="btn-pay">Procéder au paiement (CYBank)</button>
                    </form>
                <?php else: ?>
                    <a href="connection.php" class="btn-pay">Se connecter pour commander</a>
                <?php endif; ?>
                
                <a href="panier.php?action=vider" class="btn-empty-cart">Vider entièrement mon panier</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
