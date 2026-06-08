<?php
session_start();

$data = json_decode(file_get_contents('data.json'), true);
$plats = $data['plats'];
$menus = $data['menus'];

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $plats = array_filter($plats, function($p) use ($search) {
        return (stripos($p['nom'], $search) !== false) || (stripos($p['desc'], $search) !== false);
    });
}

// Logique d'ajout au panier
// Ajout au panier depuis menu aléatoire (restaurant.php)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['random_pick'])) {
    if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];
    $ids_to_add = array_filter(array_map('intval', [
        $_POST['id_entree'] ?? 0,
        $_POST['id_plat'] ?? 0,
        $_POST['id_dessert'] ?? 0
    ]));
    foreach ($data['plats'] as $item) {
        if (in_array($item['id'], $ids_to_add)) {
            $cle = 'plat_' . $item['id'] . '_rand';
            if (isset($_SESSION['panier'][$cle])) $_SESSION['panier'][$cle]['quantite']++;
            else $_SESSION['panier'][$cle] = ['nom'=>$item['nom'],'prix'=>$item['prix'],'quantite'=>1,'type'=>'plat'];
        }
    }
    header("Location: panier.php"); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_prod'])) {
    $id = (int)$_POST['id_prod'];
    $type = $_POST['type_prod'];

    if (!isset($_SESSION['panier'])) { $_SESSION['panier'] = []; }

    $cle = $type . "_" . $id;
    if (isset($_SESSION['panier'][$cle])) {
        $_SESSION['panier'][$cle]['quantite']++;
    } else {
        foreach ($data['plats'] as $item) {
            if ($item['id'] === $id) {
                $_SESSION['panier'][$cle] = [
                    'nom' => $item['nom'],
                    'prix' => $item['prix'],
                    'quantite' => 1,
                    'type' => 'plat'
                ];
                break;
            }
        }
    }
    header("Location: menu.php?ajoute=1&search=" . urlencode($search));
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(session_id() ? (isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : bin2hex(random_bytes(16))) : ''); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>La Carte - Les Arcades</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@200;300;400&family=Playfair+Display:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
    <style>
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; --muted-blue: #8FA3BF; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; line-height: 1.8; }
        header { background-color: transparent; padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); }
        .top-bar { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); letter-spacing: 2px; }
        .navbar ul { display: flex; gap: 30px; list-style: none; padding: 0; }
        .navbar a { font-size: 0.8rem; text-transform: uppercase; color: var(--text-color); text-decoration: none; }
        .navbar a:hover { color: var(--gold-color); }

        .search-wrapper { max-width: 500px; margin: 40px auto; }
        .search-form { display: flex; background: rgba(19, 30, 58, 0.4); border: 1px solid rgba(230, 140, 124, 0.3); border-radius: 50px; padding: 5px 5px 5px 25px; }
        .search-input { flex-grow: 1; background: transparent; border: none; color: var(--text-color); outline: none; font-size: 1rem; }
        .search-submit { background: var(--gold-color); border: none; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; }

        .menu-container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        .menu-container > h1, .menu-container > .gold-title { text-align: center; }
        .gold-title { font-family: var(--font-title); color: var(--gold-color); font-size: 3.5rem; text-transform: uppercase; margin-bottom: 10px; }

        /* --- BARRE DE FILTRES (Phase 3) --- */
        .filters-bar {
            background: rgba(19, 30, 58, 0.5);
            border: 1px solid rgba(230, 140, 124, 0.2);
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
            backdrop-filter: blur(10px);
        }
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
        }
        .filter-group h4 {
            color: var(--gold-color);
            font-family: var(--font-title);
            font-size: 1rem;
            margin: 0 0 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .filter-options { display: flex; flex-wrap: wrap; gap: 10px; }
        .filter-options label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(230, 140, 124, 0.1);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: 0.2s;
        }
        .filter-options label:hover { background: rgba(230, 140, 124, 0.25); }
        .filter-options input { accent-color: var(--gold-color); }
        .filter-group select {
            width: 100%;
            padding: 8px 12px;
            background: rgba(6, 11, 25, 0.5);
            border: 1px solid rgba(230, 140, 124, 0.3);
            border-radius: 4px;
            color: var(--text-color);
            font-family: inherit;
            font-size: 0.85rem;
        }

        /* --- GRILLE PLATS DYNAMIQUE --- */
        #plats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 40px;
            margin: 40px 0 60px;
            transition: opacity 0.3s;
        }
        .menu-item {
            background: rgba(19, 30, 58, 0.3);
            border: 1px solid rgba(230, 140, 124, 0.15);
            border-radius: 6px;
            padding: 25px;
            text-align: center;
            transition: 0.3s;
        }
        .menu-item:hover {
            transform: translateY(-3px);
            border-color: var(--gold-color);
            box-shadow: 0 15px 30px rgba(0,0,0,0.3);
        }
        .menu-item h3 { font-family: var(--font-title); font-size: 1.3rem; font-weight: 500; margin: 0 0 8px; }
        .menu-item sub { font-size: 0.7rem; color: var(--gold-color); vertical-align: super; }
        .price { display: block; color: var(--gold-color); font-family: var(--font-title); font-weight: bold; font-size: 1.2rem; margin: 10px 0; }
        .btn-add { background: transparent; border: 1px solid var(--gold-color); color: var(--gold-color); padding: 8px 15px; text-transform: uppercase; font-size: 0.7rem; cursor: pointer; margin-top: 10px; transition: 0.3s; }
        .btn-add:hover { background: var(--gold-color); color: #060B19; }

        .dish-gallery-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; max-width: 1100px; margin: 60px auto; }
        .gallery-block { height: 250px; border-radius: 4px; overflow: hidden; border: 1px solid rgba(230, 140, 124, 0.2); }
        .gallery-block img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
        .gallery-block:hover img { transform: scale(1.05); }

        .experience-grid { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; margin-top: 40px; }
        .exp-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(230, 140, 124, 0.2); padding: 40px; width: 280px; backdrop-filter: blur(10px); display: flex; flex-direction: column; text-align: center; }
        .exp-card h3 { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); margin-bottom: 15px; }
        .exp-detail { font-size: 0.85rem; color: var(--muted-blue); margin-bottom: 20px; flex-grow: 1; }
        .exp-price { font-family: var(--font-title); font-size: 1.4rem; color: var(--gold-color); margin-bottom: 20px; font-weight: bold; }

        footer { text-align: center; padding: 80px 20px; border-top: 1px solid rgba(230, 140, 124, 0.2); margin-top: 100px; }
        footer h3 { font-family: var(--font-title); color: var(--gold-color); font-size: 1.8rem; margin-bottom: 15px; }
        footer p { color: #5C6B85; font-size: 0.9rem; margin: 5px 0; }

        .notif { position: fixed; bottom: 20px; right: 20px; background: var(--gold-color); color: #060B19; padding: 15px 25px; border-radius: 4px; z-index: 1000; }
    </style>
    <link rel="stylesheet" id="theme-css" href="css/theme-dark.css">
    <link rel="stylesheet" href="css/mobile.css">
</head>
<body>
    <header>
        <div class="top-bar">
            <div class="logo">LES ARCADES</div>
            <nav class="navbar">
                <ul>
                    <li><a href="restaurant.php">Accueil</a></li>
                    <li><a href="menu.php">La Carte</a></li>
                    <?php if(isset($_SESSION['panier']) && count($_SESSION['panier']) > 0): ?>
                        <li><a href="panier.php" style="color:var(--gold-color)">Panier (<?= count($_SESSION['panier']) ?>)</a></li>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['user']) && $_SESSION['user']['role'] === 'client'): ?>
                        <li><a href="moncompte.php">Mon Compte</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <?php if(isset($_GET['ajoute'])): ?><div class="notif">Produit ajouté ! <a href="panier.php" style="text-decoration:underline">Voir le panier</a></div><?php endif; ?>

    <main class="menu-container">
        <h1 class="gold-title">Notre Carte</h1>

        <div class="search-wrapper">
            <form class="search-form" action="menu.php" method="GET">
                <input type="text" class="search-input" name="search" placeholder="Chercher un délice..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="search-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#060B19" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </form>
        </div>

        <!-- ============== FILTRES & TRIS (PHASE 3) ============== -->
        <section class="filters-bar">
            <div class="filters-grid">
                <div class="filter-group">
                    <h4>Catégorie</h4>
                    <select name="categorie-filter">
                        <option value="all">Toutes</option>
                        <option value="entrees">Préludes (Entrées)</option>
                        <option value="plats">Cœurs de Fête (Plats)</option>
                        <option value="desserts">Douceurs (Desserts)</option>
                    </select>
                </div>
                <div class="filter-group">
                    <h4>Régime</h4>
                    <div class="filter-options">
                        <label><input type="checkbox" name="regime" value="vegetarien"> Végétarien</label>
                        <label><input type="checkbox" name="regime" value="vegan"> Vegan</label>
                        <label><input type="checkbox" name="regime" value="halal"> Halal</label>
                        <label><input type="checkbox" name="regime" value="sans-gluten"> Sans gluten</label>
                    </div>
                </div>
                <div class="filter-group">
                    <h4>Saveur</h4>
                    <div class="filter-options">
                        <label><input type="checkbox" name="gout" value="epice"> Épicé</label>
                        <label><input type="checkbox" name="gout" value="sucre"> Sucré</label>
                    </div>
                </div>
                <div class="filter-group">
                    <h4>Trier par</h4>
                    <select id="sort-select">
                        <option value="default">Par défaut</option>
                        <option value="prix-asc">Prix croissant</option>
                        <option value="prix-desc">Prix décroissant</option>
                        <option value="populaire">Les plus commandés</option>
                    </select>
                </div>
            </div>
        </section>

        <!-- Grille des plats dynamique (chargée + filtrée en AJAX) -->
        <div id="plats-grid">
            <?php foreach($plats as $plat): ?>
            <div class="menu-item"
                 data-prix="<?= $plat['prix'] ?>"
                 data-popularite="<?= $plat['nb_commandes'] ?? 0 ?>"
                 data-categorie="<?= htmlspecialchars($plat['categorie']) ?>">
                <h3>
                    <?= htmlspecialchars($plat['nom']) ?>
                    <?php if(!empty($plat['allergenes'])): ?><sub><?= htmlspecialchars($plat['allergenes']) ?></sub><?php endif; ?>
                </h3>
                <p style="color:var(--muted-blue); font-size:0.9rem;"><?= htmlspecialchars($plat['desc']) ?></p>
                <span class="price"><?= htmlspecialchars($plat['prix']) ?>€</span>
                <form method="POST">
                    <input type="hidden" name="id_prod" value="<?= $plat['id'] ?>">
                    <input type="hidden" name="type_prod" value="plat">
                    <button type="submit" class="btn-add">Ajouter au panier</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>

        <p style="text-align:center;">
            <a href="allergenes.php" style="color:var(--muted-blue); text-decoration:underline;">Consulter la liste des 14 allergènes</a>
        </p>

        <section class="dish-gallery-grid">
            <div class="gallery-block"><img src="image0.jpeg" alt="Entrée"></div>
            <div class="gallery-block"><img src="image1.jpeg" alt="Plat"></div>
            <div class="gallery-block"><img src="image3.jpeg" alt="Tagine"></div>
            <div class="gallery-block"><img src="image2.jpeg" alt="Thé"></div>
        </section>

        <h2 class="gold-title" style="text-align:center; margin-top:40px;">Nos Expériences</h2>
        <div class="experience-grid">
            <?php foreach($menus as $menu): ?>
            <div class="exp-card">
                <h3><?= htmlspecialchars($menu['nom']) ?></h3>
                <p class="exp-detail"><?= htmlspecialchars($menu['desc']) ?></p>
                <p class="exp-price"><?= htmlspecialchars($menu['prix']) ?>€</p>
                <a href="configurer_menu.php?id=<?= $menu['id'] ?>" class="btn-add">Personnaliser</a>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer>
        <h3>Les Arcades</h3>
        <p>Une odyssée culinaire entre tradition et modernité</p>
        <p><strong>Ouvert tous les jours de 12h à 23h</strong></p>
        <p>5 Bd de la République, 78400 Chatou</p>
        <p style="opacity: 0.5; margin-top:20px;">Prix nets TTC - Service compris</p>
    </footer>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
    <script src="js/hamburger.js"></script>
    <script src="js/menu-filters.js"></script>
</body>
</html>
