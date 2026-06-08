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
// Si commandé : désactiver seulement
$platNom = null;
foreach ($data['plats'] as $p) { if ($p['id']===$id) { $platNom=$p['nom']; break; } }
$wasOrdered = false;
foreach ($data['commandes'] as $c) {
    foreach ($c['articles']??[] as $a) { if (($a['nom']??'')===$platNom) { $wasOrdered=true; break 2; } }
}
if ($wasOrdered) {
    foreach ($data['plats'] as &$p) { if ($p['id']===$id) { $p['actif']=false; break; } } unset($p);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    echo json_encode(['success'=>true,'message'=>'Plat désactivé (déjà commandé, suppression impossible)']); exit();
}
$data['plats'] = array_values(array_filter($data['plats'], fn($p)=>$p['id']!==$id));
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode(['success'=>true,'message'=>'Plat supprimé']);
