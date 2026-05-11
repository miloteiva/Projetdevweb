<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    header("Location: connection.php"); exit();
}

$idCmd = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$data = json_decode(file_get_contents('data.json'), true);
$commande = null;

foreach ($data['commandes'] ?? [] as $c) {
    if ($c['id'] === $idCmd && $c['id_client'] === $_SESSION['user']['id']) {
        $commande = $c; break;
    }
}

// Refuse si commande pas modifiable
if (!$commande) {
    header("Location: moncompte.php"); exit();
}
if ($commande['statut'] !== 'Payée') {
    $msg = "Cette commande est déjà en préparation, vous ne pouvez plus la modifier.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modifier ma commande - Les Arcades</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; --muted-blue: #8FA3BF; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; }
        header { padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); display: flex; justify-content: space-between; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); }
        .nav-link { color: var(--text-color); text-decoration: none; font-size: 0.85rem; }
        .container { max-width: 700px; margin: 50px auto; padding: 40px; background: rgba(19, 30, 58, 0.6); border: 1px solid rgba(230, 140, 124, 0.2); border-radius: 6px; backdrop-filter: blur(10px); }
        h1 { font-family: var(--font-title); color: var(--gold-color); }
        .modif-line { display: grid; grid-template-columns: 2fr 1.5fr 1fr; gap: 15px; align-items: center; padding: 15px; background: rgba(255,255,255,0.04); border-radius: 4px; margin-bottom: 10px; }
        .modif-line h4 { margin: 0; font-family: var(--font-title); color: var(--gold-color); }
        .modif-line .price-unit { color: var(--muted-blue); font-size: 0.85rem; }
        .qte-control { display: flex; gap: 5px; align-items: center; justify-content: center; }
        .qte-control button { background: var(--gold-color); color: var(--bg-color); border: none; width: 32px; height: 32px; border-radius: 4px; cursor: pointer; font-size: 1.2rem; font-weight: bold; }
        .qte-control .qte-input { width: 50px; text-align: center; padding: 6px; background: rgba(0,0,0,0.3); border: 1px solid rgba(230, 140, 124, 0.3); color: var(--text-color); border-radius: 3px; }
        .line-total { text-align: right; font-family: var(--font-title); color: var(--gold-color); font-weight: bold; }
        .summary { background: rgba(230, 140, 124, 0.1); border-left: 3px solid var(--gold-color); padding: 20px; margin: 25px 0; }
        .summary .row { display: flex; justify-content: space-between; margin: 8px 0; }
        .btn-save { background: var(--gold-color); color: var(--bg-color); border: none; padding: 14px 25px; font-family: inherit; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; width: 100%; transition: 0.3s; }
        .btn-save:hover { background: #f2a698; }
        .warning { background: rgba(255, 165, 0, 0.1); border-left: 3px solid orange; padding: 12px 18px; margin: 20px 0; color: orange; font-size: 0.9rem; }
    </style>
    <link rel="stylesheet" id="theme-css" href="css/theme-dark.css">
</head>
<body>
    <header>
        <div class="logo">LES ARCADES</div>
        <a href="moncompte.php" class="nav-link">← Mon compte</a>
    </header>

    <main class="container">
        <h1>Modifier la commande #<?= $commande['id'] ?></h1>

        <?php if (isset($msg)): ?>
            <div class="warning"><?= htmlspecialchars($msg) ?></div>
            <a href="moncompte.php" style="color: var(--gold-color);">Retour à mon compte</a>
        <?php else: ?>
            <p style="color: var(--muted-blue);">Ajustez les quantités, supprimez ou ajoutez des produits.<br>Si le total dépasse le montant payé, un complément vous sera demandé.</p>

            <div class="warning">⚠ Vous ne pouvez modifier que tant que la commande est au statut "Payée" (pas encore démarrée en cuisine).</div>

            <div id="modif-container" data-order-id="<?= $commande['id'] ?>" data-original-total="<?= $commande['total'] ?>">
                <?php foreach ($commande['articles'] as $a): ?>
                    <div class="modif-line" data-prix="<?= $a['prix'] ?>" data-nom="<?= htmlspecialchars($a['nom']) ?>">
                        <div>
                            <h4><?= htmlspecialchars($a['nom']) ?></h4>
                            <div class="price-unit"><?= number_format($a['prix'], 2) ?>€ l'unité</div>
                        </div>
                        <div class="qte-control">
                            <button type="button" class="qte-moins">−</button>
                            <input type="number" class="qte-input" value="<?= $a['quantite'] ?>" min="0" max="20">
                            <button type="button" class="qte-plus">+</button>
                        </div>
                        <div class="line-total"><?= number_format($a['prix'] * $a['quantite'], 2) ?> €</div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary">
                <div class="row">
                    <span>Total initial</span>
                    <strong><?= number_format($commande['total'], 2) ?> €</strong>
                </div>
                <div class="row">
                    <span>Nouveau total</span>
                    <strong id="current-total"><?= number_format($commande['total'], 2) ?> €</strong>
                </div>
                <div class="row">
                    <span>Différence</span>
                    <span id="diff-display"><span style="color:var(--muted-blue)">Aucun changement</span></span>
                </div>
            </div>

            <button id="btn-save-modifs" class="btn-save">Enregistrer les modifications</button>
        <?php endif; ?>
    </main>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
    <script src="js/order-modify.js"></script>
</body>
</html>
