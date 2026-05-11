<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'livreur') {
    echo json_encode(['success' => false, 'message' => 'Accès réservé aux livreurs']);
    exit();
}

$input   = json_decode(file_get_contents('php://input'), true);
$orderId = intval($input['order_id'] ?? 0);

if (!$orderId) { echo json_encode(['success' => false, 'message' => 'ID manquant']); exit(); }

$file = '../data.json';
$data = json_decode(file_get_contents($file), true);
$found = false;

foreach ($data['commandes'] as &$cmd) {
    if ($cmd['id'] === $orderId) {
        // Vérifier que ce livreur est bien assigné
        if (isset($cmd['id_livreur']) && $cmd['id_livreur'] !== $_SESSION['user']['id']) {
            echo json_encode(['success' => false, 'message' => 'Cette commande ne vous est pas assignée']);
            exit();
        }
        $cmd['statut'] = 'Livrée';
        $cmd['heure_livraison'] = date('H:i');
        $found = true;

        // Ajouter des points fidélité au client
        $clientId = $cmd['id_client'];
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

if (!$found) { echo json_encode(['success' => false, 'message' => 'Commande introuvable']); exit(); }

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['success' => true, 'message' => 'Livraison validée']);
