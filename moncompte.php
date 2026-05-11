<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    header("Location: connection.php");
    exit();
}

$user = $_SESSION['user'];
$data = json_decode(file_get_contents('data.json'), true);

// Récupérer le user à jour (au cas où il a été modifié)
foreach ($data['users'] as $u) {
    if ($u['id'] === $user['id']) { $user = $u; $_SESSION['user'] = $u; break; }
}

// Récupérer les commandes du client
$mesCommandes = [];
if (isset($data['commandes'])) {
    foreach ($data['commandes'] as $cmd) {
        if ($cmd['id_client'] === $user['id']) {
            $mesCommandes[] = $cmd;
        }
    }
}
// Plus récentes en premier
$mesCommandes = array_reverse($mesCommandes);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon Compte - Les Arcades</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; --muted-blue: #8FA3BF; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; }
        header { padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); letter-spacing: 2px; }
        .nav-link { color: var(--text-color); text-decoration: none; font-size: 0.85rem; text-transform: uppercase; margin-left: 25px; }
        .nav-link:hover { color: var(--gold-color); }
        .compte-container { max-width: 1100px; margin: 60px auto; padding: 0 20px; }
        h1 { font-family: var(--font-title); color: var(--gold-color); font-size: 3rem; margin-bottom: 10px; }
        .subtitle { color: var(--muted-blue); margin-bottom: 40px; }
        .section { background: rgba(19, 30, 58, 0.5); border: 1px solid rgba(230, 140, 124, 0.2); padding: 35px; border-radius: 6px; margin-bottom: 30px; backdrop-filter: blur(10px); }
        .section h2 { font-family: var(--font-title); color: var(--gold-color); font-size: 1.7rem; margin: 0 0 25px; padding-bottom: 12px; border-bottom: 1px solid rgba(230, 140, 124, 0.2); }
        .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
        .info-block label { display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--muted-blue); letter-spacing: 1px; margin-bottom: 8px; }
        .info-block .editable-field { font-size: 1.05rem; padding: 10px; background: rgba(255,255,255,0.03); border: 1px solid transparent; border-radius: 4px; min-height: 22px; }
        .btn-action { background: transparent; border: 1px solid var(--gold-color); color: var(--gold-color); padding: 11px 22px; font-family: inherit; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; transition: 0.3s; }
        .btn-action:hover { background: var(--gold-color); color: var(--bg-color); }
        .btn-action.saving { background: var(--gold-color); color: var(--bg-color); }
        .fidelity-box { display: flex; justify-content: space-between; align-items: center; padding: 20px; background: rgba(230, 140, 124, 0.1); border-left: 3px solid var(--gold-color); margin-top: 20px; }
        .fidelity-points { font-family: var(--font-title); color: var(--gold-color); font-size: 2.5rem; }
        .commande-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(230, 140, 124, 0.15); padding: 20px; border-radius: 4px; margin-bottom: 15px; display: grid; grid-template-columns: 1fr auto; gap: 15px; align-items: center; }
        .commande-card .cmd-info h3 { margin: 0 0 8px; color: var(--gold-color); font-family: var(--font-title); }
        .commande-card .cmd-info p { margin: 3px 0; color: var(--muted-blue); font-size: 0.85rem; }
        .commande-actions { display: flex; flex-direction: column; gap: 8px; }
        .statut-badge { display: inline-block; padding: 4px 10px; border-radius: 15px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; }
        .statut-Payée { background: rgba(74, 144, 226, 0.3); color: #87B5E8; }
        .statut-En.préparation { background: rgba(255, 165, 0, 0.3); color: #FFCC80; }
        .statut-Prête { background: rgba(168, 119, 217, 0.3); color: #D5B8F0; }
        .statut-En.livraison { background: rgba(76, 175, 80, 0.3); color: #A5D6A7; }
        .statut-Livrée { background: rgba(107, 207, 127, 0.3); color: #A5E5B5; }
        .statut-Abandonnée { background: rgba(244, 67, 54, 0.3); color: #EF9A9A; }
        .btn-small { padding: 6px 14px; font-size: 0.75rem; text-decoration: none; display: inline-block; }
    </style>
    <link rel="stylesheet" id="theme-css" href="css/theme-dark.css">
</head>
<body>
    <header>
        <div class="logo">LES ARCADES</div>
        <nav>
            <a href="restaurant.php" class="nav-link">Accueil</a>
            <a href="menu.php" class="nav-link">La Carte</a>
            <a href="moncompte.php" class="nav-link" style="color:var(--gold-color)">Mon Compte</a>
            <a href="logout.php" class="nav-link">Déconnexion</a>
        </nav>
    </header>

    <main class="compte-container">
        <h1>Mon Compte</h1>
        <p class="subtitle">Bonjour <?= htmlspecialchars($user['prenom']) ?>, ravis de vous revoir.</p>

        <!-- INFORMATIONS PERSONNELLES (Phase 3 : édition en AJAX) -->
        <section class="section">
            <h2>Mes informations</h2>
            <div class="info-grid">
                <div class="info-block">
                    <label>Nom</label>
                    <div class="editable-field" data-field="nom"><?= htmlspecialchars($user['nom']) ?></div>
                </div>
                <div class="info-block">
                    <label>Prénom</label>
                    <div class="editable-field" data-field="prenom"><?= htmlspecialchars($user['prenom']) ?></div>
                </div>
                <div class="info-block">
                    <label>Email</label>
                    <div class="editable-field" data-field="email"><?= htmlspecialchars($user['email']) ?></div>
                </div>
                <div class="info-block">
                    <label>Téléphone</label>
                    <div class="editable-field" data-field="telephone"><?= htmlspecialchars($user['telephone'] ?? 'Non renseigné') ?></div>
                </div>
                <div class="info-block" style="grid-column: 1 / -1;">
                    <label>Adresse</label>
                    <div class="editable-field" data-field="adresse"><?= htmlspecialchars($user['adresse'] ?? 'Non renseignée') ?></div>
                </div>
            </div>
            <div style="margin-top: 25px;">
                <button id="btn-edit-profile" class="btn-action">Modifier mon profil</button>
            </div>

            <div class="fidelity-box">
                <div>
                    <strong>Programme fidélité</strong>
                    <p style="margin: 5px 0 0; color: var(--muted-blue); font-size: 0.85rem;">Cumulez des points à chaque commande livrée</p>
                </div>
                <div class="fidelity-points"><?= intval($user['points_fidelite'] ?? 0) ?> pts</div>
            </div>
        </section>

        <!-- HISTORIQUE DES COMMANDES (réel depuis data.json) -->
        <section class="section">
            <h2>Mes commandes</h2>
            <?php if (empty($mesCommandes)): ?>
                <p style="color: var(--muted-blue);">Aucune commande pour le moment. <a href="menu.php" style="color: var(--gold-color);">Découvrez notre carte</a></p>
            <?php else: ?>
                <?php foreach ($mesCommandes as $cmd):
                    $articles_str = [];
                    foreach ($cmd['articles'] as $a) {
                        $articles_str[] = $a['quantite'] . 'x ' . $a['nom'];
                    }
                ?>
                <div class="commande-card">
                    <div class="cmd-info">
                        <h3>Commande #<?= $cmd['id'] ?> · <?= number_format($cmd['total'], 2) ?>€</h3>
                        <p><?= htmlspecialchars(implode(', ', $articles_str)) ?></p>
                        <p><?= htmlspecialchars($cmd['date']) ?> à <?= htmlspecialchars($cmd['heure']) ?> · <?= htmlspecialchars($cmd['type']) ?></p>
                        <span class="statut-badge statut-<?= str_replace(' ', '.', $cmd['statut']) ?>"><?= $cmd['statut'] ?></span>
                    </div>
                    <div class="commande-actions">
                        <?php if ($cmd['statut'] === 'Payée'): ?>
                            <a href="modifier_commande.php?id=<?= $cmd['id'] ?>" class="btn-action btn-small">Modifier</a>
                        <?php endif; ?>
                        <?php if ($cmd['statut'] === 'Livrée' && empty($cmd['note']) && $cmd['type'] === 'livraison'): ?>
                            <a href="notation.php?commande=<?= $cmd['id'] ?>" class="btn-action btn-small">Noter</a>
                        <?php elseif (!empty($cmd['note'])): ?>
                            <span style="color: var(--muted-blue); font-size: 0.75rem;">★ Notée</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
    <script src="js/validation.js"></script>
    <script src="js/profile-edit.js"></script>
</body>
</html>
