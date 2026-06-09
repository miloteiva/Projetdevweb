<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user'])||$_SESSION['user']['role']!=='client') {
    echo json_encode(['success'=>false,'message'=>'Accès refusé']); exit();
}
$input = json_decode(file_get_contents('php://input'), true);
$orderId = intval($input['order_id'] ?? 0);
$data = json_decode(file_get_contents('../data.json'), true);
$orig = null;
foreach ($data['commandes'] as $c) {
    if ($c['id']===$orderId && $c['id_client']===$_SESSION['user']['id']) { $orig=$c; break; }
}
if (!$orig) { echo json_encode(['success'=>false,'message'=>'Commande introuvable']); exit(); }
$_SESSION['panier'] = [];
$added=0; $skipped=0;
foreach ($orig['articles'] as $a) {
    $found=null;
    foreach ($data['plats'] as $p) { if ($p['nom']===$a['nom'] && !empty($p['actif'])) { $found=$p; break; } }
    if (!$found) { $skipped++; continue; }
    $cle='plat_'.$found['id'].'_re'.$orderId;
    $_SESSION['panier'][$cle]=['nom'=>$found['nom'],'prix'=>$found['prix'],'quantite'=>$a['quantite'],'type'=>'plat'];
    $added++;
}
echo json_encode(['success'=>true,'added'=>$added,'skipped'=>$skipped,
    'message'=>$added>0?"$added article(s) ajouté(s) au panier".($skipped?" ($skipped indisponible(s))":''):'Aucun article disponible']);
