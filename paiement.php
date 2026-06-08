<?php
session_start();
require_once 'includes/functions.php';

if (!isset($_SESSION['user'])) { header("Location: connection.php"); exit(); }

// Mode complément (paiement de différence sur commande modifiée)
$mode_complement = isset($_GET['complement']) ? (int)$_GET['complement'] : 0;
$data = loadData();

$montant_a_payer = 0;
$libelle = "";

if ($mode_complement > 0) {
    foreach ($data['commandes'] ?? [] as $cmd) {
        if ($cmd['id'] === $mode_complement && $cmd['id_client'] === $_SESSION['user']['id']) {
            $libelle = "Complément pour commande #" . $cmd['id'];
            $montant_a_payer = $cmd['total'] - ($cmd['montant_paye'] ?? $cmd['total']);
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

    // Validation robuste côté serveur
    $errors_paiement = [];
    if (!verifierLuhn($num_carte)) {
        $errors_paiement[] = "Numéro de carte invalide.";
    }
    // CVC
    if (!preg_match('/^\d{3,4}$/', $cvc)) $errors_paiement[] = "Code CVC invalide (3 ou 4 chiffres).";
    // Expiry
    if (!preg_match('/^(\d{2})\/?(\d{2})$/', $exp, $ematch)) {
        $errors_paiement[] = "Date d'expiration invalide (format MM/AA).";
    } else {
        $emm = (int)$ematch[1]; $eyy = (int)$ematch[2];
        if ($emm < 1 || $emm > 12) $errors_paiement[] = "Mois d'expiration invalide.";
        $cyy = (int)date('y'); $cmm = (int)date('m');
        if ($eyy < $cyy || ($eyy === $cyy && $emm < $cmm)) $errors_paiement[] = "Carte expirée.";
    }

    if (empty($errors_paiement)) {

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

            // PHASE 2 : Mode de réception (livraison/emporter/place)
            $type_livraison = $_POST['type_livraison'] ?? 'livraison';

            // PHASE 2 : Récupération immédiate OU différée
            $moment = $_POST['moment'] ?? 'immediat';
            $date_prevue = date('d/m/Y');
            $heure_prevue = date('H:i');
            $immediate = true;

            if ($moment === 'differe') {
                $date_choisie = $_POST['date_prevue'] ?? '';
                $heure_choisie = $_POST['heure_prevue'] ?? '';
                if ($date_choisie && $heure_choisie) {
                    // Validation du format
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_choisie) && preg_match('/^\d{2}:\d{2}$/', $heure_choisie)) {
                        $ts = strtotime($date_choisie . ' ' . $heure_choisie);
                        if ($ts && $ts > time() + 1800) { // au moins 30 min dans le futur
                            $date_prevue = date('d/m/Y', $ts);
                            $heure_prevue = date('H:i', $ts);
                            $immediate = false;
                        }
                    }
                }
            }

            $nouvelle = [
                'id'                 => count($data['commandes']) + 1,
                'id_client'          => $_SESSION['user']['id'],
                'client'             => $_SESSION['user']['prenom'] . ' ' . $_SESSION['user']['nom'],
                'articles'           => $articles,
                'total'              => $montant_a_payer,
                'montant_paye'       => $montant_a_payer,
                'statut'             => 'Payée',
                'type'               => $type_livraison,
                'id_livreur'         => null,
                'date'               => date('d/m/Y'),
                'heure'              => date('H:i'),
                'date_prevue'        => $date_prevue,
                'heure_prevue'       => $heure_prevue,
                'preparation_immediate' => $immediate,
                'note'               => false,
                'notes'              => null
            ];
            $data['commandes'][] = $nouvelle;

            // PHASE 4 : Log paiement OK
            $data = addLog($data, 'paiement_ok', "Paiement validé : commande #{$nouvelle['id']} - {$montant_a_payer}€", $_SESSION['user']['id']);

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
            // PHASE 4 : Log complément payé
            $data = addLog($data, 'paiement_ok', "Complément payé : commande #$mode_complement - {$montant_a_payer}€", $_SESSION['user']['id']);
        }

        saveData($data);
        $paiement_ok = true;
    } else {
        $erreur = implode(" ", $errors_paiement);
        // PHASE 4 : Log paiement échoué
        $data = addLog($data, 'paiement_echec', 'Paiement refusé : ' . $erreur, $_SESSION['user']['id']);
        saveData($data);
    }
}

// Pour les dates : min = aujourd'hui, max = dans 7 jours
$date_min = date('Y-m-d');
$date_max = date('Y-m-d', strtotime('+7 days'));
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
        .moment-choice { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; }
        .moment-option { padding: 15px 12px; border: 1px solid rgba(230, 140, 124, 0.3); border-radius: 6px; text-align: center; cursor: pointer; transition: 0.3s; background: rgba(255,255,255,0.03); }
        .moment-option:hover { border-color: var(--gold-color); background: rgba(230, 140, 124, 0.08); }
        .moment-option.active { border-color: var(--gold-color); background: rgba(230, 140, 124, 0.15); }
        .moment-option .titre { font-family: var(--font-title); color: var(--gold-color); font-size: 1rem; margin-bottom: 4px; }
        .moment-option .desc { font-size: 0.7rem; color: var(--muted-blue); }
        #differe-fields { display: none; padding: 12px; background: rgba(230, 140, 124, 0.05); border-radius: 4px; margin-bottom: 15px; }
        #differe-fields.show { display: block; }
    </style>
    <link rel="stylesheet" id="theme-css" href="css/theme-dark.css">
    <link rel="stylesheet" href="css/mobile.css">
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

            <form method="POST" id="payment-form">
                <?php if ($mode_complement === 0): ?>
                <div class="input-group">
                    <label>Mode de réception</label>
                    <select name="type_livraison" required>
                        <option value="livraison">Livraison à domicile</option>
                        <option value="emporter">À emporter</option>
                        <option value="place">Sur place</option>
                    </select>
                </div>

                <!-- PHASE 2 : Préparation immédiate ou différée -->
                <div class="input-group">
                    <label>Quand préparer votre commande ?</label>
                    <div class="moment-choice">
                        <label class="moment-option active" id="opt-immediat">
                            <input type="radio" name="moment" value="immediat" checked style="display:none">
                            <div class="titre">⚡ Maintenant</div>
                            <div class="desc">Préparation immédiate</div>
                        </label>
                        <label class="moment-option" id="opt-differe">
                            <input type="radio" name="moment" value="differe" style="display:none">
                            <div class="titre">🕒 Plus tard</div>
                            <div class="desc">Programmer la commande</div>
                        </label>
                    </div>
                </div>

                <div id="differe-fields">
                    <div class="row">
                        <div class="input-group">
                            <label>Date prévue</label>
                            <input type="date" name="date_prevue" min="<?= $date_min ?>" max="<?= $date_max ?>" value="<?= $date_min ?>">
                        </div>
                        <div class="input-group">
                            <label>Heure prévue</label>
                            <input type="time" name="heure_prevue" min="11:00" max="22:30" step="900" value="19:30">
                        </div>
                    </div>
                    <p style="color: var(--muted-blue); font-size: 0.75rem; margin: 0;">
                        ⓘ Service de 12h à 23h. Au minimum 30 min à l'avance.
                    </p>
                </div>
                <?php endif; ?>

                <div class="input-group">
                    <label>Numéro de carte</label>
                    <input type="text" name="num_carte" placeholder="1234 5678 9012 3456" maxlength="23" data-validate="card_number" required>
                </div>
                <div class="row">
                    <div class="input-group">
                        <label>Expiration</label>
                        <input type="text" name="exp" placeholder="MM/AA" maxlength="5" data-validate="exp" required>
                    </div>
                    <div class="input-group">
                        <label>CVC</label>
                        <input type="text" name="cvc" placeholder="123" maxlength="4" data-validate="cvc" required>
                    </div>
                </div>
                <p style="color:#8FA3BF;font-size:0.75rem;margin-top:10px;">🔒 Connexion sécurisée — données bancaires non stockées</p>
                <button type="submit" name="valider_paiement" class="btn-pay">Payer <?= number_format($montant_a_payer, 2) ?> €</button>
            </form>

            <script>
            // Toggle immédiat / différé
            const optImmediat = document.getElementById('opt-immediat');
            const optDiffere = document.getElementById('opt-differe');
            const differeFields = document.getElementById('differe-fields');
            if (optImmediat && optDiffere) {
                optImmediat.addEventListener('click', () => {
                    optImmediat.classList.add('active');
                    optDiffere.classList.remove('active');
                    optImmediat.querySelector('input').checked = true;
                    differeFields.classList.remove('show');
                });
                optDiffere.addEventListener('click', () => {
                    optDiffere.classList.add('active');
                    optImmediat.classList.remove('active');
                    optDiffere.querySelector('input').checked = true;
                    differeFields.classList.add('show');
                });
            }
            </script>
        <?php endif; ?>
    </main>

    <script src="js/theme.js"></script>
    <script src="js/common.js"></script>
    <script src="js/hamburger.js"></script>
    <script src="js/validation.js"></script>
</body>
</html>
