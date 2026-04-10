<?php
session_start();
$data = json_decode(file_get_contents('data.json'), true);

$menu_id = $_GET['id'] ?? null;
$menu_details = null;
foreach($data['menus'] as $m) {
    if($m['id'] == $menu_id) { $menu_details = $m; break; }
}

if(!$menu_details) { header("Location: menu.php"); exit(); }

$entrees = array_filter($data['plats'], function($p) { return $p['categorie'] === 'Préludes'; });
$plats = array_filter($data['plats'], function($p) { return $p['categorie'] === 'Cœurs de Fête'; });
$desserts = array_filter($data['plats'], function($p) { return $p['categorie'] === 'Douceurs'; });

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['panier'])) { $_SESSION['panier'] = []; }
    
    $_SESSION['panier'][] = [
        'nom' => "Menu " . $menu_details['nom'],
        'prix' => $menu_details['prix'],
        'quantite' => 1,
        'type' => 'menu',
        'details' => [
            'Entrée' => $_POST['entree'],
            'Plat' => $_POST['plat'],
            'Dessert' => $_POST['dessert']
        ]
    ];
    header("Location: menu.php?ajoute=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Composer mon menu - Les Arcades</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <style>
        body { background: #060B19; color: #E8F1F5; font-family: 'Montserrat', sans-serif; padding: 40px; }
        .config-card { max-width: 500px; margin: 0 auto; background: rgba(19, 30, 58, 0.8); padding: 40px; border: 1px solid #E68C7C; }
        h1 { font-family: 'Playfair Display', serif; color: #E68C7C; margin-bottom: 20px; }
        label { display: block; margin: 20px 0 10px; color: #8FA3BF; text-transform: uppercase; font-size: 0.75rem; }
        select { width: 100%; padding: 12px; background: #02050E; border: 1px solid #E68C7C; color: white; }
        .btn-submit { background: #E68C7C; color: #060B19; border: none; padding: 15px; width: 100%; cursor: pointer; font-weight: bold; margin-top: 30px; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="config-card">
        <h1>Menu <?= htmlspecialchars($menu_details['nom']) ?></h1>
        <form method="POST">
            <label>Entrée</label>
            <select name="entree" required>
                <?php foreach($entrees as $e): ?><option value="<?= htmlspecialchars($e['nom']) ?>"><?= htmlspecialchars($e['nom']) ?></option><?php endforeach; ?>
            </select>
            <label>Plat</label>
            <select name="plat" required>
                <?php foreach($plats as $p): ?><option value="<?= htmlspecialchars($p['nom']) ?>"><?= htmlspecialchars($p['nom']) ?></option><?php endforeach; ?>
            </select>
            <label>Dessert</label>
            <select name="dessert" required>
                <?php foreach($desserts as $d): ?><option value="<?= htmlspecialchars($d['nom']) ?>"><?= htmlspecialchars($d['nom']) ?></option><?php endforeach; ?>
            </select>
            <button type="submit" class="btn-submit">Valider mon choix</button>
        </form>
    </div>
</body>
</html>
