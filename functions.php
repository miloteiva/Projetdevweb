<?php
/* ========================================================
   functions.php - Bibliothèque de code PHP commune
   À inclure dans les pages PHP et endpoints API
   Bonne pratique Phase 4 : éviter la duplication
======================================================== */

/**
 * Ajoute une entrée dans le journal d'incidents (logs)
 * @param array  $data  Le tableau data.json déjà décodé
 * @param string $type  Type de log (connexion_ok, connexion_echec, blocage_user, etc.)
 * @param string $msg   Message descriptif
 * @param int|null $uid ID de l'utilisateur concerné (optionnel)
 * @return array Le tableau modifié à réenregistrer
 */
function addLog($data, $type, $msg, $uid = null) {
    if (!isset($data['logs'])) $data['logs'] = [];
    if (!isset($data['id_counters'])) $data['id_counters'] = [];
    if (!isset($data['id_counters']['log'])) $data['id_counters']['log'] = 0;

    $data['id_counters']['log']++;
    $data['logs'][] = [
        'id'      => $data['id_counters']['log'],
        'date'    => date('d/m/Y H:i:s'),
        'type'    => $type,
        'message' => $msg,
        'user_id' => $uid,
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? '',
        'ua'      => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 100)
    ];
    // Limiter à 500 logs pour éviter que le fichier devienne énorme
    if (count($data['logs']) > 500) {
        $data['logs'] = array_slice($data['logs'], -500);
    }
    return $data;
}

/**
 * Charge data.json depuis le bon chemin (fonctionne depuis racine ou /api/)
 */
function loadData($pathToData = null) {
    if ($pathToData === null) {
        $pathToData = file_exists('data.json') ? 'data.json' : '../data.json';
    }
    $content = @file_get_contents($pathToData);
    if ($content === false) return null;
    return json_decode($content, true);
}

/**
 * Sauvegarde data.json
 */
function saveData($data, $pathToData = null) {
    if ($pathToData === null) {
        $pathToData = file_exists('data.json') ? 'data.json' : '../data.json';
    }
    return file_put_contents(
        $pathToData,
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

/**
 * Vérifie qu'un utilisateur est connecté avec un rôle donné
 * Redirige vers connection.php sinon
 */
function requireRole($role) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== $role) {
        header("Location: connection.php");
        exit();
    }
}

/**
 * Vérifie l'algorithme de Luhn pour les numéros de carte bancaire
 * @param string $numCarte numéro brut (peut contenir espaces)
 * @return bool true si valide
 */
function verifierLuhn($numCarte) {
    $num = preg_replace('/\s+/', '', $numCarte);
    if (!preg_match('/^\d{13,19}$/', $num)) return false;
    $sum = 0; $alt = false;
    for ($i = strlen($num) - 1; $i >= 0; $i--) {
        $d = (int)$num[$i];
        if ($alt) { $d *= 2; if ($d > 9) $d -= 9; }
        $sum += $d;
        $alt = !$alt;
    }
    return $sum % 10 === 0;
}

/**
 * Échappe une chaîne pour affichage HTML
 */
function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}
