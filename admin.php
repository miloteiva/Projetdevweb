<?php
session_start();
// Vérification stricte du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: connection.php");
    exit();
}

$data = json_decode(file_get_contents('data.json'), true);
$users = $data['users'];
$nbUsers = count($users);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administration - Les Arcades</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        /* --- BASE --- */
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; line-height: 1.6; }
        header { background-color: transparent; padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); }
        a { text-decoration: none; color: inherit; transition: 0.3s; }
        ul { list-style: none; padding: 0; margin: 0; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); }
        .navbar ul { display: flex; gap: 30px; }
        .navbar a { font-size: 0.8rem; text-transform: uppercase; color: var(--text-color); }
        .navbar a:hover { color: var(--gold-color); }
        .auth-links { font-size: 0.75rem; color: #8FA3BF; }

        /* --- ADMIN.CSS --- */
        .admin-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .main-title-profile { font-family: var(--font-title); font-size: 3.5rem; color: var(--gold-color); margin-bottom: 40px; }
        .admin-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: rgba(19, 30, 58, 0.4); border: 1px solid rgba(230, 140, 124, 0.1); padding: 30px; text-align: center; border-radius: 4px; backdrop-filter: blur(5px); display: flex; flex-direction: column; justify-content: center; }
        .stat-number { display: block; font-family: var(--font-title); font-size: 2.5rem; color: var(--gold-color); margin-bottom: 10px; }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; color: #8FA3BF; letter-spacing: 1px; }
        .admin-card { background-color: rgba(19, 30, 58, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(230, 140, 124, 0.2); padding: 30px; border-radius: 4px; }
        .admin-header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid rgba(230, 140, 124, 0.2); padding-bottom: 20px; flex-wrap: wrap; gap: 20px; }
        .admin-card h2 { font-family: var(--font-title); color: var(--gold-color); margin: 0; }
        .admin-nav-group { display: flex; align-items: center; gap: 20px; }
        .table-container { overflow-x: auto; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { text-align: left; padding: 15px; color: var(--gold-color); border-bottom: 1px solid rgba(230, 140, 124, 0.3); font-size: 0.85rem; text-transform: uppercase; }
        .admin-table td { padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); color: #BDC6D3; }
        .action-link { background: transparent; border: none; cursor: pointer; font-size: 0.8rem; text-decoration: underline; color: var(--gold-color); transition: 0.3s; }
        .action-link:hover { opacity: 0.7; }
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
                </ul>
            </nav>
            <div class="auth-links">
                <a href="logout.php">Déconnexion (Admin)</a>
            </div>
        </div>
    </header>

    <main class="admin-container">
        <h1 class="main-title-profile">Page Administrateur</h1>

        <div class="admin-stats">
            <div class="stat-card">
                <span class="stat-number"><?= $nbUsers ?></span>
                <span class="stat-label">Utilisateurs inscrits</span>
            </div>
            <div class="stat-card">
                <span class="stat-number">0€</span>
                <span class="stat-label">CA du jour</span>
            </div>
        </div>

        <section class="admin-card full-width">
            <div class="admin-header-actions">
                <h2>Liste des Utilisateurs</h2>
            </div>

            <div class="table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom & Prénom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['nom'] . " " . $u['prenom']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><?= htmlspecialchars($u['role']) ?></td>
                            <td>
                                <button class="action-link edit">Détails</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
