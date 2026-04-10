<?php
session_start();
$erreur = "";
$succes = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sécurisation des données entrantes (XSS)
    $nom = htmlspecialchars(trim($_POST['Nom']));
    $prenom = htmlspecialchars(trim($_POST['Prénom']));
    $adresse = htmlspecialchars(trim($_POST['Adresse']));
    $telephone = htmlspecialchars(trim($_POST['Téléphone']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password']; // Le mot de passe sera vérifié, idéalement hashé en prod.

    // Validation stricte
    if (empty($nom) || empty($prenom) || empty($adresse) || empty($telephone) || empty($email) || empty($password)) {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "L'adresse email n'est pas valide.";
    } elseif (strlen($password) < 4) {
        $erreur = "Le mot de passe doit contenir au moins 4 caractères.";
    } else {
        $file = 'data.json';
        $data = json_decode(file_get_contents($file), true);

        // Vérification des doublons d'email
        $emailExiste = false;
        foreach ($data['users'] as $user) {
            if ($user['email'] === $email) {
                $emailExiste = true;
                break;
            }
        }

        if ($emailExiste) {
            $erreur = "Cette adresse email est déjà utilisée. Veuillez vous connecter.";
        } else {
            // Création du nouvel utilisateur
            $newUser = [
                "id" => count($data['users']) + 1,
                "nom" => $nom,
                "prenom" => $prenom,
                "adresse" => $adresse,
                "telephone" => $telephone,
                "email" => $email,
                "password" => $password, // Conservé en clair pour correspondre au data.json exigé par l'école
                "role" => "client"
            ];
            
            $data['users'][] = $newUser;
            file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
            
            $_SESSION['user'] = $newUser;
            header("Location: moncompte.php");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription - Les Arcades</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <style>
        /* --- BASE & COULEURS : THÈME "UN THÉ AU SAHARA" --- */
        :root {
            var(--bg-color): #060B19;      
            --bg-alt: #131E3A;        
            --text-color: #E8F1F5;    
            --gold-color: #E68C7C;    
            --font-title: 'Playfair Display', serif;
            --font-body: 'Montserrat', sans-serif;
        }

        body {
            background-color: #060B19;
            background: linear-gradient(180deg, #02050E 0%, #1B335F 100%);
            background-attachment: fixed;
            color: #E8F1F5;
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        header {
            background-color: transparent; 
            padding: 20px 40px;
            border-bottom: 1px solid rgba(230, 140, 124, 0.3); 
        }

        a { text-decoration: none; color: inherit; transition: 0.3s; }
        ul { list-style: none; padding: 0; margin: 0; }

        .top-bar { display: flex; justify-content: space-between; align-items: center; }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #E68C7C;
            letter-spacing: 2px;
            text-shadow: 0 0 15px rgba(230, 140, 124, 0.4); 
        }

        .navbar ul { display: flex; gap: 30px; }
        .navbar a {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #E8F1F5;
        }
        .navbar a:hover { color: #E68C7C; }
        .auth-links { font-size: 0.75rem; color: #8FA3BF; }

        /* --- STYLE INSCRIPTION --- */
        .login-page {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 90vh;
            padding: 40px 20px;
        }

        .main-title-profile {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            color: #E68C7C;
            align-self: flex-start; 
            max-width: 800px;
            margin: 0 auto 40px auto;
            width: 100%;
        }

        .login-container {
            background-color: rgba(19, 30, 58, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(230, 140, 124, 0.2);
            padding: 40px;
            border-radius: 4px;
            width: 100%;
            max-width: 500px; 
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .form-subtitle {
            font-family: 'Playfair Display', serif;
            color: #E68C7C;
            font-size: 1.8rem;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(230, 140, 124, 0.2);
            padding-bottom: 15px;
        }

        .input-group {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }

        .input-group label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #8FA3BF;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .input-group input {
            padding: 12px 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px dashed rgba(230, 140, 124, 0.4); 
            border-radius: 4px;
            color: #E8F1F5;
            font-family: 'Montserrat', sans-serif;
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            border: 1px solid #E68C7C;
            background: rgba(255, 255, 255, 0.1);
        }

        .btn-sign-in {
            background: transparent;
            border: 1px solid #E68C7C;
            color: #E68C7C;
            padding: 12px 25px;
            font-family: 'Montserrat', sans-serif;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
            width: fit-content; 
        }

        .btn-sign-in:hover {
            background: #E68C7C;
            color: #060B19;
            box-shadow: 0 0 15px rgba(230, 140, 124, 0.3);
        }

        .error-message {
            color: #E68C7C;
            font-weight: 500;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #E68C7C;
            padding: 10px;
            background: rgba(230, 140, 124, 0.1);
        }

        @media (max-width: 768px) {
            .main-title-profile {
                font-size: 2.5rem;
                text-align: center;
            }
        }
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
                <a href="inscription.php">Inscription</a> / <a href="connection.php">Connexion</a>
            </div>
        </div>
    </header>

    <main class="login-page">
        <h1 class="main-title-profile">Inscription</h1>

        <div class="login-container">
            <h2 class="form-subtitle">Créer un compte</h2>
            
            <?php if(!empty($erreur)): ?>
                <div class="error-message"><?= $erreur ?></div>
            <?php endif; ?>

            <form class="login-form" method="POST" action="inscription.php">
                <div class="input-group">
                    <label for="Nom">Nom</label>
                    <input type="text" id="Nom" name="Nom" required value="<?= isset($_POST['Nom']) ? htmlspecialchars($_POST['Nom']) : '' ?>">
                </div>
                <div class="input-group">
                    <label for="Prénom">Prénom</label>
                    <input type="text" id="Prénom" name="Prénom" required value="<?= isset($_POST['Prénom']) ? htmlspecialchars($_POST['Prénom']) : '' ?>">
                </div>
                <div class="input-group">
                    <label for="Adresse">Adresse</label>
                    <input type="text" id="Adresse" name="Adresse" required value="<?= isset($_POST['Adresse']) ? htmlspecialchars($_POST['Adresse']) : '' ?>">
                </div>
                <div class="input-group">
                    <label for="Téléphone">Téléphone</label>
                    <input type="tel" id="Téléphone" name="Téléphone" required value="<?= isset($_POST['Téléphone']) ? htmlspecialchars($_POST['Téléphone']) : '' ?>">
                </div>
                <div class="input-group">
                    <label for="email">Adresse Email</label>
                    <input type="email" id="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                </div>
                <div class="input-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn-sign-in">S'inscrire</button>
            </form>
        </div>
    </main>

</body>
</html>
