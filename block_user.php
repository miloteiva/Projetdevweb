<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = intval($input['user_id'] ?? 0);
$block  = (bool)($input['block'] ?? false);

if (!$userId) { echo json_encode(['success' => false, 'message' => 'ID manquant']); exit(); }

// Empêcher de se bloquer soi-même
if ($userId === $_SESSION['user']['id']) {
    echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas vous bloquer vous-même']);
    exit();
}

$file = '../data.json';
$data = json_decode(file_get_contents($file), true);
$found = false;

foreach ($data['users'] as &$u) {
    if ($u['id'] === $userId) {
        $u['bloque'] = $block;
        $found = true;
        break;
    }
}
unset($u);

if (!$found) { echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']); exit(); }

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode([
    'success' => true,
    'message' => $block ? 'Utilisateur bloqué' : 'Utilisateur débloqué',
    'blocked' => $block
]);
