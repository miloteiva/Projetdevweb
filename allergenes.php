<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Allergènes - Les Arcades</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; --muted-blue: #8FA3BF; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; }
        header { padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); display: flex; justify-content: space-between; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); }
        .nav-link { color: var(--text-color); text-decoration: none; font-size: 0.85rem; }
        .container { max-width: 900px; margin: 50px auto; padding: 40px; background: rgba(19, 30, 58, 0.6); border: 1px solid rgba(230, 140, 124, 0.2); border-radius: 6px; backdrop-filter: blur(10px); text-align: center; }
        h1 { font-family: var(--font-title); color: var(--gold-color); margin-bottom: 15px; }
        p.intro { color: var(--muted-blue); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 15px; margin-top: 30px; text-align: left; }
        .item { background: rgba(255,255,255,0.04); padding: 15px; border-radius: 4px; border-left: 3px solid var(--gold-color); }
        .item .num { color: var(--gold-color); font-family: var(--font-title); font-size: 1.3rem; }
        .item .nom { font-size: 0.9rem; margin-top: 4px; }
        img { max-width: 100%; height: auto; border-radius: 6px; margin-top: 30px; }
    </style>
    <link rel="stylesheet" id="theme-css" href="css/theme-dark.css">
    <link rel="stylesheet" href="css/mobile.css">
</head>
<body>
    <header>
        <div class="logo">LES ARCADES</div>
        <a href="menu.php" class="nav-link">← Retour à la carte</a>
    </header>
    <main class="container">
        <h1>Les 14 allergènes alimentaires</h1>
        <p class="intro">Conformément à la réglementation européenne (règlement INCO 1169/2011), voici la liste des 14 allergènes à déclaration obligatoire.</p>

        <div class="grid">
            <div class="item"><span class="num">1</span><div class="nom">Gluten (céréales)</div></div>
            <div class="item"><span class="num">2</span><div class="nom">Crustacés</div></div>
            <div class="item"><span class="num">3</span><div class="nom">Œufs</div></div>
            <div class="item"><span class="num">4</span><div class="nom">Poissons</div></div>
            <div class="item"><span class="num">5</span><div class="nom">Arachides</div></div>
            <div class="item"><span class="num">6</span><div class="nom">Soja</div></div>
            <div class="item"><span class="num">7</span><div class="nom">Lait (lactose)</div></div>
            <div class="item"><span class="num">8</span><div class="nom">Fruits à coque</div></div>
            <div class="item"><span class="num">9</span><div class="nom">Céleri</div></div>
            <div class="item"><span class="num">10</span><div class="nom">Moutarde</div></div>
            <div class="item"><span class="num">11</span><div class="nom">Graines de sésame</div></div>
            <div class="item"><span class="num">12</span><div class="nom">Anhydride sulfureux & sulfites</div></div>
            <div class="item"><span class="num">13</span><div class="nom">Lupin</div></div>
            <div class="item"><span class="num">14</span><div class="nom">Mollusques</div></div>
        </div>

        <img src="14allergenes.png" alt="Pictogrammes des 14 allergènes" style="max-width:600px; margin-top:40px;">
    </main>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
    <script src="js/hamburger.js"></script>
</body>
</html>
