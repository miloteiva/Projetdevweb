<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'client') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit();
}

$input    = json_decode(file_get_contents('php://input'), true);
$orderId  = intval($input['order_id'] ?? 0);
$produit  = intval($input['note_produit'] ?? 0);
$livr     = intval($input['note_livraison'] ?? 0);
$comment  = trim($input['commentaire'] ?? '');

if (!$orderId || $produit < 1 || $produit > 5) {
    echo json_encode(['success' => false, 'message' => 'Note produit invalide']);
    exit();
}

$file = '../data.json';
$data = json_decode(file_get_contents($file), true);
$found = false;

foreach ($data['commandes'] as &$cmd) {
    if ($cmd['id'] === $orderId) {
        if ($cmd['id_client'] !== $_SESSION['user']['id']) {
            echo json_encode(['success' => false, 'message' => 'Commande non-vôtre']);
            exit();
        }
        if (!empty($cmd['note'])) {
            echo json_encode(['success' => false, 'message' => 'Vous avez déjà noté cette commande']);
            exit();
        }
        $cmd['note']  = true;
        $cmd['notes'] = [
            'produit'     => $produit,
            'livraison'   => $livr,
            'commentaire' => htmlspecialchars($comment),
            'date_note'   => date('d/m/Y H:i')
        ];
        $found = true;
        break;
    }
}
unset($cmd);

if (!$found) { echo json_encode(['success' => false, 'message' => 'Commande introuvable']); exit(); }

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['success' => true, 'message' => 'Merci pour votre avis !']);
