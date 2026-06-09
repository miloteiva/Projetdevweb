<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['restaurateur','admin'])) {
    echo json_encode(['success'=>false,'message'=>'Accès refusé']); exit();
}
$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false,'message'=>'ID manquant']); exit(); }
$file = '../data.json';
$data = json_decode(file_get_contents($file), true);
$found = false;
foreach ($data['plats'] as &$p) {
    if ($p['id'] === $id) { $p['actif'] = empty($p['actif']); $found=true; break; }
} unset($p);
if (!$found) { echo json_encode(['success'=>false,'message'=>'Plat introuvable']); exit(); }
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode(['success'=>true]);
