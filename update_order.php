<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit();
}

$input    = json_decode(file_get_contents('php://input'), true);
$orderId  = intval($input['order_id'] ?? 0);
$articles = $input['articles'] ?? [];
$newTotal = floatval($input['total'] ?? 0);

if (!$orderId || empty($articles)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit();
}

$file = '../data.json';
$data = json_decode(file_get_contents($file), true);
$found = false;

foreach ($data['commandes'] as &$cmd) {
    if ($cmd['id'] === $orderId) {
        // Sécurité : seul le propriétaire peut modifier sa commande
        if ($cmd['id_client'] !== $_SESSION['user']['id']) {
            echo json_encode(['success' => false, 'message' => 'Cette commande ne vous appartient pas']);
            exit();
        }
        // Seules les commandes "Payée" peuvent être modifiées (pas encore en préparation)
        if ($cmd['statut'] !== 'Payée') {
            echo json_encode(['success' => false, 'message' => 'Modification impossible : la commande est déjà en préparation']);
            exit();
        }

        $oldTotal = $cmd['total'];
        $cmd['articles'] = $articles;
        $cmd['total']    = $newTotal;
        $cmd['modifiee'] = true;

        // Si moins cher : créer un ticket de réduction (option laissée libre par le PDF)
        if ($newTotal < $oldTotal) {
            $diff = $oldTotal - $newTotal;
            if (!isset($data['tickets'])) $data['tickets'] = [];
            $data['tickets'][] = [
                'id'        => count($data['tickets']) + 1,
                'id_client' => $_SESSION['user']['id'],
                'montant'   => $diff,
                'date'      => date('d/m/Y'),
                'utilise'   => false
            ];
        }

        $found = true;
        break;
    }
}
unset($cmd);

if (!$found) { echo json_encode(['success' => false, 'message' => 'Commande introuvable']); exit(); }

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['success' => true, 'message' => 'Commande modifiée']);
