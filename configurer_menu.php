<?php
session_start();
$data = json_decode(file_get_contents('data.json'), true);

$menu_id = $_GET['id'] ?? null;
$menu_details = null;

foreach($data['menus'] as $m) {
    if($m['id'] == $menu_id) { $menu_details = $m; break; }
}

if(!$menu_details) { header("Location: menu.php"); exit(); }

// Définition de la structure selon le menu
// Sahara (ID 1) : 3 temps | Quintessence (ID 2) : 5 temps | Eclipse (ID 3) : 7 services
if ($menu_id == 1) {
    $structure = ['Entrée' => 1, 'Plat' => 1, 'Dessert' => 1];
} elseif ($menu_id == 2) {
    $structure = ['Entrées' => 2, 'Plats' => 2, 'Dessert' => 1];
} else {
    $structure = ['Entrées' => 2, 'Plats' => 3, 'Desserts' => 2];
}

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
        'details' => $_POST['choix'] // On récupère tous les choix dynamiques
    ];
    header("Location: menu.php?ajoute=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Configuration <?= htmlspecialchars($menu_details['nom']) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <style>
        body { background: #060B19; color: #E8F1F5; font-family: 'Montserrat', sans-serif; padding: 40px; }
        .config-card { max-width: 600px; margin: 0 auto; background: rgba(19, 30, 58, 0.8); padding: 40px; border: 1px solid #E68C7C; }
        h1 { font-family: 'Playfair Display', serif; color: #E68C7C; margin-bottom: 10px; }
        .desc { color: #8FA3BF; font-style: italic; margin-bottom: 30px; font-size: 0.9rem; }
        label { display: block; margin: 15px 0 5px; color: #E68C7C; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; }
        select { width: 100%; padding: 10px; background: #02050E; border: 1px solid rgba(230,140,124,0.4); color: white; margin-bottom: 10px; }
        .btn-submit { background: #E68C7C; color: #060B19; border: none; padding: 15px; width: 100%; cursor: pointer; font-weight: bold; margin-top: 30px; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="config-card">
        <h1><?= htmlspecialchars($menu_details['nom']) ?></h1>
        <p class="desc"><?= htmlspecialchars($menu_details['desc']) ?> — <?= htmlspecialchars($menu_details['prix']) ?>€</p>
        
        <form method="POST">
            <?php foreach($structure as $label => $quantite): ?>
                <?php for($i = 1; $i <= $quantite; $i++): ?>
                    <label><?= $label ?> (n°<?= $i ?>)</label>
                    <select name="choix[<?= $label ?> <?= $i ?>]" required>
                        <?php 
                        // On choisit la liste de plats selon le label
                        $liste = strpos($label, 'Entrée') !== false ? $entrees : (strpos($label, 'Plat') !== false ? $plats : $desserts);
                        foreach($liste as $p): 
                        ?>
                            <option value="<?= htmlspecialchars($p['nom']) ?>"><?= htmlspecialchars($p['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endfor; ?>
            <?php endforeach; ?>
            
            <button type="submit" class="btn-submit">Ajouter au panier</button>
            <a href="menu.php" style="display:block; text-align:center; color:#8FA3BF; margin-top:15px; text-decoration:none; font-size:0.8rem;">Retour</a>
        </form>
    </div>
</body>
</html>
