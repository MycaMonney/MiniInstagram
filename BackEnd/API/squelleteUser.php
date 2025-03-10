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
        $user = recupererDonneesJson();
        $user = filtrerUser($user);
        $verifUser = verifierUser($user);

        if (is_array($verifUser)) {
            envoyerDonnees($verifUser, STATUS_HTTP_NON_AUTORISE);
        }

        InsererUser($user['username'], $user['urlPdP']);
        envoyerDonnees($user, STATUS_HTTP_OK);
        break;

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
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id === null || $id === false) {
            envoyerDonnees(['Erreur' => 'ID non trouvé'], STATUS_HTTP_NON_TROUVE);
        }

        $user = RecupererDonneesUserParID($id);
        if ($user === false) {
            envoyerDonnees(['Erreur' => 'User non trouvé'], STATUS_HTTP_NON_TROUVE);
        }

        SupprimerUser($id);
        envoyerDonnees(['message' => "Suppression effectuée."], STATUS_HTTP_OK);
        break;

    default:
        http_response_code(405);
        header("Content-type: application/json; charset=utf-8");
        echo json_encode(["error" => "Méthode invalide"]);
        break;
}
