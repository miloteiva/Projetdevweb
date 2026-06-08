<?php
session_start();
header('Content-Type: application/json');
$data = json_decode(file_get_contents('../data.json'), true);
$plats = array_filter($data['plats'], fn($p) => !empty($p['actif']));
$entrees  = array_values(array_filter($plats, fn($p)=>$p['categorie']==='Préludes'));
$mains    = array_values(array_filter($plats, fn($p)=>$p['categorie']==='Cœurs de Fête'));
$desserts = array_values(array_filter($plats, fn($p)=>$p['categorie']==='Douceurs'));
if (!$entrees||!$mains||!$desserts) { echo json_encode(['success'=>false,'message'=>'Carte incomplète']); exit(); }
function pick($a){return $a[array_rand($a)];}
$choice=['entree'=>pick($entrees),'plat'=>pick($mains),'dessert'=>pick($desserts)];
$choice['total']=$choice['entree']['prix']+$choice['plat']['prix']+$choice['dessert']['prix'];
$alts=[];
for($i=0;$i<6;$i++){$a=['entree'=>pick($entrees),'plat'=>pick($mains),'dessert'=>pick($desserts)];$a['total']=$a['entree']['prix']+$a['plat']['prix']+$a['dessert']['prix'];$alts[]=$a;}
echo json_encode(['success'=>true,'choice'=>$choice,'alternatives'=>$alts]);
