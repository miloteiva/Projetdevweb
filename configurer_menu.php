<?php
session_start();
$data = json_decode(file_get_contents('data.json'), true);
$menus = $data['menus'];
$plats = $data['plats'];

$id_menu = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$menu_choisi = null;
foreach ($menus as $m) {
    if ($m['id'] === $id_menu) { $menu_choisi = $m; break; }
}

// Catégoriser les plats
$entrees  = array_filter($plats, fn($p) => $p['categorie'] === 'Préludes');
$mains    = array_filter($plats, fn($p) => $p['categorie'] === 'Cœurs de Fête');
$desserts = array_filter($plats, fn($p) => $p['categorie'] === 'Douceurs');

// Traitement de l'ajout au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_menu'])) {
    if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];
    $details = [];
    if (!empty($_POST['entree']))  $details['Entrée'] = $_POST['entree'];
    if (!empty($_POST['plat']))    $details['Plat'] = $_POST['plat'];
    if (!empty($_POST['dessert'])) $details['Dessert'] = $_POST['dessert'];

    $cle = 'menu_' . $id_menu . '_' . uniqid();
    $_SESSION['panier'][$cle] = [
        'nom' => 'Menu ' . $menu_choisi['nom'],
        'prix' => $menu_choisi['prix'],
        'quantite' => 1,
        'type' => 'menu',
        'details' => $details
    ];
    header("Location: panier.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Configurer le menu - Les Arcades</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; --muted-blue: #8FA3BF; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; }
        header { padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); display: flex; justify-content: space-between; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); }
        .nav-link { color: var(--text-color); text-decoration: none; font-size: 0.85rem; }
        .config-container { max-width: 800px; margin: 50px auto; padding: 40px; background: rgba(19, 30, 58, 0.6); border: 1px solid rgba(230, 140, 124, 0.2); border-radius: 6px; backdrop-filter: blur(10px); }
        h1 { font-family: var(--font-title); color: var(--gold-color); }
        .step { background: rgba(255,255,255,0.04); padding: 20px; border-radius: 4px; margin: 20px 0; }
        .step h3 { font-family: var(--font-title); color: var(--gold-color); margin: 0 0 12px; }
        .choices { display: grid; grid-template-columns: 1fr; gap: 8px; }
        .choices label { display: flex; gap: 12px; align-items: center; padding: 10px; background: rgba(0,0,0,0.2); border-radius: 3px; cursor: pointer; transition: 0.2s; }
        .choices label:hover { background: rgba(230, 140, 124, 0.1); }
        .choices input[type="radio"] { accent-color: var(--gold-color); }
        .total-box { background: rgba(230, 140, 124, 0.1); border-left: 3px solid var(--gold-color); padding: 15px 20px; margin: 20px 0; }
        .total-box .amount { font-family: var(--font-title); color: var(--gold-color); font-size: 2rem; }
        .btn-add { background: var(--gold-color); color: var(--bg-color); border: none; padding: 14px 30px; font-family: inherit; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; width: 100%; transition: 0.3s; }
        .btn-add:hover { background: #f2a698; }
    </style>
    <link rel="stylesheet" id="theme-css" href="css/theme-dark.css">
</head>
<body>
    <header>
        <div class="logo">LES ARCADES</div>
        <a href="menu.php" class="nav-link">← Retour à la carte</a>
    </header>

    <main class="config-container">
        <?php if (!$menu_choisi): ?>
            <h1>Menu introuvable</h1>
            <a href="menu.php" style="color: var(--gold-color);">Retour à la carte</a>
        <?php else: ?>
            <h1>Personnaliser : <?= htmlspecialchars($menu_choisi['nom']) ?></h1>
            <p style="color: var(--muted-blue);"><?= htmlspecialchars($menu_choisi['desc']) ?></p>

            <div class="total-box">
                <div style="font-size: 0.8rem; color: var(--muted-blue);">Prix du menu</div>
                <div class="amount"><?= number_format($menu_choisi['prix'], 2) ?> €</div>
            </div>

            <form method="POST">
                <div class="step">
                    <h3>1. Choisissez votre entrée</h3>
                    <div class="choices">
                        <?php foreach ($entrees as $p): ?>
                            <label><input type="radio" name="entree" value="<?= htmlspecialchars($p['nom']) ?>"> <strong><?= htmlspecialchars($p['nom']) ?></strong> <span style="color: var(--muted-blue); font-size: 0.85rem;">- <?= htmlspecialchars($p['desc']) ?></span></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="step">
                    <h3>2. Choisissez votre plat</h3>
                    <div class="choices">
                        <?php foreach ($mains as $p): ?>
                            <label><input type="radio" name="plat" value="<?= htmlspecialchars($p['nom']) ?>"> <strong><?= htmlspecialchars($p['nom']) ?></strong> <span style="color: var(--muted-blue); font-size: 0.85rem;">- <?= htmlspecialchars($p['desc']) ?></span></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="step">
                    <h3>3. Choisissez votre dessert</h3>
                    <div class="choices">
                        <?php foreach ($desserts as $p): ?>
                            <label><input type="radio" name="dessert" value="<?= htmlspecialchars($p['nom']) ?>"> <strong><?= htmlspecialchars($p['nom']) ?></strong> <span style="color: var(--muted-blue); font-size: 0.85rem;">- <?= htmlspecialchars($p['desc']) ?></span></label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" name="valider_menu" class="btn-add">Ajouter ce menu au panier</button>
            </form>
        <?php endif; ?>
    </main>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
</body>
</html>
