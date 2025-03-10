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
            $photo = RecupererDonneesPhotos();
            envoyerDonnees($photo, STATUS_HTTP_OK);
        } else {
            $photo = RecupererDonneesPhotoParIDUser($id);
            if ($photo === false) {
                envoyerDonnees(['Erreur' => 'ID non trouvé'], STATUS_HTTP_NON_TROUVE);
            }
            envoyerDonnees($photo, STATUS_HTTP_OK);
        }
        break;

    case 'POST':
        $photo = recupererDonneesJson();
        $photo = filtrerPhoto($photo);
        $verifPhoto = verifierPhoto($photo);

        if (is_array($verifPhoto)) {
            envoyerDonnees($verifPhoto, STATUS_HTTP_NON_AUTORISE);
        }

        InsererPhoto($photo['userId'], $photo['url']);
        envoyerDonnees($photo, STATUS_HTTP_OK);
        break;

    case 'PUT':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id === null || $id === false) {
            envoyerDonnees(['Erreur' => 'ID non trouvé'], STATUS_HTTP_NON_TROUVE);
        }

        $photo = RecupererDonneesPhotoParIDUser($id);
        if ($photo === false) {
            envoyerDonnees(['Erreur' => 'Photo non trouvée'], STATUS_HTTP_NON_TROUVE);
        }

        $photo = recupererDonneesJson();
        $photo = filtrerPhoto($photo);
        $verifPhoto = verifierPhoto($photo);

        if (is_array($verifPhoto)) {
            envoyerDonnees($verifPhoto, STATUS_HTTP_MAUVAISE_REQUETE);
        }

        ModifierPhoto($id, $photo[''], $photo['url']);
        envoyerDonnees(['message' => "Modification effectuée."], STATUS_HTTP_OK);
        break;

    case 'DELETE':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if ($id === null || $id === false) {
            envoyerDonnees(['Erreur' => 'ID non trouvé'], STATUS_HTTP_NON_TROUVE);
        }

        $photo = RecupererDonneesPhotoParIDUser($id);
        if ($photo === false) {
            envoyerDonnees(['Erreur' => 'Photo non trouvée'], STATUS_HTTP_NON_TROUVE);
        }

        SupprimerPhoto($id);
        envoyerDonnees(['message' => "Suppression effectuée."], STATUS_HTTP_OK);
        break;

    default:
        http_response_code(405);
        header("Content-type: application/json; charset=utf-8");
        echo json_encode(["error" => "Méthode invalide"]);
        break;
}
