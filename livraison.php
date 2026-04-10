<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    header("Location: connection.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Livraisons - Les Arcades</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        /* --- BASE --- */
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; }
        body.delivery-body { background: var(--bg-color); color: var(--text-color); font-family: var(--font-body); margin: 0; padding-bottom: 80px; }
        header { padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(230, 140, 124, 0.2); background: rgba(19, 30, 58, 0.9); position: sticky; top: 0; z-index: 100; }
        .logo { font-family: var(--font-title); font-size: 1.2rem; color: var(--gold-color); }
        .delivery-badge { font-size: 0.7rem; color: #8FA3BF; text-transform: uppercase; border: 1px solid #8FA3BF; padding: 3px 8px; border-radius: 20px; }
        a { text-decoration: none; color: inherit; }

        /* --- LIVRAISON.CSS --- */
        .delivery-container { padding: 40px 20px; max-width: 1200px; margin: 0 auto; }
        .page-title { font-family: var(--font-title); color: var(--gold-color); font-size: 2.5rem; margin-bottom: 40px; }
        .delivery-card { background: rgba(19, 30, 58, 0.4); border: 1px solid rgba(230, 140, 124, 0.2); border-radius: 4px; padding: 30px; margin-bottom: 25px; }
        .card-header { display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px; margin-bottom: 15px; }
        .order-id { color: var(--gold-color); font-weight: bold; }
        .order-time { font-size: 0.8rem; color: #8FA3BF; }
        .info-section { margin-bottom: 20px; }
        label { display: block; font-size: 0.65rem; text-transform: uppercase; color: #8FA3BF; letter-spacing: 1px; margin-bottom: 5px; }
        .client-name, .address { font-size: 1.1rem; font-weight: 500; margin-bottom: 10px; }
        .btn-contact, .btn-maps { display: block; text-align: center; padding: 12px; border-radius: 4px; font-size: 0.9rem; margin-top: 10px; }
        .btn-contact { background: transparent; border: 1px solid #8FA3BF; color: var(--text-color); }
        .btn-maps { background: #1B335F; border: 1px solid var(--gold-color); color: var(--gold-color); }
        .access-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; background: rgba(0,0,0,0.2); padding: 10px; border-radius: 4px; }
        .comment-box { font-style: italic; color: #BDC6D3; font-size: 0.9rem; border-left: 2px solid var(--gold-color); padding-left: 10px; }
        .btn-complete { width: 100%; padding: 15px; background: var(--gold-color); color: var(--bg-color); border: none; border-radius: 4px; font-weight: bold; text-transform: uppercase; margin-top: 20px; cursor: pointer; }
    </style>
</head>
<body class="delivery-body">
    <header>
        <div class="logo">LES ARCADES</div>
        <div style="display:flex; gap:15px; align-items:center;">
            <a href="logout.php" style="font-size:0.8rem; color:#8FA3BF;">Déconnexion</a>
            <div class="delivery-badge">Livreur Connecté</div>
        </div>
    </header>

    <main class="delivery-container">
        <h1 class="page-title">Mes Courses</h1>

        <article class="delivery-card">
            <div class="card-header">
                <span class="order-id">Commande #0001</span>
                <span class="order-time">Départ : 19:45</span>
            </div>

            <div class="info-section">
                <label>Client</label>
                <p class="client-name">Jean Dupont</p>
                <a href="tel:0600000000" class="btn-contact">📞 Appeler le client</a>
            </div>

            <div class="info-section">
                <label>Adresse de livraison</label>
                <p class="address">12 Bd de la République, Chatou</p>
                <a href="#" target="_blank" class="btn-maps">📍 Ouvrir dans Maps</a>
            </div>

            <div class="access-grid">
                <div class="access-item"><label>Interphone</label><p>1234</p></div>
                <div class="access-item"><label>Étage</label><p>3ème</p></div>
            </div>

            <div class="info-section">
                <label>Commentaire</label>
                <div class="comment-box">"Attention au chien dans la cour."</div>
            </div>

            <button class="btn-complete">Valider la livraison</button>
        </article>
    </main>
</body>
</html>
