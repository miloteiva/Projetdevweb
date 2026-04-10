<?php
session_start();

// Vérification de sécurité pour le rôle client [cite: 16, 105]
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    header("Location: connection.php");
    exit();
}

$message_remerciement = false;

// Traitement du formulaire lors de l'envoi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ici, on pourrait enregistrer les notes dans data.json [cite: 115]
    // Pour l'instant, on déclenche l'affichage du message de succès
    $message_remerciement = true;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Noter ma commande - Les Arcades</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <style>
        /* --- BASE THÈME LES ARCADES --- */
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; line-height: 1.6; }
        
        header { background-color: transparent; padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); }
        .top-bar { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); letter-spacing: 2px; }
        
        .notation-container { padding: 60px 20px; display: flex; justify-content: center; align-items: center; min-height: 80vh; }
        .rating-card { background: rgba(19, 30, 58, 0.5); backdrop-filter: blur(10px); border: 1px solid rgba(230, 140, 124, 0.3); padding: 50px; border-radius: 8px; max-width: 600px; width: 100%; text-align: center; box-shadow: 0 15px 35px rgba(0,0,0,0.4); }
        
        .main-title { font-family: var(--font-title); color: var(--gold-color); font-size: 2.5rem; margin-bottom: 10px; }
        
        /* --- STYLE FORMULAIRE --- */
        .stars { display: flex; flex-direction: row-reverse; justify-content: center; gap: 10px; margin-bottom: 20px; }
        .stars input { display: none; }
        .stars label { font-size: 2.5rem; color: rgba(232, 241, 245, 0.2); cursor: pointer; transition: 0.3s; }
        .stars label:hover, .stars label:hover ~ label, .stars input:checked ~ label { color: var(--gold-color); text-shadow: 0 0 10px rgba(230, 140, 124, 0.5); }
        
        textarea { width: 100%; height: 100px; background: rgba(6, 11, 25, 0.5); border: 1px solid rgba(230, 140, 124, 0.3); border-radius: 4px; color: var(--text-color); padding: 15px; font-family: var(--font-body); resize: none; margin-top: 10px; }
        
        .submit-btn { background-color: var(--gold-color); color: #060B19; border: none; padding: 15px 40px; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; transition: 0.3s; margin-top: 20px; }
        .submit-btn:hover { background-color: #E8F1F5; transform: translateY(-2px); }

        /* --- STYLE MESSAGE MERCI --- */
        .thank-you-msg { animation: fadeIn 0.8s ease-in-out; }
        .check-icon { font-size: 4rem; color: var(--gold-color); margin-bottom: 20px; display: block; }
        .btn-retour { display: inline-block; margin-top: 30px; color: var(--gold-color); text-decoration: underline; font-size: 0.9rem; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <header>
        <div class="top-bar">
            <div class="logo">LES ARCADES</div>
            <div class="auth-links"><a href="moncompte.php" style="color:#E8F1F5; text-decoration:none;">Mon Espace</a></div>
        </div>
    </header>

    <main class="notation-container">
        <section class="rating-card">
            
            <?php if ($message_remerciement): ?>
                <div class="thank-you-msg">
                    <span class="check-icon">✨</span>
                    <h1 class="main-title">Merci infiniment !</h1>
                    <p>Votre avis a bien été pris en compte. <br> Grâce à vous, <strong>Les Arcades</strong> continuent de s'améliorer chaque jour.</p>
                    <a href="moncompte.php" class="btn-retour">Retourner à mon compte</a>
                </div>

            <?php else: ?>
                <h1 class="main-title">Votre Avis</h1>
                <p>Comment s'est passée votre dégustation ?</p>

                <form action="notation.php" method="POST" class="rating-form">
                    <div class="rating-group">
                        <h3 style="color:var(--gold-color); margin-top:30px;">Qualité des produits</h3>
                        <div class="stars">
                            <input type="radio" id="p5" name="p-rate" value="5"><label for="p5">★</label>
                            <input type="radio" id="p4" name="p-rate" value="4"><label for="p4">★</label>
                            <input type="radio" id="p3" name="p-rate" value="3"><label for="p3">★</label>
                            <input type="radio" id="p2" name="p-rate" value="2"><label for="p2">★</label>
                            <input type="radio" id="p1" name="p-rate" value="1"><label for="p1">★</label>
                        </div>
                    </div>

                    <div class="rating-group">
                        <h3 style="color:var(--gold-color);">Service de livraison</h3>
                        <div class="stars">
                            <input type="radio" id="l5" name="l-rate" value="5"><label for="l5">★</label>
                            <input type="radio" id="l4" name="l-rate" value="4"><label for="l4">★</label>
                            <input type="radio" id="l3" name="l-rate" value="3"><label for="l3">★</label>
                            <input type="radio" id="l2" name="l-rate" value="2"><label for="l2">★</label>
                            <input type="radio" id="l1" name="l-rate" value="1"><label for="l1">★</label>
                        </div>
                    </div>

                    <textarea name="comment" placeholder="Un petit mot sur votre expérience..."></textarea>
                    
                    <button type="submit" class="submit-btn">Envoyer mon avis</button>
                </form>
            <?php endif; ?>

        </section>
    </main>
</body>
</html>
