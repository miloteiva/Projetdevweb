<?php
session_start();
if(!isset($_SESSION['user']) || empty($_SESSION['panier'])) { header("Location: menu.php"); exit(); }

// Simulation du succès de l'API CYBank [cite: 109]
$paiement_reussi = true; 

if($paiement_reussi) {
    $file = 'data.json';
    $data = json_decode(file_get_contents($file), true);
    
    // Initialisation de la clé commandes si inexistante [cite: 63]
    if(!isset($data['commandes'])) { $data['commandes'] = []; }

    $nouvelleCmd = [
        "id" => count($data['commandes']) + 1,
        "id_client" => $_SESSION['user']['id'],
        "client" => $_SESSION['user']['nom'] . " " . $_SESSION['user']['prenom'],
        "articles" => array_values($_SESSION['panier']),
        "total" => array_reduce($_SESSION['panier'], function($sum, $i){ return $sum + ($i['prix']*$i['quantite']); }, 0),
        "statut" => "À préparer", [cite: 50, 102]
        "heure" => date('H:i'),
        "date" => date('d/m/Y')
    ];

    $data['commandes'][] = $nouvelleCmd;
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    
    unset($_SESSION['panier']);
    header("Location: moncompte.php?commande_reussie=1");
    exit();
}
