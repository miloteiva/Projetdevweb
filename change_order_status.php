<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) ||
    !in_array($_SESSION['user']['role'], ['restaurateur', 'admin', 'livreur'])) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit();
}

$input    = json_decode(file_get_contents('php://input'), true);
$orderId  = intval($input['order_id'] ?? 0);
$status   = $input['status'] ?? '';
$livreurId = isset($input['livreur_id']) ? intval($input['livreur_id']) : null;

$statutsValides = ['Payée', 'En préparation', 'Prête', 'En livraison', 'Livrée', 'Abandonnée'];

if (!$orderId || !in_array($status, $statutsValides)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit();
}

$file = '../data.json';
$data = json_decode(file_get_contents($file), true);
if (!isset($data['commandes'])) $data['commandes'] = [];

$found = false;
foreach ($data['commandes'] as &$cmd) {
    if ($cmd['id'] === $orderId) {
        $cmd['statut'] = $status;
        if ($livreurId !== null) $cmd['id_livreur'] = $livreurId;
        $found = true;
        break;
    }
}
unset($cmd);

if (!$found) { echo json_encode(['success' => false, 'message' => 'Commande introuvable']); exit(); }

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['success' => true, 'message' => 'Statut mis à jour : ' . $status]);
