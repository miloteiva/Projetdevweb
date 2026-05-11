<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { echo json_encode(['success' => false, 'message' => 'Données invalides']); exit(); }

// Validation côté serveur (toujours obligatoire, même si JS le fait côté client)
$nom       = trim($input['nom'] ?? '');
$prenom    = trim($input['prenom'] ?? '');
$email     = filter_var(trim($input['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$telephone = trim($input['telephone'] ?? '');
$adresse   = trim($input['adresse'] ?? '');

$errors = [];
if (strlen($nom) < 2 || strlen($nom) > 50)       $errors[] = 'Nom invalide';
if (strlen($prenom) < 2 || strlen($prenom) > 50) $errors[] = 'Prénom invalide';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $errors[] = 'Email invalide';
if (strlen($telephone) > 0 && !preg_match('/^(?:(?:\+33|0)[1-9])(?:[\s.-]?\d{2}){4}$/', $telephone))
    $errors[] = 'Téléphone invalide';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit();
}

$file = '../data.json';
$data = json_decode(file_get_contents($file), true);
$userId = $_SESSION['user']['id'];
$updated = false;

foreach ($data['users'] as &$user) {
    if ($user['id'] === $userId) {
        // Vérifier que le nouvel email n'est pas pris par un autre user
        if ($user['email'] !== $email) {
            foreach ($data['users'] as $u) {
                if ($u['id'] !== $userId && $u['email'] === $email) {
                    echo json_encode(['success' => false, 'message' => 'Email déjà utilisé']);
                    exit();
                }
            }
        }
        $user['nom']       = htmlspecialchars($nom);
        $user['prenom']    = htmlspecialchars($prenom);
        $user['email']     = $email;
        $user['telephone'] = htmlspecialchars($telephone);
        $user['adresse']   = htmlspecialchars($adresse);
        $_SESSION['user']  = $user;
        $updated = true;
        break;
    }
}
unset($user);

if (!$updated) { echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable']); exit(); }

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['success' => true, 'message' => 'Profil mis à jour']);
