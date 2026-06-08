<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) ||
    !in_array($_SESSION['user']['role'], ['restaurateur', 'admin', 'livreur'])) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit();
}

$input     = json_decode(file_get_contents('php://input'), true);
$orderId   = intval($input['order_id'] ?? 0);
$status    = $input['status'] ?? '';
$livreurId = isset($input['livreur_id']) ? intval($input['livreur_id']) : null;

$statutsValides = ['Payée', 'En préparation', 'Prête', 'En livraison', 'Livrée', 'Abandonnée'];

if (!$orderId || !in_array($status, $statutsValides)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit();
}

$file = '../data.json';
$data = json_decode(file_get_contents($file), true);
if (!isset($data['commandes'])) $data['commandes'] = [];

$role = $_SESSION['user']['role'];
$userId = $_SESSION['user']['id'];

$found = false;
$ancienStatut = null;
foreach ($data['commandes'] as &$cmd) {
    if ($cmd['id'] === $orderId) {
        // SÉCURITÉ : un livreur ne peut modifier QUE ses propres commandes
        if ($role === 'livreur') {
            if (!isset($cmd['id_livreur']) || (int)$cmd['id_livreur'] !== (int)$userId) {
                echo json_encode(['success' => false, 'message' => 'Cette commande ne vous est pas assignée']);
                exit();
            }
            // Un livreur ne peut passer qu'à "Abandonnée" ou "Livrée"
            if (!in_array($status, ['Abandonnée', 'Livrée'])) {
                echo json_encode(['success' => false, 'message' => 'Action non autorisée pour un livreur']);
                exit();
            }
        }

        $ancienStatut = $cmd['statut'];
        $cmd['statut'] = $status;
        if ($livreurId !== null) $cmd['id_livreur'] = $livreurId;
        // Si on abandonne, on libère le livreur
        if ($status === 'Abandonnée' && $role === 'livreur') {
            // On garde l'id_livreur pour traçabilité mais on note l'abandon
            $cmd['abandonnee_par'] = $userId;
            $cmd['heure_abandon'] = date('H:i');
        }
        $found = true;
        break;
    }
}
unset($cmd);

if (!$found) {
    echo json_encode(['success' => false, 'message' => 'Commande introuvable']);
    exit();
}

// Log de l'incident (Phase 4)
if (!isset($data['logs'])) $data['logs'] = [];
if (!isset($data['id_counters']['log'])) $data['id_counters']['log'] = 0;
$data['id_counters']['log']++;
$data['logs'][] = [
    'id'      => $data['id_counters']['log'],
    'date'    => date('d/m/Y H:i:s'),
    'type'    => $status === 'Abandonnée' ? 'order_abandoned' : ($status === 'Livrée' ? 'order_delivered' : 'order_status'),
    'message' => "Commande #$orderId : $ancienStatut → $status",
    'user_id' => $userId,
    'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
    'ua'      => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 100)
];
if (count($data['logs']) > 500) $data['logs'] = array_slice($data['logs'], -500);

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo json_encode(['success' => true, 'message' => 'Statut mis à jour : ' . $status]);
