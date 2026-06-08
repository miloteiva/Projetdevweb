<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: connection.php");
    exit();
}

$data = json_decode(file_get_contents('data.json'), true);
$users = $data['users'];
$commandes = $data['commandes'] ?? [];
$logs = array_reverse($data['logs'] ?? []); // Plus récents en premier
$logs = array_slice($logs, 0, 50); // On affiche les 50 derniers

// Statistiques
$nbClients = 0; $nbBloques = 0;
foreach ($users as $u) {
    if ($u['role'] === 'client') $nbClients++;
    if (!empty($u['bloque'])) $nbBloques++;
}

// CA du jour
$caJour = 0;
$today = date('d/m/Y');
foreach ($commandes as $cmd) {
    if (in_array($cmd['statut'], ['Payée', 'En préparation', 'Prête', 'En livraison', 'Livrée'])
        && $cmd['date'] === $today) {
        $caJour += $cmd['total'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administration - Les Arcades</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; --muted-blue: #8FA3BF; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; }
        header { padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); letter-spacing: 2px; }
        .nav-link { color: var(--text-color); text-decoration: none; font-size: 0.85rem; text-transform: uppercase; margin-left: 25px; }
        .nav-link:hover { color: var(--gold-color); }
        .admin-container { max-width: 1200px; margin: 50px auto; padding: 0 20px; }
        h1 { font-family: var(--font-title); color: var(--gold-color); font-size: 3rem; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-box { background: rgba(19, 30, 58, 0.5); border: 1px solid rgba(230, 140, 124, 0.2); padding: 25px; border-radius: 6px; text-align: center; }
        .stat-box .value { font-family: var(--font-title); color: var(--gold-color); font-size: 2.5rem; }
        .stat-box .label { color: var(--muted-blue); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; background: rgba(19, 30, 58, 0.5); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid rgba(230, 140, 124, 0.1); }
        th { background: rgba(230, 140, 124, 0.1); color: var(--gold-color); font-family: var(--font-title); font-weight: normal; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
        tr:hover { background: rgba(255,255,255,0.02); }
        .role-badge { padding: 3px 8px; border-radius: 10px; font-size: 0.7rem; }
        .role-admin { background: rgba(230, 140, 124, 0.3); color: var(--gold-color); }
        .role-client { background: rgba(143, 163, 191, 0.2); color: var(--muted-blue); }
        .role-restaurateur { background: rgba(168, 119, 217, 0.3); color: #D5B8F0; }
        .role-livreur { background: rgba(76, 175, 80, 0.3); color: #A5D6A7; }
        .btn-block-user { background: transparent; border: 1px solid var(--gold-color); color: var(--gold-color); padding: 6px 14px; font-size: 0.75rem; cursor: pointer; transition: 0.3s; font-family: inherit; }
        .btn-block-user:hover { background: var(--gold-color); color: var(--bg-color); }
        .btn-block-user.blocked-state { background: #ff6b6b; color: #fff; border-color: #ff6b6b; }
        h2 { font-family: var(--font-title); color: var(--gold-color); margin-top: 50px; }
    </style>
    <link rel="stylesheet" id="theme-css" href="css/theme-dark.css">
    <link rel="stylesheet" href="css/mobile.css">
</head>
<body>
    <header>
        <div class="logo">LES ARCADES</div>
        <nav>
            <a href="restaurant.php" class="nav-link">Accueil</a>
            <a href="admin.php" class="nav-link" style="color:var(--gold-color)">Admin</a>
            <a href="logout.php" class="nav-link">Déconnexion</a>
        </nav>
    </header>

    <main class="admin-container">
        <h1>Administration</h1>
        <p style="color: var(--muted-blue);">Tableau de bord - Bienvenue <?= htmlspecialchars($_SESSION['user']['prenom']) ?></p>

        <section class="stats">
            <div class="stat-box"><div class="value"><?= count($users) ?></div><div class="label">Utilisateurs</div></div>
            <div class="stat-box"><div class="value"><?= $nbClients ?></div><div class="label">Clients</div></div>
            <div class="stat-box"><div class="value"><?= $nbBloques ?></div><div class="label">Comptes bloqués</div></div>
            <div class="stat-box"><div class="value"><?= number_format($caJour, 0) ?>€</div><div class="label">CA du jour</div></div>
        </section>

        <h2>Gestion des utilisateurs</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Nom complet</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr style="<?= !empty($u['bloque']) ? 'opacity:0.5;' : '' ?>">
                    <td>#<?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="role-badge role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td class="user-status"><?= !empty($u['bloque']) ? '🚫 Bloqué' : '✓ Actif' ?></td>
                    <td>
                        <?php if ($u['id'] !== $_SESSION['user']['id']): ?>
                            <button class="btn-block-user <?= !empty($u['bloque']) ? 'blocked-state' : '' ?>"
                                    data-user-id="<?= $u['id'] ?>"
                                    data-blocked="<?= !empty($u['bloque']) ? '1' : '0' ?>">
                                <?= !empty($u['bloque']) ? 'Débloquer' : 'Bloquer' ?>
                            </button>
                        <?php else: ?>
                            <em style="color: var(--muted-blue); font-size: 0.75rem;">(vous)</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Dernières commandes</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Client</th><th>Total</th><th>Statut</th><th>Date</th></tr>
            </thead>
            <tbody>
                <?php foreach (array_reverse($commandes) as $cmd): ?>
                <tr>
                    <td>#<?= $cmd['id'] ?></td>
                    <td><?= htmlspecialchars($cmd['client']) ?></td>
                    <td><?= number_format($cmd['total'], 2) ?>€</td>
                    <td><?= htmlspecialchars($cmd['statut']) ?></td>
                    <td><?= htmlspecialchars($cmd['date']) ?> <?= htmlspecialchars($cmd['heure']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($commandes)): ?><tr><td colspan="5" style="text-align:center; color:var(--muted-blue);">Aucune commande</td></tr><?php endif; ?>
            </tbody>
        </table>
    
        <h2>Logs d'incidents (50 derniers)</h2>
        <table>
            <thead>
                <tr><th>Date</th><th>Type</th><th>Message</th><th>User</th><th>IP</th></tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--muted-blue);">Aucun log enregistré</td></tr>
                <?php else: foreach ($logs as $log): ?>
                <tr>
                    <td style="font-size:0.8rem;white-space:nowrap;"><?= htmlspecialchars($log['date']) ?></td>
                    <td>
                        <?php $lt = htmlspecialchars($log['type']); ?>
                        <span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:0.7rem;font-weight:600;
                            background:<?= in_array($log['type'],['connexion_echec','paiement_echec'])?'rgba(255,107,107,0.2)':( in_array($log['type'],['connexion_ok','order_delivered'])?'rgba(107,207,127,0.2)':'rgba(230,140,124,0.2)') ?>;
                            color:<?= in_array($log['type'],['connexion_echec','paiement_echec'])?'#ff6b6b':( in_array($log['type'],['connexion_ok','order_delivered'])?'#6bcf7f':'#E68C7C') ?>;">
                            <?= $lt ?>
                        </span>
                    </td>
                    <td style="font-size:0.82rem;color:var(--muted-blue);"><?= htmlspecialchars($log['message']) ?></td>
                    <td style="font-size:0.8rem;"><?= $log['user_id'] ? '#'.$log['user_id'] : '-' ?></td>
                    <td style="font-size:0.75rem;color:var(--muted-blue);"><?= htmlspecialchars($log['ip']) ?></td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>

    </main>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
    <script src="js/hamburger.js"></script>
    <script src="js/admin-actions.js"></script>
</body>
</html>
