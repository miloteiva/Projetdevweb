<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    header("Location: connection.php");
    exit();
}

$data = json_decode(file_get_contents('data.json'), true);
$user = $_SESSION['user'];

// Récupérer SES livraisons en cours
$mesLivraisons = [];
foreach ($data['commandes'] ?? [] as $cmd) {
    if (isset($cmd['id_livreur']) && $cmd['id_livreur'] === $user['id']
        && $cmd['statut'] === 'En livraison') {
        // Récupérer infos client
        foreach ($data['users'] as $u) {
            if ($u['id'] === $cmd['id_client']) {
                $cmd['client_info'] = $u; break;
            }
        }
        $mesLivraisons[] = $cmd;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes livraisons</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; --muted-blue: #8FA3BF; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; }
        header { padding: 15px 20px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-title); font-size: 1.4rem; color: var(--gold-color); letter-spacing: 1px; }
        .nav-link { color: var(--text-color); text-decoration: none; font-size: 0.85rem; }
        .liv-container { max-width: 600px; margin: 30px auto; padding: 0 15px; }
        h1 { font-family: var(--font-title); color: var(--gold-color); font-size: 2rem; }
        .delivery-card { background: rgba(19, 30, 58, 0.6); border: 1px solid rgba(230, 140, 124, 0.3); border-radius: 8px; padding: 25px; margin-bottom: 20px; backdrop-filter: blur(10px); }
        .delivery-card h2 { font-family: var(--font-title); color: var(--gold-color); margin: 0 0 15px; font-size: 1.5rem; }
        .info-line { display: flex; gap: 12px; margin-bottom: 12px; align-items: flex-start; }
        .info-line .icon { width: 24px; flex-shrink: 0; font-size: 1.2rem; text-align: center; }
        .info-line .content { flex: 1; }
        .info-label { font-size: 0.7rem; text-transform: uppercase; color: var(--muted-blue); letter-spacing: 1px; }
        .info-value { font-size: 1rem; margin-top: 2px; }
        .articles-list { background: rgba(0,0,0,0.2); padding: 12px; border-radius: 4px; margin: 15px 0; font-size: 0.85rem; }
        .total-display { font-family: var(--font-title); color: var(--gold-color); font-size: 1.4rem; text-align: right; margin: 10px 0; }
        /* GROS boutons pour gros gants en hiver */
        .btn-deliver, .btn-abandon, .btn-map { display: block; width: 100%; padding: 18px; font-family: inherit; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; border: none; cursor: pointer; border-radius: 6px; margin-top: 10px; text-decoration: none; text-align: center; transition: 0.3s; min-height: 56px; }
        .btn-deliver { background: var(--gold-color); color: var(--bg-color); }
        .btn-deliver:hover { background: #f2a698; }
        .btn-deliver:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-abandon { background: transparent; border: 1px solid #ff6b6b; color: #ff6b6b; }
        .btn-abandon:hover { background: #ff6b6b; color: #fff; }
        .btn-map { background: transparent; border: 1px solid var(--gold-color); color: var(--gold-color); }
        .btn-map:hover { background: var(--gold-color); color: var(--bg-color); }
        .empty { text-align: center; padding: 60px 20px; color: var(--muted-blue); }
        @media (max-width: 480px) { .liv-container { padding: 0 10px; } .delivery-card { padding: 18px; } }
    </style>
    <link rel="stylesheet" id="theme-css" href="css/theme-dark.css">
</head>
<body>
    <header>
        <div class="logo">LES ARCADES</div>
        <a href="logout.php" class="nav-link">Déconnexion</a>
    </header>

    <main class="liv-container">
        <h1>Mes livraisons</h1>
        <p style="color: var(--muted-blue);">Bonjour <?= htmlspecialchars($user['prenom']) ?>, voici les commandes qui vous sont assignées</p>

        <?php if (empty($mesLivraisons)): ?>
            <div class="empty">
                <div style="font-size: 3rem;">📦</div>
                <p>Aucune livraison en cours.<br>Patientez, le restaurateur vous assignera bientôt.</p>
            </div>
        <?php else: ?>
            <?php foreach ($mesLivraisons as $cmd):
                $client = $cmd['client_info'] ?? [];
                $adresse = $client['adresse'] ?? 'Adresse manquante';
                $tel = $client['telephone'] ?? 'Non renseigné';
            ?>
            <div class="delivery-card">
                <h2>Commande #<?= $cmd['id'] ?></h2>

                <div class="info-line">
                    <span class="icon">👤</span>
                    <div class="content">
                        <div class="info-label">Client</div>
                        <div class="info-value"><?= htmlspecialchars($cmd['client']) ?></div>
                    </div>
                </div>
                <div class="info-line">
                    <span class="icon">📍</span>
                    <div class="content">
                        <div class="info-label">Adresse de livraison</div>
                        <div class="info-value"><?= htmlspecialchars($adresse) ?></div>
                    </div>
                </div>
                <div class="info-line">
                    <span class="icon">📞</span>
                    <div class="content">
                        <div class="info-label">Téléphone</div>
                        <div class="info-value"><a href="tel:<?= htmlspecialchars($tel) ?>" style="color: var(--gold-color);"><?= htmlspecialchars($tel) ?></a></div>
                    </div>
                </div>

                <div class="articles-list">
                    <strong>Articles :</strong><br>
                    <?php foreach ($cmd['articles'] as $a): ?>
                        • <?= $a['quantite'] ?>x <?= htmlspecialchars($a['nom']) ?><br>
                    <?php endforeach; ?>
                </div>
                <div class="total-display">Total : <?= number_format($cmd['total'], 2) ?>€</div>

                <a href="https://www.google.com/maps/dir/?api=1&destination=<?= urlencode($adresse) ?>" target="_blank" class="btn-map">🗺 Ouvrir dans Maps</a>
                <button class="btn-deliver" data-order-id="<?= $cmd['id'] ?>">✓ Valider la livraison</button>
                <button class="btn-abandon" data-order-id="<?= $cmd['id'] ?>">✗ Abandonner</button>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
    <script src="js/order-actions.js"></script>
</body>
</html>
