<?php
session_start();
$data = json_decode(file_get_contents('data.json'), true);
$menus = $data['menus'];
$plats = $data['plats'];

// Gestion ajout panier depuis menu aléatoire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['random_pick'])) {
    $allPlats = $data['plats'] ?? [];
    $ids = [(int)($_POST['id_entree']??0),(int)($_POST['id_plat']??0),(int)($_POST['id_dessert']??0)];
    if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];
    foreach ($allPlats as $p) {
        if (in_array($p['id'], $ids)) {
            $cle = 'plat_'.$p['id'].'_rand';
            if (isset($_SESSION['panier'][$cle])) $_SESSION['panier'][$cle]['quantite']++;
            else $_SESSION['panier'][$cle] = ['nom'=>$p['nom'],'prix'=>$p['prix'],'quantite'=>1,'type'=>'plat'];
        }
    }
    header('Location: panier.php'); exit();
}

$id_menu = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$menu_choisi = null;
foreach ($menus as $m) {
    if ($m['id'] === $id_menu) { $menu_choisi = $m; break; }
}

// Catégoriser les plats existants pour piocher dedans
$entrees  = array_values(array_filter($plats, fn($p) => $p['categorie'] === 'Préludes'));
$mains    = array_values(array_filter($plats, fn($p) => $p['categorie'] === 'Cœurs de Fête'));
$desserts = array_values(array_filter($plats, fn($p) => $p['categorie'] === 'Douceurs'));

/* ---------------------------------------------------------------
   DÉFINITION DYNAMIQUE DES SERVICES PAR MENU
   Sahara (3 services - 45€)        : Entrée + Plat + Dessert
   Quintessence (5 services - 75€)  : Amuse-bouche + Entrée + Plat + Trou normand + Dessert
   Éclipse (7 services - 110€)      : Mise en bouche + Entrée froide + Entrée chaude
                                     + Sorbet + Plat + Plateau de douceurs + Mignardises
   --------------------------------------------------------------- */
function getServicesPourMenu($id, $entrees, $mains, $desserts) {
    if ($id === 1) {
        // SAHARA - 3 services
        return [
            ['nom' => 'entree',  'titre' => '1. Choisissez votre entrée',   'choices' => $entrees,  'fixe' => null],
            ['nom' => 'plat',    'titre' => '2. Choisissez votre plat',     'choices' => $mains,    'fixe' => null],
            ['nom' => 'dessert', 'titre' => '3. Choisissez votre dessert',  'choices' => $desserts, 'fixe' => null],
        ];
    }
    if ($id === 2) {
        // QUINTESSENCE - 5 services
        return [
            ['nom' => 'amuse',   'titre' => '1. Amuse-bouche du Chef',                                  'choices' => array_slice($entrees, 0, 3), 'fixe' => null],
            ['nom' => 'entree',  'titre' => '2. Entrée raffinée',                                       'choices' => $entrees,  'fixe' => null],
            ['nom' => 'plat',    'titre' => '3. Plat signature',                                        'choices' => $mains,    'fixe' => null],
            ['nom' => 'trou',    'titre' => '4. Trou normand',                                          'choices' => null,      'fixe' => 'Sorbet citron-menthe & eau-de-vie de figue'],
            ['nom' => 'dessert', 'titre' => '5. Dessert d\'inspiration',                                'choices' => $desserts, 'fixe' => null],
        ];
    }
    if ($id === 3) {
        // ÉCLIPSE - 7 services avec accord mets & vins
        return [
            ['nom' => 'mise',     'titre' => '1. Mise en bouche',                                       'choices' => null,                        'fixe' => 'Velouté d\'amandes au safran d\'Atlas (préparé par le Chef)'],
            ['nom' => 'froide',   'titre' => '2. Entrée froide',                                        'choices' => array_slice($entrees, 0, 3), 'fixe' => null],
            ['nom' => 'chaude',   'titre' => '3. Entrée chaude',                                        'choices' => array_slice($entrees, 2, 3), 'fixe' => null],
            ['nom' => 'sorbet',   'titre' => '4. Sorbet de fraîcheur',                                  'choices' => null,                        'fixe' => 'Sorbet à la rose de Damas (offert)'],
            ['nom' => 'plat',     'titre' => '5. Plat principal • Accord mets & vins',                  'choices' => $mains,                      'fixe' => null],
            ['nom' => 'plateau',  'titre' => '6. Plateau de douceurs',                                  'choices' => $desserts,                   'fixe' => null],
            ['nom' => 'mignard',  'titre' => '7. Mignardises & Thé à la menthe',                        'choices' => null,                        'fixe' => 'Sélection de 4 mignardises + Thé traditionnel à la menthe fraîche'],
        ];
    }
    return [];
}

$services = $menu_choisi ? getServicesPourMenu($menu_choisi['id'], $entrees, $mains, $desserts) : [];

// Traitement validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_menu']) && $menu_choisi) {
    if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];
    $details = [];
    foreach ($services as $s) {
        if ($s['fixe']) {
            $details[$s['titre']] = $s['fixe'];
        } elseif (!empty($_POST[$s['nom']])) {
            $details[$s['titre']] = $_POST[$s['nom']];
        }
    }

    $cle = 'menu_' . $id_menu . '_' . uniqid();
    $_SESSION['panier'][$cle] = [
        'nom'      => 'Menu ' . $menu_choisi['nom'],
        'prix'     => $menu_choisi['prix'],
        'quantite' => 1,
        'type'     => 'menu',
        'details'  => $details
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
        .config-container { max-width: 850px; margin: 50px auto; padding: 40px; background: rgba(19, 30, 58, 0.6); border: 1px solid rgba(230, 140, 124, 0.2); border-radius: 6px; backdrop-filter: blur(10px); }
        h1 { font-family: var(--font-title); color: var(--gold-color); margin-bottom: 5px; }
        .menu-meta { display: flex; gap: 18px; flex-wrap: wrap; margin-bottom: 8px; color: var(--muted-blue); font-size: 0.85rem; }
        .menu-meta span { background: rgba(230, 140, 124, 0.1); padding: 4px 12px; border-radius: 14px; }
        .step { background: rgba(255,255,255,0.04); padding: 22px; border-radius: 4px; margin: 18px 0; border-left: 3px solid var(--gold-color); }
        .step h3 { font-family: var(--font-title); color: var(--gold-color); margin: 0 0 14px; font-size: 1.15rem; }
        .choices { display: grid; grid-template-columns: 1fr; gap: 8px; }
        .choices label { display: flex; gap: 12px; align-items: flex-start; padding: 12px; background: rgba(0,0,0,0.2); border-radius: 3px; cursor: pointer; transition: 0.2s; }
        .choices label:hover { background: rgba(230, 140, 124, 0.1); }
        .choices input[type="radio"] { accent-color: var(--gold-color); margin-top: 3px; }
        .fixe-line { background: rgba(230, 140, 124, 0.08); border: 1px dashed rgba(230, 140, 124, 0.3); padding: 14px; border-radius: 3px; font-style: italic; color: var(--text-color); }
        .fixe-line strong { color: var(--gold-color); font-style: normal; display: block; margin-bottom: 4px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
        .total-box { background: rgba(230, 140, 124, 0.1); border-left: 3px solid var(--gold-color); padding: 18px 22px; margin: 20px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
        .total-box .amount { font-family: var(--font-title); color: var(--gold-color); font-size: 2.2rem; }
        .total-box .services-count { color: var(--muted-blue); font-size: 0.85rem; }
        .btn-add { background: var(--gold-color); color: var(--bg-color); border: none; padding: 14px 30px; font-family: inherit; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; width: 100%; transition: 0.3s; }
        .btn-add:hover { background: #f2a698; }
    </style>
    <link rel="stylesheet" id="theme-css" href="css/theme-dark.css">
    <link rel="stylesheet" href="css/mobile.css">
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
            <div class="menu-meta">
                <span>📍 <?= count($services) ?> services</span>
                <span>💎 Prix fixe</span>
                <?php if ($menu_choisi['id'] === 3): ?><span>🍷 Accord mets &amp; vins inclus</span><?php endif; ?>
            </div>
            <p style="color: var(--muted-blue); margin-top: 10px;"><?= htmlspecialchars($menu_choisi['desc']) ?></p>

            <div class="total-box">
                <div>
                    <div style="font-size: 0.8rem; color: var(--muted-blue);">Prix du menu</div>
                    <div class="amount"><?= number_format($menu_choisi['prix'], 2) ?> €</div>
                </div>
                <div class="services-count">
                    Menu en <strong><?= count($services) ?> temps</strong>
                </div>
            </div>

            <form method="POST">
                <?php foreach ($services as $s): ?>
                    <div class="step">
                        <h3><?= htmlspecialchars($s['titre']) ?></h3>

                        <?php if ($s['fixe']): ?>
                            <div class="fixe-line">
                                <strong>Choix du Chef</strong>
                                <?= htmlspecialchars($s['fixe']) ?>
                            </div>
                        <?php else: ?>
                            <div class="choices">
                                <?php foreach ($s['choices'] as $p): ?>
                                    <label>
                                        <input type="radio" name="<?= $s['nom'] ?>" value="<?= htmlspecialchars($p['nom']) ?>" required>
                                        <span>
                                            <strong><?= htmlspecialchars($p['nom']) ?></strong>
                                            <span style="color: var(--muted-blue); font-size: 0.85rem; display: block; margin-top: 2px;"><?= htmlspecialchars($p['desc']) ?></span>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <button type="submit" name="valider_menu" class="btn-add">Ajouter ce menu (<?= count($services) ?> services) au panier</button>
            </form>
        <?php endif; ?>
    </main>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
    <script src="js/hamburger.js"></script>

    <!-- SECTION CRUD PLATS & MENUS (Phase 4 - Restaurateur/Admin) -->
    <?php if (isset($_SESSION['user']) && in_array($_SESSION['user']['role'], ['restaurateur','admin'])): ?>
    <section style="max-width:1100px;margin:50px auto 80px;padding:0 20px;">
        <div style="background:rgba(19,30,58,0.5);border:1px solid rgba(230,140,124,0.2);border-radius:6px;padding:35px;backdrop-filter:blur(10px);">
            <h2 style="font-family:'Playfair Display',serif;color:#E68C7C;margin:0 0 25px;padding-bottom:12px;border-bottom:1px solid rgba(230,140,124,0.2);">
                Gestion de la Carte
            </h2>
            
            <!-- PLATS -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                <h3 style="font-family:'Playfair Display',serif;color:#E68C7C;margin:0;">Plats (<?= count($data['plats']) ?>)</h3>
                <button id="btn-new-plat" class="btn-action">+ Nouveau plat</button>
            </div>
            <table style="width:100%;border-collapse:collapse;background:rgba(255,255,255,0.03);font-size:0.85rem;margin-bottom:40px;">
                <thead>
                    <tr style="background:rgba(230,140,124,0.1);">
                        <th style="padding:10px;text-align:left;color:#E68C7C;font-family:'Playfair Display',serif;font-weight:normal;">Nom</th>
                        <th style="padding:10px;text-align:left;color:#E68C7C;font-family:'Playfair Display',serif;font-weight:normal;">Catégorie</th>
                        <th style="padding:10px;text-align:left;color:#E68C7C;font-family:'Playfair Display',serif;font-weight:normal;">Prix</th>
                        <th style="padding:10px;text-align:left;color:#E68C7C;font-family:'Playfair Display',serif;font-weight:normal;">Statut</th>
                        <th style="padding:10px;text-align:left;color:#E68C7C;font-family:'Playfair Display',serif;font-weight:normal;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['plats'] as $p): ?>
                    <tr style="border-bottom:1px solid rgba(230,140,124,0.08);<?= empty($p['actif'])?'opacity:0.45':'' ?>">
                        <td style="padding:10px;color:#E8F1F5;"><?= htmlspecialchars($p['nom']) ?></td>
                        <td style="padding:10px;color:#8FA3BF;font-size:0.8rem;"><?= htmlspecialchars($p['categorie']) ?></td>
                        <td style="padding:10px;color:#E68C7C;font-family:'Playfair Display',serif;"><?= number_format($p['prix'],2) ?> €</td>
                        <td style="padding:10px;">
                            <?php if(!empty($p['actif'])): ?>
                                <span style="color:#6bcf7f;font-size:0.75rem;">● Actif</span>
                            <?php else: ?>
                                <span style="color:#ff6b6b;font-size:0.75rem;">● Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:10px;">
                            <button class="btn-action btn-small btn-edit-plat" 
                                    data-plat='<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>'
                                    style="font-size:0.7rem;padding:5px 10px;margin-right:5px;">Modifier</button>
                            <button class="btn-action btn-small btn-toggle-plat" 
                                    data-id="<?= $p['id'] ?>"
                                    style="font-size:0.7rem;padding:5px 10px;margin-right:5px;"><?= empty($p['actif'])?'Activer':'Désactiver' ?></button>
                            <button class="btn-action btn-small btn-delete-plat" 
                                    data-id="<?= $p['id'] ?>"
                                    style="font-size:0.7rem;padding:5px 10px;border-color:#ff6b6b;color:#ff6b6b;">Supprimer</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- MENUS -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                <h3 style="font-family:'Playfair Display',serif;color:#E68C7C;margin:0;">Menus (<?= count($data['menus']) ?>)</h3>
                <button id="btn-new-menu" class="btn-action">+ Nouveau menu</button>
            </div>
            <table style="width:100%;border-collapse:collapse;background:rgba(255,255,255,0.03);font-size:0.85rem;">
                <thead>
                    <tr style="background:rgba(230,140,124,0.1);">
                        <th style="padding:10px;text-align:left;color:#E68C7C;font-family:'Playfair Display',serif;font-weight:normal;">Nom</th>
                        <th style="padding:10px;text-align:left;color:#E68C7C;font-family:'Playfair Display',serif;font-weight:normal;">Services</th>
                        <th style="padding:10px;text-align:left;color:#E68C7C;font-family:'Playfair Display',serif;font-weight:normal;">Prix</th>
                        <th style="padding:10px;text-align:left;color:#E68C7C;font-family:'Playfair Display',serif;font-weight:normal;">Statut</th>
                        <th style="padding:10px;text-align:left;color:#E68C7C;font-family:'Playfair Display',serif;font-weight:normal;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['menus'] as $m): ?>
                    <tr style="border-bottom:1px solid rgba(230,140,124,0.08);<?= empty($m['actif'])?'opacity:0.45':'' ?>">
                        <td style="padding:10px;color:#E8F1F5;"><?= htmlspecialchars($m['nom']) ?></td>
                        <td style="padding:10px;color:#8FA3BF;"><?= $m['nb_services'] ?> temps</td>
                        <td style="padding:10px;color:#E68C7C;font-family:'Playfair Display',serif;"><?= number_format($m['prix'],2) ?> €</td>
                        <td style="padding:10px;">
                            <?php if(!empty($m['actif'])): ?>
                                <span style="color:#6bcf7f;font-size:0.75rem;">● Actif</span>
                            <?php else: ?>
                                <span style="color:#ff6b6b;font-size:0.75rem;">● Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:10px;">
                            <button class="btn-action btn-small btn-edit-menu" 
                                    data-menu='<?= htmlspecialchars(json_encode($m), ENT_QUOTES) ?>'
                                    style="font-size:0.7rem;padding:5px 10px;margin-right:5px;">Modifier</button>
                            <button class="btn-action btn-small btn-toggle-menu" 
                                    data-id="<?= $m['id'] ?>"
                                    style="font-size:0.7rem;padding:5px 10px;"><?= empty($m['actif'])?'Activer':'Désactiver' ?></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>

    <!-- STYLES MODAL -->
    <style>
    .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.75);display:flex;align-items:center;justify-content:center;z-index:9990;backdrop-filter:blur(5px);}
    .modal-box{background:rgba(19,30,58,0.98);border:1px solid #E68C7C;padding:35px;max-width:580px;width:90%;max-height:90vh;overflow-y:auto;border-radius:6px;box-shadow:0 30px 60px rgba(0,0,0,0.6);}
    .modal-box h2{font-family:'Playfair Display',serif;color:#E68C7C;margin:0 0 20px;}
    .modal-box .input-group{margin-bottom:14px;display:flex;flex-direction:column;}
    .modal-box .input-group label{font-size:0.72rem;text-transform:uppercase;color:#8FA3BF;letter-spacing:1px;margin-bottom:5px;}
    .modal-box input,.modal-box select,.modal-box textarea{padding:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(230,140,124,0.3);color:#E8F1F5;border-radius:4px;font-family:inherit;font-size:0.9rem;}
    .modal-box textarea{resize:vertical;font-family:inherit;}
    .modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:20px;}
    </style>
    <script src="js/resto-crud.js"></script>

</body>
</html>
