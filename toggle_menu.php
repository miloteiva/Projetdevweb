<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['restaurateur','admin'])) {
    echo json_encode(['success'=>false,'message'=>'Accès refusé']); exit();
}
$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
$file = '../data.json';
$data = json_decode(file_get_contents($file), true);
foreach ($data['menus'] as &$m) { if ($m['id']===$id) { $m['actif']=empty($m['actif']); break; } } unset($m);
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode(['success'=>true]);
