<?php
require_once '../PHP/connexionBase.php';
require_once '../API/functionsApi.php';
require_once '../API/constantesApi.php';

// Allow request from any origin
header("Access-Control-Allow-Origin: *");
// Allow requests with usual methods and Content-Type header
header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// Set content type to JSON
header("Content-Type: application/json");

$typeRequete = $_SERVER['REQUEST_METHOD'];

switch ($typeRequete) {
    case 'GET':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id === null) {
            $user = RecupererDonneesUsers();
            envoyerDonnees($user, STATUS_HTTP_OK);
        } else {
            $user = RecupererDonneesUserParID($id);
            if ($user === false) {
                envoyerDonnees(['Erreur' => 'ID non trouvé'], STATUS_HTTP_NON_TROUVE);
            }
            envoyerDonnees($user, STATUS_HTTP_OK);
        }
        break;

    case 'POST':
        $pdo = connexionBdd();
        $user = recupererDonneesJson();

        //Connexion de l'utilisateur
        if (isset($user['action']) && $user['action'] === "login") {
            $foundUser = RecupererDonneesUserParNom($user['username']);

            if ($foundUser && password_verify($user['mdp'], $foundUser['mdp'])) {

                $token = GenererJWT($foundUser['idUser'], $foundUser['username']);

                envoyerDonnees([
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'token' => $token,
                    'user' => $foundUser
                ], STATUS_HTTP_OK);
            } else {
                envoyerDonnees(['error' => 'Identifiants invalides'], STATUS_HTTP_NON_AUTORISE);
            }
            exit;
        }

        //Vérification et insertion d'un nouvel utilisateur
        if (!isset($user["username"])|| !isset($user["mdp"]) || !isset($user["urlPdP"])) {
            envoyerDonnees(['Erreur' => 'Données incomplètes'], STATUS_HTTP_MAUVAISE_REQUETE);
            exit;
        }

        try {
            $passWordHash = password_hash($user["mdp"], PASSWORD_DEFAULT);
            $userId = InsererUser($user['username'], $passWordHash, $user['urlPdP']);
            envoyerDonnees([
                "success" => true,
                "message" => "Utilisateur ajouté avec succès",
                "idUser" => $userId
            ], STATUS_HTTP_OK);
        } catch (PDOException $e) {
            envoyerDonnees([
                "Erreur" => "Problème lors de l'ajout en BDD",
                "details" => $e->getMessage()
            ], STATUS_HTTP_ERREUR_DU_SERVEUR);
        }

        exit;

    case 'PUT':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id === null || $id === false) {
            envoyerDonnees(['Erreur' => 'ID non trouvé'], STATUS_HTTP_NON_TROUVE);
        }

        $user = RecupererDonneesUserParID($id);
        if ($user === false) {
            envoyerDonnees(['Erreur' => 'User non trouvé'], STATUS_HTTP_NON_TROUVE);
        }

        $user = recupererDonneesJson();
        $user = filtrerUser($user);
        $verifUser = verifierUser($user);

        if (is_array($verifUser)) {
            envoyerDonnees($verifUser, STATUS_HTTP_MAUVAISE_REQUETE);
        }

        ModifierUser($id, $user['username'], $user['urlPdP']);
        envoyerDonnees(['message' => "Modification effectuée."], STATUS_HTTP_OK);
        break;

    case 'DELETE':
        // Récupération de l'ID utilisateur depuis l'URL
        $id = filter_input(INPUT_GET, 'idUser', FILTER_VALIDATE_INT);

        if (!$id) {
            envoyerDonnees(['error' => 'ID utilisateur invalide ou manquant'], STATUS_HTTP_NON_TROUVE);
        }

        $user = RecupererDonneesUserParID($id);
        if (!$user) {
            envoyerDonnees(['error' => 'Utilisateur non trouvé'], STATUS_HTTP_NON_TROUVE);
        }

        //Suppression de l'utilisateur
        SupprimerUser($id);
        envoyerDonnees(['message' => "Utilisateur supprimé avec succès"], STATUS_HTTP_OK);
        break;

    default:
        http_response_code(405);
        header("Content-type: application/json; charset=utf-8");
        echo json_encode(["error" => "Méthode invalide"]);
        break;
}
