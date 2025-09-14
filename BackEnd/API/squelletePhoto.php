<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        
            if (!$photo || !isset($photo['user_token']) || !isset($photo['photo_url'])) {
                envoyerDonnees(["success" => false, "message" => "Données invalides"], STATUS_HTTP_MAUVAISE_REQUETE);
                exit;
            }

            $user = DecoderJWT($photo['user_token']);
            if (!$user || !isset($user['id'])) {
                envoyerDonnees(["success" => false, "message" => "Token invalide ou expiré"], STATUS_HTTP_NON_AUTORISE);
                exit;
            }

            $photo['user_id'] = $user['id'];

            //Si l’image est en base64, on la transforme en fichier
            $imageBase64 = $photo['photo_url'];
            if (preg_match('/^data:image\/(\w+);base64,/', $imageBase64, $type)) {
                $imageBase64 = substr($imageBase64, strpos($imageBase64, ',') + 1);
                $type = strtolower($type[1]); // png, jpeg...
        
                $imageData = base64_decode($imageBase64);
                if ($imageData === false) {
                    envoyerDonnees(["success" => false, "message" => "Échec du décodage de l'image."], STATUS_HTTP_MAUVAISE_REQUETE);
                    exit;
                }
           
                //Création du chemin pour enregistrer l'image
                $nomFichier = uniqid('photo_') . '.' . $type;
                $cheminDossier = __DIR__ . '/../uploads/';
                $cheminComplet = $cheminDossier . $nomFichier;
                $cheminRelatif = 'https://10.5.57.106/GitHub/MiniInstagram/BackEnd/uploads/' . $nomFichier; // URL complète

                //Crée le dossier s'il n'existe pas
                if (!is_dir($cheminDossier)) {
                    mkdir($cheminDossier, 0777, true);
                }

                //Sauvegarde l'image sur le serveur
                file_put_contents($cheminComplet, $imageData);

                //Remplace le base64 par le chemin relatif
                $photo['photo_url'] = $cheminRelatif;
            }
        
            //Vérifie et filtre les données
            $photo = filtrerPhoto($photo);
            $verifPhoto = verifierPhoto($photo);
        
            if (is_array($verifPhoto)) {
                envoyerDonnees(["success" => false, "message" => "Vérification échouée", "details" => $verifPhoto], STATUS_HTTP_NON_AUTORISE);
                exit;
            }
        
            //Insertion dans la  BDD
            InsererPhoto($user['id'], $photo['photo_url']);
        
            // Réponse
            echo json_encode([
                "success" => true,
                "message" => "Photo ajoutée",
                "photo" => $photo
            ]);
            exit;
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
        envoyerDonnees(["success" => false, "message" => "Méthode invalide"], 405);
        exit;
    }
