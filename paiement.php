<?php
session_start();

if (!isset($_SESSION['user'])) { header("Location: connection.php"); exit(); }

// Mode complément (paiement de différence sur commande modifiée)
$mode_complement = isset($_GET['complement']) ? (int)$_GET['complement'] : 0;
$data = json_decode(file_get_contents('data.json'), true);

$montant_a_payer = 0;
$libelle = "";

if ($mode_complement > 0) {
    foreach ($data['commandes'] ?? [] as $cmd) {
        if ($cmd['id'] === $mode_complement && $cmd['id_client'] === $_SESSION['user']['id']) {
            // Cherche le complément (différence non encore réglée)
            $libelle = "Complément pour commande #" . $cmd['id'];
            $montant_a_payer = $cmd['total'] - ($cmd['montant_paye'] ?? $cmd['total']);
            // Si commande modifiée et plus chère que paiement initial : on demande la différence
            // Hypothèse simplifiée : on stocke `montant_paye_initial` à la commande, sinon on prend total
            break;
        }
    }
} else {
    // Flux normal : panier
    if (empty($_SESSION['panier'])) {
        header("Location: panier.php");
        exit();
    }
    foreach ($_SESSION['panier'] as $item) {
        $montant_a_payer += $item['prix'] * $item['quantite'];
    }
    $libelle = "Commande Les Arcades (" . count($_SESSION['panier']) . " article(s))";
}

// Traitement du paiement (simulation CYBank)
$paiement_ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_paiement'])) {
    $num_carte = preg_replace('/\s+/', '', $_POST['num_carte'] ?? '');
    $cvc = $_POST['cvc'] ?? '';
    $exp = $_POST['exp'] ?? '';

    if (preg_match('/^\d{13,19}$/', $num_carte) && preg_match('/^\d{3,4}$/', $cvc) && !empty($exp)) {

        if ($mode_complement === 0) {
            // Création de la commande
            if (!isset($data['commandes'])) $data['commandes'] = [];
            $articles = [];
            foreach ($_SESSION['panier'] as $item) {
                $articles[] = [
                    'nom' => $item['nom'],
                    'prix' => $item['prix'],
                    'quantite' => $item['quantite'],
                    'type' => $item['type'] ?? 'plat'
                ];
            }
            $nouvelle = [
                'id' => count($data['commandes']) + 1,
                'id_client' => $_SESSION['user']['id'],
                'client' => $_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom'],
                'articles' => $articles,
                'total' => $montant_a_payer,
                'montant_paye' => $montant_a_payer,
                'statut' => 'Payée',
                'type' => $_POST['type_livraison'] ?? 'livraison',
                'id_livreur' => null,
                'date' => date('d/m/Y'),
                'heure' => date('H:i'),
                'note' => false,
                'notes' => null
            ];
            $data['commandes'][] = $nouvelle;
            unset($_SESSION['panier']);
        } else {
            // Complément payé
            foreach ($data['commandes'] as &$c) {
                if ($c['id'] === $mode_complement) {
                    $c['montant_paye'] = $c['total'];
                    $c['complement_paye'] = true;
                    break;
                }
            }
            unset($c);
        }

        file_put_contents('data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $paiement_ok = true;
    } else {
        $erreur = "Informations de carte invalides";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paiement - CYBank</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500&family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet">
    <style>
        :root { --bg-color: #060B19; --text-color: #E8F1F5; --gold-color: #E68C7C; --font-title: 'Playfair Display', serif; --font-body: 'Montserrat', sans-serif; --muted-blue: #8FA3BF; }
        body { background: linear-gradient(180deg, #02050E 0%, #1B335F 100%) fixed; color: var(--text-color); font-family: var(--font-body); margin: 0; padding: 0; }
        header { padding: 20px 40px; border-bottom: 1px solid rgba(230, 140, 124, 0.3); display: flex; justify-content: space-between; }
        .logo { font-family: var(--font-title); font-size: 1.8rem; color: var(--gold-color); }
        .nav-link { color: var(--text-color); text-decoration: none; font-size: 0.85rem; }
        .pay-container { max-width: 500px; margin: 50px auto; padding: 40px; background: rgba(19, 30, 58, 0.6); border: 1px solid rgba(230, 140, 124, 0.2); border-radius: 6px; backdrop-filter: blur(10px); }
        h1 { font-family: var(--font-title); color: var(--gold-color); margin-bottom: 10px; }
        .total-box { background: rgba(230, 140, 124, 0.1); border-left: 3px solid var(--gold-color); padding: 15px 20px; margin: 20px 0; }
        .total-box .label { font-size: 0.8rem; color: var(--muted-blue); text-transform: uppercase; letter-spacing: 1px; }
        .total-box .amount { font-family: var(--font-title); color: var(--gold-color); font-size: 2.2rem; }
        .input-group { margin-bottom: 15px; }
        .input-group label { display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--muted-blue); letter-spacing: 1px; margin-bottom: 6px; }
        .input-group input, .input-group select { width: 100%; padding: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(230, 140, 124, 0.3); color: var(--text-color); border-radius: 4px; font-family: inherit; font-size: 1rem; box-sizing: border-box; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .btn-pay { background: var(--gold-color); color: var(--bg-color); border: none; padding: 16px; width: 100%; font-family: inherit; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; cursor: pointer; margin-top: 20px; transition: 0.3s; }
        .btn-pay:hover { background: #f2a698; box-shadow: 0 5px 20px rgba(230, 140, 124, 0.3); }
        .success { text-align: center; padding: 40px; }
        .success .check { font-size: 4rem; color: var(--gold-color); }
        .erreur { color: #ff6b6b; padding: 10px; border: 1px solid #ff6b6b; background: rgba(255,107,107,0.1); margin-bottom: 15px; }
    </style>
    <link rel="stylesheet" id="theme-css" href="css/theme-dark.css">
</head>
<body>
    <header>
        <div class="logo">LES ARCADES</div>
        <a href="restaurant.php" class="nav-link">Retour à l'accueil</a>
    </header>

    <main class="pay-container">
        <?php if ($paiement_ok): ?>
            <div class="success">
                <div class="check">✓</div>
                <h1>Paiement validé</h1>
                <p style="color: var(--muted-blue);">Votre transaction a été traitée par CYBank.<br>Confirmation envoyée par email.</p>
                <a href="moncompte.php" class="btn-pay" style="display:inline-block; text-decoration:none; padding: 12px 25px; margin-top: 20px;">Mes commandes</a>
            </div>
        <?php else: ?>
            <h1>Paiement sécurisé</h1>
            <p style="color: var(--muted-blue); font-size: 0.85rem;">via l'API CYBank · <?= htmlspecialchars($libelle) ?></p>

            <div class="total-box">
                <div class="label"><?= $mode_complement ? 'Complément à régler' : 'Montant total' ?></div>
                <div class="amount"><?= number_format($montant_a_payer, 2) ?> €</div>
            </div>

            <?php if (isset($erreur)): ?><div class="erreur"><?= $erreur ?></div><?php endif; ?>

            <form method="POST">
                <?php if ($mode_complement === 0): ?>
                <div class="input-group">
                    <label>Mode de réception</label>
                    <select name="type_livraison" required>
                        <option value="livraison">Livraison à domicile</option>
                        <option value="emporter">À emporter</option>
                        <option value="place">Sur place</option>
                    </select>
                </div>
                <?php endif; ?>

                <div class="input-group">
                    <label>Numéro de carte</label>
                    <input type="text" name="num_carte" placeholder="1234 5678 9012 3456" maxlength="19" required>
                </div>
                <div class="row">
                    <div class="input-group">
                        <label>Expiration</label>
                        <input type="text" name="exp" placeholder="MM/AA" maxlength="5" required>
                    </div>
                    <div class="input-group">
                        <label>CVC</label>
                        <input type="text" name="cvc" placeholder="123" maxlength="4" required>
                    </div>
                </div>
                <button type="submit" name="valider_paiement" class="btn-pay">Payer <?= number_format($montant_a_payer, 2) ?> €</button>
            </form>
        <?php endif; ?>
    </main>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
</body>
</html>
