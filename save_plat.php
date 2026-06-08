<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['restaurateur','admin'])) {
    echo json_encode(['success'=>false,'message'=>'Accès refusé']); exit();
}
$input = json_decode(file_get_contents('php://input'), true);
$id          = isset($input['id']) && $input['id'] !== null ? intval($input['id']) : null;
$nom         = trim($input['nom'] ?? '');
$categorie   = $input['categorie'] ?? '';
$desc        = trim($input['desc'] ?? '');
$ingredients = trim($input['ingredients'] ?? '');
$prix        = floatval($input['prix'] ?? 0);
$allergenes  = trim($input['allergenes'] ?? '');
$tags        = is_array($input['tags']) ? $input['tags'] : [];
$cats = ['Préludes','Cœurs de Fête','Douceurs'];
if (strlen($nom)<2) { echo json_encode(['success'=>false,'message'=>'Nom trop court']); exit(); }
if (!in_array($categorie,$cats)) { echo json_encode(['success'=>false,'message'=>'Catégorie invalide']); exit(); }
if ($prix <= 0) { echo json_encode(['success'=>false,'message'=>'Prix invalide']); exit(); }
$validTags = ['vegetarien','vegan','halal','sans-gluten','epice','sucre'];
$tags = array_values(array_intersect($tags, $validTags));
$file = '../data.json';
$data = json_decode(file_get_contents($file), true);
if ($id === null) {
    $newId = (isset($data['id_counters']['plat']) ? $data['id_counters']['plat'] : count($data['plats'])) + 1;
    $data['id_counters']['plat'] = $newId;
    $data['plats'][] = ['id'=>$newId,'categorie'=>$categorie,'nom'=>$nom,'allergenes'=>$allergenes,'desc'=>$desc,'ingredients'=>$ingredients,'prix'=>$prix,'tags'=>$tags,'nb_commandes'=>0,'actif'=>true];
    $msg = 'Plat créé avec succès';
} else {
    $found = false;
    foreach ($data['plats'] as &$p) {
        if ($p['id'] === $id) { $p['nom']=$nom; $p['categorie']=$categorie; $p['desc']=$desc; $p['ingredients']=$ingredients; $p['prix']=$prix; $p['allergenes']=$allergenes; $p['tags']=$tags; $found=true; break; }
    } unset($p);
    if (!$found) { echo json_encode(['success'=>false,'message'=>'Plat introuvable']); exit(); }
    $msg = 'Plat modifié avec succès';
}
file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode(['success'=>true,'message'=>$msg]);
