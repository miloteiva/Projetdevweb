<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('../data.json'), true);
$plats = $data['plats'];

$categorie = $_GET['categorie'] ?? 'all';
$regimes   = $_GET['regime'] ?? [];
$gouts     = $_GET['gout'] ?? [];

// Mapping catégories du front vers les catégories réelles
$mapCategorie = [
    'all' => null,
    'entrees'  => 'Préludes',
    'plats'    => 'Cœurs de Fête',
    'desserts' => 'Douceurs'
];

$result = array_filter($plats, function($p) use ($categorie, $regimes, $gouts, $mapCategorie) {
    // Filtre catégorie
    if ($categorie !== 'all' && isset($mapCategorie[$categorie])
        && $p['categorie'] !== $mapCategorie[$categorie]) {
        return false;
    }

    // Filtres régime : doit POSSÉDER tous les tags cochés
    foreach ($regimes as $r) {
        if (!in_array($r, $p['tags'] ?? [])) return false;
    }

    // Filtres goût : doit posséder au moins UN des goûts cochés (si goûts cochés)
    if (!empty($gouts)) {
        $match = false;
        foreach ($gouts as $g) {
            if (in_array($g, $p['tags'] ?? [])) { $match = true; break; }
        }
        if (!$match) return false;
    }

    return true;
});

echo json_encode(['success' => true, 'plats' => array_values($result)]);
