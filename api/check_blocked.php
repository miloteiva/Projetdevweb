<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['blocked' => false]);
    exit();
}

$data = json_decode(file_get_contents('../data.json'), true);
$userId = $_SESSION['user']['id'];

foreach ($data['users'] as $u) {
    if ($u['id'] === $userId) {
        if (!empty($u['bloque'])) {
            // Conformément à la phase 3 : session terminée sur-le-champ
            session_destroy();
            echo json_encode(['blocked' => true]);
            exit();
        }
        break;
    }
}

echo json_encode(['blocked' => false]);
