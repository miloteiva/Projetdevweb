<?php
session_start();
// Vérification stricte du rôle
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    header("Location: connection.php");
    exit();
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mon Compte - Les Arcades</title>
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

        /* --- MONCOMPTE.CSS --- */
        .profile-container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }
        .main-title { font-family: var(--font-title); font-size: 3.5rem; color: var(--gold-color); margin-bottom: 40px; }
        .profile-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .full-width { grid-column: span 2; }
        .profile-card { background-color: rgba(19, 30, 58, 0.6); backdrop-filter: blur(10px); border: 1px solid rgba(230, 140, 124, 0.2); padding: 30px; border-radius: 4px; }
        .profile-card h2 { font-family: var(--font-title); color: var(--gold-color); font-size: 1.8rem; margin-bottom: 25px; border-bottom: 1px solid rgba(230, 140, 124, 0.2); padding-bottom: 10px; }
        .info-group { margin-bottom: 20px; }
        .info-group label { display: block; font-size: 0.8rem; text-transform: uppercase; color: #8FA3BF; margin-bottom: 8px; }
        .placeholder-box { width: 100%; height: 40px; background: rgba(255, 255, 255, 0.05); border: 1px dashed rgba(230, 140, 124, 0.4); border-radius: 4px; display: flex; align-items: center; padding-left: 15px; box-sizing: border-box; }
        .placeholder-box.tall { height: 80px; align-items: flex-start; padding-top: 10px; }
        .loyalty-status { text-align: center; margin-bottom: 20px; }
        .points-count { display: block; font-size: 3rem; font-family: var(--font-title); color: var(--gold-color); }
        .progress-container { background: rgba(0, 0, 0, 0.3); height: 10px; border-radius: 10px; margin-bottom: 15px; overflow: hidden; }
        .progress-bar { width: 15%; height: 100%; background: var(--gold-color); box-shadow: 0 0 10px var(--gold-color); }
        .orders-table-container { overflow-x: auto; }
        .orders-table { width: 100%; border-collapse: collapse; text-align: left; }
        .orders-table th { padding: 15px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); font-weight: 500; color: var(--gold-color); }
        .orders-table td { padding: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }
        .status-pill { background: rgba(230, 140, 124, 0.2); color: var(--gold-color); padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; }
        .btn-action { background: transparent; border: 1px solid var(--gold-color); color: var(--gold-color); padding: 10px 20px; font-family: var(--font-body); text-transform: uppercase; cursor: pointer; transition: 0.3s; margin-top: 10px; }
        .btn-action:hover { background: var(--gold-color); color: var(--bg-color); }
        .view-link { color: var(--gold-color); text-decoration: underline; font-size: 0.9rem; }
        @media (max-width: 768px) { .profile-grid { grid-template-columns: 1fr; } .full-width { grid-column: span 1; } }
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
                    <li><a href="notation.php">Notation</a></li>
                </ul>
            </nav>
            <div class="auth-links">
                <a href="logout.php">Déconnexion</a>
            </div>
        </div>
    </header>
    <main class="profile-container">
        <h1 class="main-title">Mon Espace</h1>

        <div class="profile-grid">
            <section class="profile-card">
                <h2>Mes Informations</h2>
                <div class="info-group">
                    <label>Nom Complet</label>
                    <div class="placeholder-box"><?= htmlspecialchars($user['nom'] . " " . $user['prenom']) ?></div>
                </div>
                <div class="info-group">
                    <label>Email</label>
                    <div class="placeholder-box"><?= htmlspecialchars($user['email']) ?></div>
                </div>
                <div class="info-group">
                    <label>Téléphone</label>
                    <div class="placeholder-box"><?= htmlspecialchars($user['telephone'] ?? 'Non renseigné') ?></div>
                </div>
                <div class="info-group">
                    <label>Adresse de livraison</label>
                    <div class="placeholder-box tall"><?= htmlspecialchars($user['adresse'] ?? 'Non renseignée') ?></div>
                </div>
                <button class="btn-action">Modifier mon profil</button>
            </section>

            <section class="profile-card loyalty-card">
                <h2>Programme Fidélité</h2>
                <div class="loyalty-status">
                    <span class="points-count">15</span>
                    <span class="points-label">Points cumulés</span>
                </div>
                <div class="progress-container">
                    <div class="progress-bar"></div>
                </div>
                <p class="subtitle">Encore quelques points pour votre prochain thé offert !</p>
            </section>

            <section class="profile-card full-width">
                <h2>Historique des Commandes</h2>
                <div class="orders-table-container">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>N° Commande</th>
                                <th>Statut</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Aujourd'hui</td>
                                <td>#000001</td>
                                <td><span class="status-pill">En attente</span></td>
                                <td>0.00 €</td>
                                <td><a href="#" class="view-link">Détails</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
