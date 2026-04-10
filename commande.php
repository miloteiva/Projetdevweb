<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'restaurateur') {
    header("Location: connection.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion des Commandes - Les Arcades</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        /* --- BASE --- */
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; line-height: 1.6; }
        header { background-color: transparent; padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); }
        .top-bar { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); }
        .navbar ul { display: flex; gap: 30px; list-style: none; padding: 0;}
        .navbar a { text-decoration: none; font-size: 0.8rem; text-transform: uppercase; color: var(--text-color); }
        .auth-links a { color: #8FA3BF; text-decoration: none;}

        /* --- ADMIN + COMMANDE.CSS --- */
        .admin-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .admin-header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .main-title-profile { font-family: var(--font-title); font-size: 3.5rem; color: var(--gold-color); }
        .admin-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: rgba(19, 30, 58, 0.4); border: 1px solid rgba(230, 140, 124, 0.1); padding: 30px; text-align: center; border-radius: 4px; }
        .stat-number { display: block; font-family: var(--font-title); font-size: 2.5rem; color: var(--gold-color); margin-bottom: 10px; }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; color: #8FA3BF; }
        .admin-card { background-color: rgba(19, 30, 58, 0.6); border: 1px solid rgba(230, 140, 124, 0.2); padding: 30px; border-radius: 4px; }
        .section-header { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
        .status-indicator { width: 12px; height: 12px; border-radius: 50%; box-shadow: 0 0 10px rgba(230, 140, 124, 0.5); }
        .waiting { background-color: #E68C7C; } 
        .delivery { background-color: #8FA3BF; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { text-align: left; padding: 15px; color: var(--gold-color); border-bottom: 1px solid rgba(230, 140, 124, 0.3); }
        .admin-table td { padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        .btn-action { padding: 6px 15px; font-size: 0.7rem; text-transform: uppercase; cursor: pointer; border-radius: 4px; font-weight: 500; }
        .btn-action.success { background: var(--gold-color); color: var(--bg-color); border: 1px solid var(--gold-color); }
        .btn-action.outline { background: transparent; border: 1px solid #8FA3BF; color: #8FA3BF; }
        .mt-40 { margin-top: 40px; }
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
                <a href="logout.php">Déconnexion (Chef)</a>
            </div>
        </div>
    </header>

    <main class="admin-container">
        <div class="admin-header-flex">
            <h1 class="main-title-profile">Gestion des Commandes</h1>
        </div>

        <div class="admin-stats">
            <div class="stat-card">
                <span class="stat-number">1</span>
                <span class="stat-label">À Préparer</span>
            </div>
            <div class="stat-card">
                <span class="stat-number">0</span>
                <span class="stat-label">En Livraison</span>
            </div>
        </div>

        <section class="admin-card order-section">
            <div class="section-header">
                <div class="status-indicator waiting"></div>
                <h2 style="color:var(--gold-color); font-family:var(--font-title);">Commandes à Préparer</h2>
            </div>
            <div class="table-container">
                <table class="admin-table">
                    <thead><tr><th>N°</th><th>Client</th><th>Détails</th><th>Heure</th><th>Action</th></tr></thead>
                    <tbody>
                        <tr><td>#0001</td><td><strong>Jean Dupont</strong></td><td>1x Couscous Royal</td><td>19:30</td><td><button class="btn-action success">Prête</button></td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card order-section mt-40">
            <div class="section-header">
                <div class="status-indicator delivery"></div>
                <h2 style="color:var(--gold-color); font-family:var(--font-title);">En cours de Livraison</h2>
            </div>
            <div class="table-container">
                <table class="admin-table">
                    <thead><tr><th>N°</th><th>Client / Adresse</th><th>Livreur</th><th>Départ</th><th>Action</th></tr></thead>
                    <tbody>
                        <tr><td colspan="5" style="text-align:center;">Aucune livraison en cours</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
