<?php
session_start();

// Chargement des données
$data = json_decode(file_get_contents('data.json'), true);
$plats = $data['plats'];
$menus = $data['menus'];

// --- LOGIQUE DE RECHERCHE ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($search !== '') {
    $plats = array_filter($plats, function($p) use ($search) {
        return (stripos($p['nom'], $search) !== false) || (stripos($p['desc'], $search) !== false);
    });
}

// Logique d'ajout au panier
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

function getPlatsParCategorie($plats, $categorie) {
    return array_filter($plats, function($p) use ($categorie) {
        return $p['categorie'] === $categorie;
    });
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
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

        /* --- RECHERCHE --- */
        .search-wrapper { max-width: 500px; margin: 40px auto; }
        .search-form { display: flex; background: rgba(19, 30, 58, 0.4); border: 1px solid rgba(230, 140, 124, 0.3); border-radius: 50px; padding: 5px 5px 5px 25px; }
        .search-input { flex-grow: 1; background: transparent; border: none; color: var(--text-color); outline: none; font-size: 1rem; }
        .search-submit { background: var(--gold-color); border: none; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; }

        .menu-container { max-width: 1200px; margin: 0 auto; padding: 40px 20px; text-align: center; }
        .gold-title { font-family: var(--font-title); color: var(--gold-color); font-size: 3.5rem; text-transform: uppercase; margin-bottom: 10px; }
        
        .grid-menu { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 60px; margin-top: 60px; margin-bottom: 60px;}
        .category-title { font-family: var(--font-title); color: var(--gold-color); font-size: 1.8rem; text-transform: uppercase; border-bottom: 1px solid rgba(230, 140, 124, 0.2); display: inline-block; padding-bottom: 10px; margin-bottom: 40px; }
        
        .menu-item { margin-bottom: 45px; }
        .menu-item h3 { font-family: var(--font-title); font-size: 1.4rem; font-weight: 500; }
        .menu-item sub { font-size: 0.7rem; color: var(--gold-color); vertical-align: super; }
        .price { display: block; color: var(--gold-color); font-family: var(--font-title); font-weight: bold; font-size: 1.2rem; }
        
        .btn-add { background: transparent; border: 1px solid var(--gold-color); color: var(--gold-color); padding: 8px 15px; text-transform: uppercase; font-size: 0.7rem; cursor: pointer; margin-top: 15px; transition: 0.3s; }
        .btn-add:hover { background: var(--gold-color); color: #060B19; }

        /* --- GALERIE PHOTO (4 BLOCS) --- */
        .dish-gallery-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; max-width: 1100px; margin: 60px auto; }
        .gallery-block { height: 250px; border-radius: 4px; overflow: hidden; border: 1px solid rgba(230, 140, 124, 0.2); box-shadow: 0 10px 20px rgba(0,0,0,0.3); }
        .gallery-block img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
        .gallery-block:hover img { transform: scale(1.05); }

        /* --- SECTION EXPÉRIENCES (3 BLOCS COTE A COTE) --- */
        .experience-grid { display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; margin-top: 40px; }
        .exp-card { 
            background: rgba(255, 255, 255, 0.03); 
            border: 1px solid rgba(230, 140, 124, 0.2); 
            padding: 40px; 
            width: 280px; /* Retour à la largeur initiale pour 3 blocs côte à côte */
            backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            text-align: center;
        }
        .exp-card h3 { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); margin-bottom: 15px; }
        .exp-detail { font-size: 0.85rem; color: var(--muted-blue); margin-bottom: 20px; flex-grow: 1; }
        .exp-price { font-family: var(--font-title); font-size: 1.4rem; color: var(--gold-color); margin-bottom: 20px; font-weight: bold; }
        .btn-config { margin-top: auto; }

        footer { text-align: center; padding: 80px 20px; border-top: 1px solid rgba(230, 140, 124, 0.2); margin-top: 100px; }
        footer h3 { font-family: var(--font-title); color: var(--gold-color); font-size: 1.8rem; margin-bottom: 15px; }
        footer p { color: #5C6B85; font-size: 0.9rem; margin: 5px 0; }
        
        .notif { position: fixed; bottom: 20px; right: 20px; background: var(--gold-color); color: #060B19; padding: 15px 25px; border-radius: 4px; z-index: 1000; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    </style>
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

        <div class="grid-menu">
            <?php 
            foreach(['Préludes', 'Cœurs de Fête', 'Douceurs'] as $cat): 
                $platsCat = getPlatsParCategorie($plats, $cat);
                if (empty($platsCat)) continue;
            ?>
            <section class="menu-column">
                <h2 class="category-title"><?= $cat ?></h2>
                <?php foreach($platsCat as $plat): ?>
                <div class="menu-item">
                    <h3><?= htmlspecialchars($plat['nom']) ?> <?php if(!empty($plat['allergenes'])): ?><sub><?= htmlspecialchars($plat['allergenes']) ?></sub><?php endif; ?></h3>
                    <p style="color:var(--muted-blue); font-size:0.9rem;"><?= htmlspecialchars($plat['desc']) ?></p>
                    <span class="price"><?= htmlspecialchars($plat['prix']) ?>€</span>
                    <form method="POST">
                        <input type="hidden" name="id_prod" value="<?= $plat['id'] ?>"><input type="hidden" name="type_prod" value="plat">
                        <button type="submit" class="btn-add">Ajouter au panier</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </section>
            <?php endforeach; ?>
        </div>
        
        <a href="allergenes.php" style="color:var(--muted-blue); text-decoration:underline; display:block; margin-top:40px;">Consulter la liste des allergènes</a>

        <section class="dish-gallery-grid">
            <div class="gallery-block"><img src="image0.jpeg" alt="Entrée"></div> <div class="gallery-block"><img src="image1.jpeg" alt="Plat"></div> <div class="gallery-block"><img src="image3.jpeg" alt="Tagine"></div> <div class="gallery-block"><img src="image2.jpeg" alt="Thé"></div> </section>

        <h2 class="gold-title" style="margin-top:40px;">Nos Expériences</h2>
        <div class="experience-grid">
            <?php foreach($menus as $menu): ?>
            <div class="exp-card">
                <h3><?= htmlspecialchars($menu['nom']) ?></h3>
                <p class="exp-detail"><?= htmlspecialchars($menu['desc']) ?></p>
                <p class="exp-price"><?= htmlspecialchars($menu['prix']) ?>€</p>
                <a href="configurer_menu.php?id=<?= $menu['id'] ?>" class="btn-add btn-config">Personnaliser</a>
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
</body>
</html>
