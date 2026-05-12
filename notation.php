<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    header("Location: connection.php");
    exit();
}

// Récupération de l'ID de commande à noter
$idCmd = isset($_GET['commande']) ? (int)$_GET['commande'] : 0;
$data = json_decode(file_get_contents('data.json'), true);
$commande = null;

foreach ($data['commandes'] ?? [] as $c) {
    if ($c['id'] === $idCmd && $c['id_client'] === $_SESSION['user']['id']) {
        $commande = $c;
        break;
    }
}

// Vérifications
$erreur_acces = null;
if (!$commande) {
    $erreur_acces = "Cette commande n'existe pas ou ne vous appartient pas.";
} elseif ($commande['statut'] !== 'Livrée') {
    $erreur_acces = "Vous ne pouvez noter qu'une commande déjà livrée.";
} elseif (!empty($commande['note'])) {
    $erreur_acces = "Vous avez déjà noté cette commande.";
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
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; --muted-blue: #8FA3BF; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; line-height: 1.6; }

        header { background-color: transparent; padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); }
        .top-bar { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); letter-spacing: 2px; }

        .notation-container { padding: 60px 20px; display: flex; justify-content: center; align-items: center; min-height: 80vh; }
        .rating-card { background: rgba(19, 30, 58, 0.5); backdrop-filter: blur(10px); border: 1px solid rgba(230, 140, 124, 0.3); padding: 50px; border-radius: 8px; max-width: 600px; width: 100%; text-align: center; box-shadow: 0 15px 35px rgba(0,0,0,0.4); }

        .main-title { font-family: var(--font-title); color: var(--gold-color); font-size: 2.5rem; margin-bottom: 10px; }
        .order-info { color: var(--muted-blue); font-size: 0.9rem; margin-bottom: 25px; }

        .stars { display: flex; flex-direction: row-reverse; justify-content: center; gap: 10px; margin-bottom: 20px; }
        .stars input { display: none; }
        .stars label { font-size: 2.5rem; color: rgba(232, 241, 245, 0.2); cursor: pointer; transition: 0.3s; }
        .stars label:hover, .stars label:hover ~ label, .stars input:checked ~ label { color: var(--gold-color); text-shadow: 0 0 10px rgba(230, 140, 124, 0.5); }

        textarea { width: 100%; height: 100px; background: rgba(6, 11, 25, 0.5); border: 1px solid rgba(230, 140, 124, 0.3); border-radius: 4px; color: var(--text-color); padding: 15px; font-family: var(--font-body); resize: none; margin-top: 10px; box-sizing: border-box; }

        .submit-btn { background-color: var(--gold-color); color: #060B19; border: none; padding: 15px 40px; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; transition: 0.3s; margin-top: 20px; font-family: inherit; }
        .submit-btn:hover { background-color: #E8F1F5; transform: translateY(-2px); }
        .submit-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        .thank-you-msg { animation: fadeIn 0.8s ease-in-out; }
        .check-icon { font-size: 4rem; color: var(--gold-color); margin-bottom: 20px; display: block; }
        .btn-retour { display: inline-block; margin-top: 30px; color: var(--gold-color); text-decoration: underline; font-size: 0.9rem; }

        .erreur-box { background: rgba(255, 107, 107, 0.1); border-left: 3px solid #ff6b6b; padding: 20px; margin: 20px 0; color: #ff6b6b; text-align: left; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    <link rel="stylesheet" id="theme-css" href="css/theme-dark.css">
</head>
<body>
    <header>
        <div class="top-bar">
            <div class="logo">LES ARCADES</div>
            <div class="auth-links"><a href="moncompte.php" style="color:#E8F1F5; text-decoration:none;">← Mon Espace</a></div>
        </div>
    </header>

    <main class="notation-container">
        <section class="rating-card" id="rating-card">

            <?php if ($erreur_acces): ?>
                <h1 class="main-title">Notation impossible</h1>
                <div class="erreur-box"><?= htmlspecialchars($erreur_acces) ?></div>
                <a href="moncompte.php" class="btn-retour">← Retour à mon compte</a>

            <?php else: ?>
                <h1 class="main-title">Votre Avis</h1>
                <p>Comment s'est passée votre dégustation ?</p>
                <div class="order-info">Commande #<?= $commande['id'] ?> · <?= number_format($commande['total'], 2) ?>€ · <?= htmlspecialchars($commande['date']) ?></div>

                <!-- Pas d'attribut action : l'envoi se fait en AJAX -->
                <form id="rating-form" onsubmit="return false;">
                    <input type="hidden" id="order-id" value="<?= $commande['id'] ?>">

                    <div class="rating-group">
                        <h3 style="color:var(--gold-color); margin-top:30px;">Qualité des produits</h3>
                        <div class="stars">
                            <input type="radio" id="p5" name="note_produit" value="5"><label for="p5">★</label>
                            <input type="radio" id="p4" name="note_produit" value="4"><label for="p4">★</label>
                            <input type="radio" id="p3" name="note_produit" value="3"><label for="p3">★</label>
                            <input type="radio" id="p2" name="note_produit" value="2"><label for="p2">★</label>
                            <input type="radio" id="p1" name="note_produit" value="1"><label for="p1">★</label>
                        </div>
                    </div>

                    <div class="rating-group">
                        <h3 style="color:var(--gold-color);">Service de livraison</h3>
                        <div class="stars">
                            <input type="radio" id="l5" name="note_livraison" value="5"><label for="l5">★</label>
                            <input type="radio" id="l4" name="note_livraison" value="4"><label for="l4">★</label>
                            <input type="radio" id="l3" name="note_livraison" value="3"><label for="l3">★</label>
                            <input type="radio" id="l2" name="note_livraison" value="2"><label for="l2">★</label>
                            <input type="radio" id="l1" name="note_livraison" value="1"><label for="l1">★</label>
                        </div>
                    </div>

                    <textarea id="commentaire" name="commentaire" placeholder="Un petit mot sur votre expérience..." maxlength="500"></textarea>

                    <button type="submit" class="submit-btn" id="submit-btn">Envoyer mon avis</button>
                </form>
            <?php endif; ?>

        </section>
    </main>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
    <script>
    // --- Envoi de la notation en AJAX (Phase 3) ---
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('rating-form');
        if (!form) return;

        const submitBtn = document.getElementById('submit-btn');

        submitBtn.addEventListener('click', async () => {
            const orderId  = parseInt(document.getElementById('order-id').value, 10);
            const noteProd = document.querySelector('input[name="note_produit"]:checked');
            const noteLiv  = document.querySelector('input[name="note_livraison"]:checked');
            const comment  = document.getElementById('commentaire').value.trim();

            if (!noteProd) {
                notify('Merci de noter la qualité des produits (au moins une étoile).', 'error');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Envoi en cours...';

            const result = await apiCall('api/save_rating.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    order_id: orderId,
                    note_produit: parseInt(noteProd.value, 10),
                    note_livraison: noteLiv ? parseInt(noteLiv.value, 10) : 0,
                    commentaire: comment
                })
            });

            if (result.success) {
                // Affichage de l'écran de remerciement (sans recharger la page)
                document.getElementById('rating-card').innerHTML = `
                    <div class="thank-you-msg">
                        <span class="check-icon">✨</span>
                        <h1 class="main-title">Merci infiniment !</h1>
                        <p>Votre avis a bien été enregistré.<br>
                        Grâce à vous, <strong>Les Arcades</strong> continuent de s'améliorer chaque jour.</p>
                        <a href="moncompte.php" class="btn-retour">Retourner à mon compte</a>
                    </div>
                `;
                notify('Merci pour votre avis !', 'success');
            } else {
                notify(result.message || 'Erreur lors de l\'envoi', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Envoyer mon avis';
            }
        });
    });
    </script>
</body>
</html>
