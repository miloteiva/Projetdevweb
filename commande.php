<?php
session_start();
if (!isset($_SESSION['user']) ||
    !in_array($_SESSION['user']['role'], ['restaurateur', 'admin'])) {
    header("Location: connection.php");
    exit();
}

$data = json_decode(file_get_contents('data.json'), true);
$commandes = $data['commandes'] ?? [];

// Livreurs disponibles
$livreurs = array_filter($data['users'], function($u) { return $u['role'] === 'livreur' && empty($u['bloque']); });

// Grouper par statut
function commandesParStatut($commandes, $statut) {
    return array_filter($commandes, fn($c) => $c['statut'] === $statut);
}
$payees      = commandesParStatut($commandes, 'Payée');
$preparation = commandesParStatut($commandes, 'En préparation');
$pretes      = commandesParStatut($commandes, 'Prête');
$enLivraison = commandesParStatut($commandes, 'En livraison');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tableau de bord - Cuisine</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; --muted-blue: #8FA3BF; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; }
        header { padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); letter-spacing: 2px; }
        .nav-link { color: var(--text-color); text-decoration: none; font-size: 0.85rem; text-transform: uppercase; margin-left: 25px; }
        .nav-link:hover { color: var(--gold-color); }
        .dash-container { max-width: 1400px; margin: 40px auto; padding: 0 20px; }
        h1 { font-family: var(--font-title); color: var(--gold-color); font-size: 2.8rem; margin-bottom: 5px; }
        .kanban { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-top: 30px; }
        .column { background: rgba(19, 30, 58, 0.4); border: 1px solid rgba(230, 140, 124, 0.2); border-radius: 6px; padding: 20px; }
        .column h2 { font-family: var(--font-title); color: var(--gold-color); font-size: 1.3rem; margin: 0 0 15px; padding-bottom: 10px; border-bottom: 1px solid rgba(230, 140, 124, 0.2); display: flex; justify-content: space-between; }
        .column h2 .count { background: var(--gold-color); color: var(--bg-color); padding: 2px 10px; border-radius: 12px; font-size: 0.8rem; }
        .cmd-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(230, 140, 124, 0.15); padding: 15px; border-radius: 4px; margin-bottom: 12px; }
        .cmd-card h3 { margin: 0 0 8px; color: var(--gold-color); font-size: 1rem; }
        .cmd-card p { margin: 4px 0; color: var(--muted-blue); font-size: 0.8rem; }
        .cmd-card .articles { font-size: 0.8rem; color: var(--text-color); margin-top: 8px; padding-top: 8px; border-top: 1px dashed rgba(230, 140, 124, 0.2); }
        .btn-status-change, .btn-assign-livreur { background: var(--gold-color); color: var(--bg-color); border: none; padding: 8px 14px; font-family: inherit; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; cursor: pointer; margin-top: 10px; transition: 0.3s; width: 100%; }
        .btn-status-change:hover, .btn-assign-livreur:hover { background: #f2a698; }
        select { width: 100%; padding: 6px; margin-top: 8px; background: rgba(6, 11, 25, 0.5); border: 1px solid rgba(230, 140, 124, 0.3); color: var(--text-color); border-radius: 3px; font-family: inherit; font-size: 0.8rem; }
        .empty-col { text-align: center; color: var(--muted-blue); font-size: 0.85rem; padding: 30px 0; }
    </style>
    <link rel="stylesheet" id="theme-css" href="css/theme-dark.css">
    <link rel="stylesheet" href="css/mobile.css">
</head>
<body>
    <header>
        <div class="logo">LES ARCADES</div>
        <nav>
            <a href="commande.php" class="nav-link" style="color:var(--gold-color)">Cuisine</a>
            <a href="configurer_menu.php" class="nav-link">Menus</a>
            <a href="logout.php" class="nav-link">Déconnexion</a>
        </nav>
    </header>

    <main class="dash-container">
        <h1>Cuisine en direct</h1>
        <p style="color: var(--muted-blue);">Gérer les commandes</p>

        <div class="kanban">
            <!-- COLONNE 1 : Payées (à démarrer en préparation) -->
            <div class="column">
                <h2>À démarrer <span class="count"><?= count($payees) ?></span></h2>
                <?php if (empty($payees)): ?>
                    <p class="empty-col">Aucune nouvelle commande</p>
                <?php else: foreach ($payees as $c): ?>
                    <div class="cmd-card">
                        <h3>#<?= $c['id'] ?> · <?= number_format($c['total'], 2) ?>€</h3>
                        <p><?= htmlspecialchars($c['client']) ?> · <?= htmlspecialchars($c['type']) ?></p>
                        <p><?= htmlspecialchars($c['date']) ?> à <?= htmlspecialchars($c['heure']) ?></p>
                        <?php if (isset($c['preparation_immediate']) && !$c['preparation_immediate'] && !empty($c['date_prevue'])): ?>
                            <p style="background: rgba(230, 140, 124, 0.15); color: var(--gold-color); padding: 6px 10px; border-radius: 3px; font-size: 0.78rem; margin: 6px 0;">
                                🕒 À préparer pour le <?= htmlspecialchars($c['date_prevue']) ?> à <?= htmlspecialchars($c['heure_prevue']) ?>
                            </p>
                        <?php endif; ?>
                        <div class="articles">
                            <?php foreach ($c['articles'] as $a): ?>• <?= $a['quantite'] ?>x <?= htmlspecialchars($a['nom']) ?><br><?php endforeach; ?>
                        </div>
                        <button class="btn-status-change" data-order-id="<?= $c['id'] ?>" data-status="En préparation">▶ Démarrer la préparation</button>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <!-- COLONNE 2 : En préparation -->
            <div class="column">
                <h2>En préparation <span class="count"><?= count($preparation) ?></span></h2>
                <?php if (empty($preparation)): ?>
                    <p class="empty-col">Aucune en cuisine</p>
                <?php else: foreach ($preparation as $c): ?>
                    <div class="cmd-card">
                        <h3>#<?= $c['id'] ?> · <?= number_format($c['total'], 2) ?>€</h3>
                        <p><?= htmlspecialchars($c['client']) ?> · <?= htmlspecialchars($c['type']) ?></p>
                        <div class="articles">
                            <?php foreach ($c['articles'] as $a): ?>• <?= $a['quantite'] ?>x <?= htmlspecialchars($a['nom']) ?><br><?php endforeach; ?>
                        </div>
                        <button class="btn-status-change" data-order-id="<?= $c['id'] ?>" data-status="Prête">✓ Marquer prête</button>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <!-- COLONNE 3 : Prêtes (assigner livreur) -->
            <div class="column">
                <h2>Prêtes <span class="count"><?= count($pretes) ?></span></h2>
                <?php if (empty($pretes)): ?>
                    <p class="empty-col">Aucune prête</p>
                <?php else: foreach ($pretes as $c): ?>
                    <div class="cmd-card">
                        <h3>#<?= $c['id'] ?> · <?= number_format($c['total'], 2) ?>€</h3>
                        <p><?= htmlspecialchars($c['client']) ?></p>
                        <?php if ($c['type'] === 'livraison'): ?>
                            <select data-order-id="<?= $c['id'] ?>">
                                <option value="">Sélectionner un livreur</option>
                                <?php foreach ($livreurs as $l): ?>
                                    <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['prenom'] . ' ' . $l['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn-assign-livreur" data-order-id="<?= $c['id'] ?>">📦 Assigner & livrer</button>
                        <?php else: ?>
                            <p style="color: var(--gold-color); font-size: 0.85rem; margin-top: 10px;">À emporter / sur place</p>
                            <button class="btn-status-change" data-order-id="<?= $c['id'] ?>" data-status="Livrée">✓ Marquer remise</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <!-- COLONNE 4 : En livraison -->
            <div class="column">
                <h2>En livraison <span class="count"><?= count($enLivraison) ?></span></h2>
                <?php if (empty($enLivraison)): ?>
                    <p class="empty-col">Aucune en route</p>
                <?php else: foreach ($enLivraison as $c):
                    $livreurNom = '?';
                    foreach ($data['users'] as $u) {
                        if (isset($c['id_livreur']) && $u['id'] === $c['id_livreur']) {
                            $livreurNom = $u['prenom'] . ' ' . $u['nom']; break;
                        }
                    }
                ?>
                    <div class="cmd-card">
                        <h3>#<?= $c['id'] ?> · <?= number_format($c['total'], 2) ?>€</h3>
                        <p><?= htmlspecialchars($c['client']) ?></p>
                        <p>🚴 Livreur : <?= htmlspecialchars($livreurNom) ?></p>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </main>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
    <script src="js/hamburger.js"></script>
    <script src="js/order-actions.js"></script>
</body>
</html>
