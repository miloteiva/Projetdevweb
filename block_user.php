<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/functions.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit();
}

$input  = json_decode(file_get_contents('php://input'), true);
$userId = intval($input['user_id'] ?? 0);
$block  = (bool)($input['block'] ?? false);

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit();
}

// Empêcher de se bloquer soi-même
if ($userId === $_SESSION['user']['id']) {
    echo json_encode(['success' => false, 'message' => 'Vous ne pouvez pas vous bloquer vous-même']);
    exit();
}

$data = loadData();
$found = false;
$emailCible = '';

foreach ($data['users'] as &$u) {
    if ($u['id'] === $userId) {
        $u['bloque'] = $block;
        $emailCible = $u['email'];
        $found = true;
        break;
    }
}
unset($u);

if (!$found) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']);
    exit();
}

// PHASE 4 : Log de l'incident (blocage/déblocage)
$action = $block ? 'blocage_user' : 'deblocage_user';
$msg = ($block ? 'Compte bloqué' : 'Compte débloqué') . " : $emailCible (par admin #" . $_SESSION['user']['id'] . ')';
$data = addLog($data, $action, $msg, $userId);

saveData($data);

echo json_encode([
    'success' => true,
    'message' => $block ? 'Utilisateur bloqué' : 'Utilisateur débloqué',
    'blocked' => $block
]);
