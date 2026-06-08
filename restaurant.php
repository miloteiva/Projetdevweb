<?php
session_start();
$data = json_decode(file_get_contents('data.json'), true);
// Plats populaires : top 4 par nb_commandes
$populaires = array_filter($data['plats'] ?? [], fn($p) => !empty($p['actif']));
usort($populaires, fn($a,$b) => ($b['nb_commandes']??0) <=> ($a['nb_commandes']??0));
$populaires = array_slice(array_values($populaires), 0, 4);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Les Arcades - Restaurant Oriental</title>
    <meta name="csrf-token" content="<?php echo htmlspecialchars(bin2hex(random_bytes(16))); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-color: #060B19;
            --bg-alt: #131E3A;
            --text-color: #E8F1F5;
            --gold-color: #E68C7C;
            --font-title: 'Playfair Display', serif;
            --font-body: 'Montserrat', sans-serif;
        }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; line-height: 1.6; }
        header { background-color: transparent; padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); }
        a { text-decoration: none; color: inherit; transition: 0.3s; }
        ul { list-style: none; padding: 0; margin: 0; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); letter-spacing: 2px; text-shadow: 0 0 15px rgba(230, 140, 124, 0.4); }
        .navbar ul { display: flex; gap: 30px; }
        .navbar a { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-color); }
        .navbar a:hover { color: var(--gold-color); }
        .intro-section { text-align: center; padding: 100px 20px; max-width: 900px; margin: 0 auto; }
        .main-title { font-family: var(--font-title); font-size: 4rem; font-weight: 400; color: var(--gold-color); margin-bottom: 25px; text-shadow: 2px 2px 0px rgba(0,0,0,0.5); }
        .intro-text { font-size: 1.3rem; color: var(--text-color); font-weight: 300; opacity: 0.9; }
        .grid-layout { display: grid; grid-template-columns: 1fr 1fr; max-width: 1300px; margin: 0 auto 100px auto; gap: 0; box-shadow: 0 20px 50px rgba(0,0,0,0.4); }
        .grid-item { overflow: hidden; display: flex; }
        .text-content { padding: 80px; display: flex; flex-direction: column; justify-content: center; background: rgba(19, 30, 58, 0.4); backdrop-filter: blur(5px); }
        .text-content h2 { font-family: var(--font-title); color: var(--gold-color); font-size: 2.2rem; margin-bottom: 30px; position: relative; padding-bottom: 15px; }
        .text-content h2::after { content: ''; position: absolute; left: 0; bottom: 0; width: 60px; height: 2px; background: var(--gold-color); }
        .text-content p { font-size: 1.05rem; line-height: 1.9; color: #BDC6D3; text-align: justify; }
        .photo-container { position: relative; height: 500px; }
        .photo-container img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s ease; }
        .photo-container:hover img { transform: scale(1.08); }
        .menu-section { background-color: rgba(19, 30, 58, 0.6); padding: 100px 20px; text-align: center; border-top: 1px solid rgba(230, 140, 124, 0.2); backdrop-filter: blur(10px); }
        .menu-title { font-family: var(--font-title); font-size: 3rem; color: var(--gold-color); text-transform: uppercase; letter-spacing: 4px; }
        .subtitle { font-style: italic; color: #8FA3BF; margin-bottom: 60px; letter-spacing: 1px; }
        .menu-list { max-width: 700px; margin: 0 auto; text-align: left; }
        .menu-item { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 30px; font-family: var(--font-title); font-size: 1.4rem; }
        .ligne { flex-grow: 1; border-bottom: 1px dotted rgba(230, 140, 124, 0.3); margin: 0 15px; }
        .prix { color: var(--gold-color); font-weight: bold; }
        .search-wrapper { max-width: 500px; margin: 60px auto 0 auto; }
        .search-form { display: flex; background: rgba(19, 30, 58, 0.4); border: 1px solid rgba(230, 140, 124, 0.3); border-radius: 50px; padding: 5px 5px 5px 25px; }
        .search-input { flex-grow: 1; background: transparent; border: none; color: var(--text-color); outline: none; font-size: 1rem; }
        .search-submit { background: var(--gold-color); border: none; width: 45px; height: 45px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.3s; }
        .search-submit:hover { transform: scale(1.1); }
        .stats-container { display: flex; justify-content: center; gap: 100px; margin-top: 100px; }
        .number { display: block; font-family: var(--font-title); font-size: 4.5rem; color: var(--gold-color); line-height: 1; }
        .label { text-transform: uppercase; font-size: 0.8rem; letter-spacing: 3px; color: #8FA3BF; margin-top: 15px; display: block; }
        footer { text-align: center; padding: 60px; border-top: 1px solid rgba(230, 140, 124, 0.1); color: #5C6B85; }
        @media (max-width: 992px) {
            .grid-layout { grid-template-columns: 1fr; }
            .grid-item.photo-container { height: 400px; }
            .text-content { padding: 50px 30px; }
            .main-title { font-size: 3rem; }
            .stats-container { gap: 40px; flex-direction: column; }
        }
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
                    <?php if(isset($_SESSION['user'])): ?>
                        <?php if($_SESSION['user']['role'] === 'admin'): ?><li><a href="admin.php">Admin</a></li><?php endif; ?>
                        <?php if($_SESSION['user']['role'] === 'restaurateur'): ?><li><a href="commande.php">Commandes</a></li><?php endif; ?>
                        <?php if($_SESSION['user']['role'] === 'livreur'): ?><li><a href="livraison.php">Livraison</a></li><?php endif; ?>
                        <?php if($_SESSION['user']['role'] === 'client'): ?>
                            <li><a href="moncompte.php">Mon Compte</a></li>
                            <li><a href="notation.php">Notation</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php" style="color:var(--gold-color)">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="inscription.php">Inscription</a></li>
                        <li><a href="connection.php">Connexion</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <section class="intro-section">
        <h1 class="main-title">Bienvenue chez Les Arcades</h1>
        <p class="intro-text">
            Découvrez une cuisine orientale authentique et raffinée.
            Situé au cœur de la ville, notre établissement célèbre les saveurs
            Ancestrales de l'Orient dans un écrin de modernité.
        </p>
    </section>

    <section class="grid-layout">
        <div class="grid-item photo-container">
            <img src="couscous.jpg" alt="Le Couscous Signature">
        </div>
        <div class="grid-item text-content">
            <h2>L'Art du Couscous</h2>
            <p>
                Au cœur de notre établissement, la semoule est travaillée comme une véritable œuvre d'art.
                Inspirés par l'héritage de l'Atlas, nos chefs préparent chaque grain à la main,
                selon un rituel ancestral qui lui confère une légèreté incomparable.
                Accompagné de notre célèbre <strong>agneau de sept heures</strong>, de légumes oubliés
                et d'un bouillon infusé au safran pur, chaque bouchée est un voyage sensoriel
                unique entre tradition séculaire et gastronomie moderne.
            </p>
        </div>

        <div class="grid-item text-content">
            <h2>Une Immersion Sensorielle</h2>
            <p>
                Plus qu'un simple repas, <em>Les Arcades</em> vous propose une véritable évasion
                dans une oasis de sérénité au milieu du tumulte urbain. Sous les lumières tamisées
                de nos lanternes ciselées, laissez-vous transporter par les effluves de pain chaud
                et de menthe fraîche. Que ce soit pour un dîner intimiste ou une grande tablée
                familiale, notre salle offre un cadre raffiné où l'hospitalité orientale s'exprime
                dans chaque détail, faisant de votre passage un moment de partage hors du temps.
            </p>
        </div>
        <div class="grid-item photo-container">
            <img src="restaurant-gastronomique-kembs-02-1920x1280.jpg" alt="Ambiance salle Les Arcades">
        </div>
    </section>

    
    <!-- PLATS POPULAIRES Phase 4 -->
    <?php if (!empty($populaires)): ?>
    <section style="padding:70px 20px;background:rgba(19,30,58,0.4);border-top:1px solid rgba(230,140,124,0.2);">
        <div style="max-width:1200px;margin:0 auto;">
            <h2 style="font-family:'Playfair Display',serif;color:#E68C7C;font-size:2.5rem;text-align:center;margin-bottom:8px;">Les Préférés de Nos Clients</h2>
            <p style="text-align:center;color:#8FA3BF;font-style:italic;margin-bottom:40px;">Les plats qui font la réputation de la maison</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:20px;">
                <?php foreach ($populaires as $i => $p): ?>
                <div style="background:rgba(19,30,58,0.5);border:1px solid rgba(230,140,124,0.25);border-radius:6px;padding:25px;text-align:center;position:relative;transition:0.3s;">
                    <?php if ($i===0): ?><span style="position:absolute;top:10px;right:10px;background:#E68C7C;color:#060B19;padding:3px 8px;border-radius:10px;font-size:0.7rem;font-weight:bold;">⭐ N°1</span><?php endif; ?>
                    <h3 style="font-family:'Playfair Display',serif;color:#E68C7C;margin:0 0 8px;"><?= htmlspecialchars($p['nom']) ?></h3>
                    <p style="color:#8FA3BF;font-size:0.85rem;min-height:45px;"><?= htmlspecialchars($p['desc']) ?></p>
                    <p style="font-family:'Playfair Display',serif;color:#E68C7C;font-weight:bold;font-size:1.15rem;"><?= number_format($p['prix'],2) ?> €</p>
                    <p style="color:#8FA3BF;font-size:0.75rem;"><?= intval($p['nb_commandes']) ?> commandes</p>
                    <a href="menu.php" style="display:inline-block;margin-top:8px;border:1px solid #E68C7C;color:#E68C7C;padding:6px 14px;font-size:0.75rem;text-transform:uppercase;text-decoration:none;letter-spacing:1px;transition:0.3s;" onmouseover="this.style.background='#E68C7C';this.style.color='#060B19'" onmouseout="this.style.background='';this.style.color='#E68C7C'">Voir la carte</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- MENU ALEATOIRE Phase 4 -->
    <section style="text-align:center;padding:60px 20px;">
        <h2 style="font-family:'Playfair Display',serif;color:#E68C7C;font-size:2.2rem;margin-bottom:12px;">🎲 Le Menu Surprise du Chef</h2>
        <p style="color:#8FA3BF;margin-bottom:25px;">Indécis ? Laissez-nous tirer au sort une entrée, un plat et un dessert.</p>
        <button id="btn-random-menu" style="background:linear-gradient(135deg,#E68C7C,#f2a698);color:#060B19;border:none;padding:14px 32px;font-family:inherit;font-weight:bold;text-transform:uppercase;letter-spacing:2px;cursor:pointer;border-radius:30px;font-size:1rem;transition:0.3s;">🎲 Tirer mon menu surprise</button>
        <div id="random-result" style="max-width:680px;margin:0 auto;"></div>
    </section>

<section class="menu-section">
        <h2 class="menu-title">Spécialités</h2>
        <p class="subtitle">L'essence de notre cuisine</p>

        <div class="menu-list">
            <div class="menu-item"><span class="plat">Couscous du Chef</span><span class="ligne"></span><span class="prix">26€</span></div>
            <div class="menu-item"><span class="plat">Couscous Royal</span><span class="ligne"></span><span class="prix">24€</span></div>
            <div class="menu-item"><span class="plat">Couscous Berbère</span><span class="ligne"></span><span class="prix">22€</span></div>
            <div class="menu-item"><span class="plat">Couscous Merguez</span><span class="ligne"></span><span class="prix">25€</span></div>
            <div class="menu-item"><span class="plat">Couscous Légumes</span><span class="ligne"></span><span class="prix">15€</span></div>
        </div>

        <div class="search-wrapper">
            <form class="search-form" action="menu.php" method="GET">
                <input type="text" class="search-input" name="search" placeholder="Quel délice cherchez-vous ?" aria-label="Rechercher">
                <button type="submit" class="search-submit">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#060B19" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </button>
            </form>
        </div>

        <div class="stats-container">
            <div class="stat-box"><span class="number">30</span><span class="label">Ans d'expérience</span></div>
            <div class="stat-box"><span class="number">55</span><span class="label">Couverts d'exception</span></div>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <h3>Les Arcades</h3>
            <p>Ouvert tous les jours de 12h à 23h</p>
            <p class="contact-info">Service Réservation : 01 23 45 67 89</p>
            <p>5 Bd de la République 78400 Chatou</p>
        </div>
    </footer>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
    <script src="js/hamburger.js"></script>
    <script src="js/random-menu.js"></script>
</body>
</html>
