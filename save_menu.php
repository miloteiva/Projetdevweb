<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['restaurateur','admin'])) {
    echo json_encode(['success'=>false,'message'=>'Accès refusé']); exit();
}
$input = json_decode(file_get_contents('php://input'), true);
$id  = isset($input['id']) && $input['id'] !== null ? intval($input['id']) : null;
$nom = trim($input['nom'] ?? '');
$desc= trim($input['desc'] ?? '');
$prix= floatval($input['prix'] ?? 0);
$nbs = intval($input['nb_services'] ?? 3);
if (strlen($nom)<2||$prix<=0||!in_array($nbs,[3,5,7])) { echo json_encode(['success'=>false,'message'=>'Données invalides']); exit(); }
$structs=[3=>'Entrée + Plat + Dessert',5=>'Amuse-bouche + Entrée + Plat + Trou normand + Dessert',7=>'Mise en bouche + Entrée froide + Entrée chaude + Sorbet + Plat + Douceurs + Mignardises'];
$file = '../data.json';
$data = json_decode(file_get_contents($file), true);
if ($id===null) {
    $newId = (isset($data['id_counters']['menu']) ? $data['id_counters']['menu'] : count($data['menus'])) + 1;
    $data['id_counters']['menu'] = $newId;
    $data['menus'][] = ['id'=>$newId,'nom'=>$nom,'desc'=>$desc,'prix'=>$prix,'nb_services'=>$nbs,'structure'=>$structs[$nbs],'actif'=>true];
} else {
    $found=false;
    foreach ($data['menus'] as &$m) { if ($m['id']===$id) { $m['nom']=$nom;$m['desc']=$desc;$m['prix']=$prix;$m['nb_services']=$nbs;$m['structure']=$structs[$nbs];$found=true;break; } } unset($m);
    if (!$found) { echo json_encode(['success'=>false,'message'=>'Menu introuvable']); exit(); }
}
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode(['success'=>true,'message'=>'Menu enregistré']);
