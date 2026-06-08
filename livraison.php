<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    header("Location: connection.php");
    exit();
}

$data = json_decode(file_get_contents('data.json'), true);
$user = $_SESSION['user'];

// Récupérer SES livraisons en cours (assignées + statut 'En livraison')
$mesLivraisons = [];
// Récupérer aussi les commandes Prêtes qui pourraient lui être assignées prochainement (info)
$enAttenteAssignation = 0;

foreach ($data['commandes'] ?? [] as $cmd) {
    if (isset($cmd['id_livreur']) && (int)$cmd['id_livreur'] === (int)$user['id']
        && $cmd['statut'] === 'En livraison') {
        // Récupérer infos client
        foreach ($data['users'] as $u) {
            if ($u['id'] === $cmd['id_client']) {
                $cmd['client_info'] = $u;
                break;
            }
        }
        $mesLivraisons[] = $cmd;
    }
    // Compter les commandes prêtes (en attente d'assignation par le restaurateur)
    if ($cmd['statut'] === 'Prête' && ($cmd['type'] ?? '') === 'livraison') {
        $enAttenteAssignation++;
    }
}

// Trier par ordre croissant d'ID (les plus anciennes commandes en premier)
usort($mesLivraisons, fn($a, $b) => $a['id'] <=> $b['id']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(bin2hex(random_bytes(16))); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mes livraisons - Les Arcades</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; --muted-blue: #8FA3BF; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; }
        header { padding: 15px 20px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-title); font-size: 1.4rem; color: var(--gold-color); letter-spacing: 1px; }
        .nav-link { color: var(--text-color); text-decoration: none; font-size: 0.85rem; }
        .liv-container { max-width: 600px; margin: 30px auto; padding: 0 15px; }
        h1 { font-family: var(--font-title); color: var(--gold-color); font-size: 2rem; margin-bottom: 5px; }
        .info-bar { background: rgba(230, 140, 124, 0.08); border-left: 3px solid var(--gold-color); padding: 12px 15px; margin: 15px 0 25px; font-size: 0.85rem; color: var(--muted-blue); border-radius: 4px; }
        .delivery-card { background: rgba(19, 30, 58, 0.6); border: 1px solid rgba(230, 140, 124, 0.3); border-radius: 8px; padding: 25px; margin-bottom: 20px; backdrop-filter: blur(10px); box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .delivery-card h2 { font-family: var(--font-title); color: var(--gold-color); margin: 0 0 15px; font-size: 1.5rem; display: flex; justify-content: space-between; align-items: baseline; }
        .delivery-card h2 .heure-tag { font-size: 0.75rem; color: var(--muted-blue); font-family: var(--font-body); font-weight: normal; }
        .info-line { display: flex; gap: 12px; margin-bottom: 12px; align-items: flex-start; }
        .info-line .icon { width: 24px; flex-shrink: 0; font-size: 1.2rem; text-align: center; }
        .info-line .content { flex: 1; min-width: 0; }
        .info-label { font-size: 0.7rem; text-transform: uppercase; color: var(--muted-blue); letter-spacing: 1px; }
        .info-value { font-size: 1rem; margin-top: 2px; word-break: break-word; }
        .info-value a { color: var(--gold-color); text-decoration: none; }
        .articles-list { background: rgba(0,0,0,0.2); padding: 12px; border-radius: 4px; margin: 15px 0; font-size: 0.85rem; line-height: 1.7; }
        .articles-list strong { color: var(--gold-color); }
        .total-display { font-family: var(--font-title); color: var(--gold-color); font-size: 1.4rem; text-align: right; margin: 10px 0; }
        /* GROS boutons pour gros gants en hiver - exigence du cahier des charges */
        .btn-deliver, .btn-abandon, .btn-map { display: block; width: 100%; padding: 18px; font-family: inherit; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; border: none; cursor: pointer; border-radius: 6px; margin-top: 10px; text-decoration: none; text-align: center; transition: 0.3s; min-height: 56px; box-sizing: border-box; }
        .btn-deliver { background: var(--gold-color); color: var(--bg-color); }
        .btn-deliver:hover { background: #f2a698; }
        .btn-deliver:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-abandon { background: transparent; border: 1px solid #ff6b6b; color: #ff6b6b; }
        .btn-abandon:hover { background: #ff6b6b; color: #fff; }
        .btn-map { background: transparent; border: 2px solid var(--gold-color); color: var(--gold-color); }
        .btn-map:hover { background: var(--gold-color); color: var(--bg-color); }
        .empty { text-align: center; padding: 60px 20px; color: var(--muted-blue); background: rgba(19, 30, 58, 0.4); border-radius: 8px; border: 1px dashed rgba(230, 140, 124, 0.3); }
        .empty .emoji { font-size: 3rem; display: block; margin-bottom: 15px; }
        .badge-livraison { display: inline-block; background: rgba(76, 175, 80, 0.3); color: #A5D6A7; padding: 3px 10px; border-radius: 12px; font-size: 0.7rem; margin-left: 10px; vertical-align: middle; }
        @media (max-width: 480px) {
            .liv-container { padding: 0 10px; }
            .delivery-card { padding: 18px; }
            h1 { font-size: 1.7rem; }
        }
    </style>
    <link rel="stylesheet" id="theme-css" href="css/theme-dark.css">
    <link rel="stylesheet" href="css/mobile.css">
</head>
<body>
    <header>
        <div class="logo">LES ARCADES</div>
        <div>
            <span style="color: var(--muted-blue); font-size: 0.8rem; margin-right: 12px;">🚴 <?= htmlspecialchars($user['prenom']) ?></span>
            <a href="logout.php" class="nav-link">Déconnexion</a>
        </div>
    </header>

    <main class="liv-container">
        <h1>Mes livraisons</h1>
        <p style="color: var(--muted-blue); margin: 0;">Bonjour <?= htmlspecialchars($user['prenom']) ?>, voici les commandes qui vous sont assignées.</p>

        <div class="info-bar">
            <strong style="color: var(--gold-color);"><?= count($mesLivraisons) ?> livraison(s) en cours</strong>
            <?php if ($enAttenteAssignation > 0): ?>
                · <?= $enAttenteAssignation ?> commande(s) prête(s) en attente d'assignation par le restaurateur
            <?php endif; ?>
        </div>

        <?php if (empty($mesLivraisons)): ?>
            <div class="empty">
                <span class="emoji">📦</span>
                <p style="margin: 0 0 10px;"><strong>Aucune livraison en cours.</strong></p>
                <p style="margin: 0; font-size: 0.9rem;">Patientez, le restaurateur vous assignera bientôt une commande.</p>
                <?php if ($enAttenteAssignation > 0): ?>
                    <p style="margin-top: 15px; color: var(--gold-color); font-size: 0.85rem;">
                        💡 Il y a <?= $enAttenteAssignation ?> commande(s) prête(s) à être assignée(s).
                    </p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($mesLivraisons as $cmd):
                $client = $cmd['client_info'] ?? [];
                $adresse = $client['adresse'] ?? 'Adresse manquante';
                $tel = $client['telephone'] ?? 'Non renseigné';
                // Préparation de l'URL Maps - utiliser l'adresse en clair, urlencode l'URL
                $mapsUrl = 'https://www.google.com/maps/dir/?api=1&destination=' . urlencode($adresse);
                $wazeUrl = 'https://waze.com/ul?q=' . urlencode($adresse);
            ?>
            <div class="delivery-card">
                <h2>
                    Commande #<?= $cmd['id'] ?>
                    <span class="heure-tag">Reçue à <?= htmlspecialchars($cmd['heure'] ?? '--:--') ?></span>
                </h2>

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
                        <div class="info-value"><a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $tel)) ?>"><?= htmlspecialchars($tel) ?></a></div>
                    </div>
                </div>

                <div class="articles-list">
                    <strong>📋 Articles à livrer :</strong><br>
                    <?php foreach ($cmd['articles'] as $a): ?>
                        • <?= $a['quantite'] ?>x <?= htmlspecialchars($a['nom']) ?><br>
                    <?php endforeach; ?>
                </div>
                <div class="total-display">Total : <?= number_format($cmd['total'], 2) ?>€</div>

                <!-- Bouton Maps - ouvre dans un nouvel onglet avec rel="noopener" pour sécurité -->
                <a href="<?= htmlspecialchars($mapsUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-map">
                    🗺 Ouvrir l'itinéraire dans Google Maps
                </a>
                <a href="<?= htmlspecialchars($wazeUrl) ?>" target="_blank" rel="noopener noreferrer" class="btn-map" style="background: rgba(0, 120, 200, 0.1); border-color: #4FA8E8; color: #4FA8E8; margin-top: 8px;">
                    🚗 Ouvrir dans Waze
                </a>

                <button type="button" class="btn-deliver" data-order-id="<?= $cmd['id'] ?>">
                    ✓ Valider la livraison
                </button>
                <button type="button" class="btn-abandon" data-order-id="<?= $cmd['id'] ?>">
                    ✗ Abandonner (adresse introuvable)
                </button>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
    <script src="js/hamburger.js"></script>
    <script src="js/order-actions.js"></script>
</body>
</html>
