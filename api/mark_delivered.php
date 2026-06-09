<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    echo json_encode(['success' => false, 'message' => 'Accès réservé aux livreurs']);
    exit();
}

$input   = json_decode(file_get_contents('php://input'), true);
$orderId = intval($input['order_id'] ?? 0);

if (!$orderId) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit();
}

$file = '../data.json';
$data = json_decode(file_get_contents($file), true);
$found = false;
$clientId = null;

foreach ($data['commandes'] as &$cmd) {
    if ($cmd['id'] === $orderId) {
        // Vérifier que ce livreur est bien assigné
        if (!isset($cmd['id_livreur']) || (int)$cmd['id_livreur'] !== (int)$_SESSION['user']['id']) {
            echo json_encode(['success' => false, 'message' => 'Cette commande ne vous est pas assignée']);
            exit();
        }
        // On ne peut valider que les commandes en livraison
        if ($cmd['statut'] !== 'En livraison') {
            echo json_encode(['success' => false, 'message' => 'Cette commande n\'est pas en cours de livraison']);
            exit();
        }
        $cmd['statut'] = 'Livrée';
        $cmd['heure_livraison'] = date('H:i');
        $clientId = $cmd['id_client'];
        $found = true;

        // Mettre à jour les statistiques des plats (nb_commandes)
        foreach ($cmd['articles'] as $article) {
            foreach ($data['plats'] as &$plat) {
                if ($plat['nom'] === $article['nom']) {
                    $plat['nb_commandes'] = ($plat['nb_commandes'] ?? 0) + $article['quantite'];
                    break;
                }
            }
            unset($plat);
        }

        // Ajouter des points fidélité au client (+5 par livraison)
        foreach ($data['users'] as &$u) {
            if ($u['id'] === $clientId && $u['role'] === 'client') {
                $u['points_fidelite'] = ($u['points_fidelite'] ?? 0) + 5;
                break;
            }
        }
        unset($u);
        break;
    }
}
unset($cmd);

if (!$found) {
    echo json_encode(['success' => false, 'message' => 'Commande introuvable']);
    exit();
}

// Log de la livraison (Phase 4)
if (!isset($data['logs'])) $data['logs'] = [];
if (!isset($data['id_counters']['log'])) $data['id_counters']['log'] = 0;
$data['id_counters']['log']++;
$data['logs'][] = [
    'id'      => $data['id_counters']['log'],
    'date'    => date('d/m/Y H:i:s'),
    'type'    => 'order_delivered',
    'message' => "Commande #$orderId livrée (+5 pts fidélité client #$clientId)",
    'user_id' => $_SESSION['user']['id'],
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
    'ua'      => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 100)
];
if (count($data['logs']) > 500) $data['logs'] = array_slice($data['logs'], -500);

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['success' => true, 'message' => 'Livraison validée']);
