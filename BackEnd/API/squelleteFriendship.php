<?php

// Allow request from any origin
header("Access-Control-Allow-Origin: *");

// Allow requests with usual methods and Content-Type header
header("Access-Control-Allow-Methods: GET, PUT, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Set content type to JSON
header("Content-Type: application/json");

// Import
require_once('../PHP/connexionBase.php');
require_once('../API/functionsApi.php');
require_once('../API/constantesApi.php');
// Import

$typeRequete = $_SERVER['REQUEST_METHOD'];

switch ($typeRequete) {
    case 'GET':
        // If there is no id, return all friendships (HTTP code, header, and data)
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id === null) {
            $friendships = RecupererDonneesFriendships();
            envoyerDonnees($friendships, STATUS_HTTP_OK);
        }

        // If there is an id, retrieve friendships for that user
        if ($id !== null) {
            $friendships = RecupererInfosAmisParIDUser($id);
            if ($friendships === false) {
                envoyerDonnees(['Erreur' => 'Aucune amitié trouvée pour cet utilisateur'], STATUS_HTTP_NON_TROUVE);
            }
            envoyerDonnees($friendships, STATUS_HTTP_OK);
        }
        break;

    case 'POST':
        $friendship = recupererDonneesJson();

        // Décodage du token pour obtenir l'utilisateur actuel
        $user = DecoderJWT($friendship['actual_user_token']);

        if (!$user || !isset($user['id'])) {
            envoyerDonnees(["success" => false, "message" => "Token invalide."], STATUS_HTTP_NON_AUTORISE);
            exit;
        }

        // Ajouter user_id_1 depuis le token
        $user_id_1 = $user['id'];
        $user_id_2 = $friendship['user_id_2'];

        InsererFriendship($user_id_1, $user_id_2);

        envoyerDonnees(["success" => true, "message" => "Amitié ajoutée."], STATUS_HTTP_OK);
        break;

    case 'DELETE':
        // Implement the logic for handling DELETE requests
        $userId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
        $friendId = filter_input(INPUT_GET, 'friend_id', FILTER_VALIDATE_INT);

        if ($userId === null || $friendId === null || $userId === false || $friendId === false) {
            envoyerDonnees(['Erreur' => 'IDs non valides'], STATUS_HTTP_NON_TROUVE);
        }

        $friendship = RecupererFriendshipsParIDUser($userId);

        if ($friendship === false) {
            envoyerDonnees(['Erreur' => 'Amitié non trouvée'], STATUS_HTTP_NON_TROUVE);
        }

        // Supprimer la relation d'amitié entre les deux utilisateurs
        SupprimerFriendship($userId, $friendId);
        envoyerDonnees(['message' => "Amitié supprimée avec succès"], STATUS_HTTP_OK);
        break;

    default:
        // Invalid method
        http_response_code(405);
        header("Content-type: application/json; charset=utf-8");
        echo json_encode(["error" => "Méthode non autorisée"]);
        break;
}
