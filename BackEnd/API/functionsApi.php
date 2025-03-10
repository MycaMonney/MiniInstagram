<?php
require_once '../PHP/connexionBase.php';

/* ------------------------------ USERS ------------------------------*/
/**
 * Retourne tous les users
 * 
 * @return array Un tableau de users
 */
function RecupererDonneesUsers(): array
{
    $pdo = connexionBdd();

    $sql = "SELECT * FROM Users";

    $statement = $pdo->prepare($sql);

    $statement->execute();

    return $statement->fetchAll();
}

/**
 * Retourne le user en fonction de l'id demandé
 * @return false Le user n'existe pas
 * @return array Un tableau de user
 */

function RecupererDonneesUserParID(int $idUser): array|false
{
    $pdo = connexionBdd();

    $sql = "SELECT * FROM Users WHERE idUser = :idUser";

    $statement = $pdo->prepare($sql);

    $statement->execute([
        ':idUser' => $idUser,
    ]);

    return $statement->fetch();
}

/**
 * Insert les donées dans la base
 * @return int L'id du User ajouté
 */
function InsererUser(string $username, string $urlPdP): int
{
    $pdo = connexionBdd();

    $sql = 'INSERT INTO Users (username, urlPdP) VALUES (:USER_NAME, :URL_PDP)';
    $statement = $pdo->prepare($sql);
    $statement->execute([
        ':USER_NAME' => $username,
        ':URL_PDP' => $urlPdP,
    ]);

    return (int) $pdo->lastInsertId();
}


/**
 * Modifie les informations de la bdd
 * @return void Avec les informations
 */
function ModifierUser(int $idUser, string $username, string $urlPdP): void
{
    $pdo = connexionBdd();

    $sql = "UPDATE Users SET username = :USER_NAME, urlPdP = :URL_PDP WHERE idUser = :idUser";

    $statement = $pdo->prepare($sql);

    $statement->execute([
        ':idUser' => $idUser,
        ':USER_NAME' => $username,
        ':URL_PDP' => $urlPdP,
    ]);

}

/**
 * Supprime le user dont l'id est passé en paramètre
 * 
 * @param int $id
 * @return void
 */
function SupprimerUser(int $idUser): void
{
    //Ouvrir la connexion
    $pdo = connexionBdd();

    //Préparer la requête
    $sql = 'DELETE FROM Users WHERE idUser = :idUser';

    $statement = $pdo->prepare($sql);

    //Exécuter la requête, passe l'Id saisi par l'utilisateur
    $statement->execute([
        ':idUser' => $idUser,
    ]);
}

/**
 * Filtre les données rentrés par le user 
 * 
 * @return array Le tableau verifié
 */
function filtrerUser(array $users): array
{
    $username = null;
    if (array_key_exists("username", $users)) {
        $username = filter_var($users["username"], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
    $urlPdP = null;
    if (array_key_exists("urlPdP", $users)) {
        $urlPdP = filter_var($users["urlPdP"], FILTER_VALIDATE_URL);
    }

    return ["username" => $username, "urlPdP" => $urlPdP];
}

/**
 * Vérifie les données rentrées par le user 
 * @return bool Si il n'y a pas d'erreurs
 * @return array Le tableau d'erreurs
 */
function verifierUser(array $users): array|bool
{
    $erreurs = [];
    if ($users["username"] === null || $users["username"] === "") {
        $erreurs["username"] = "Le username du user est obligatoire";
    }
    if ($users["urlPdP"] === null || $users["urlPdP"] === "") {
        $erreurs["urlPdP"] = "La pdp du user est obligatoire";
    }
    if (empty($erreurs)) {
        return true;
    }
    return $erreurs;
}

/* ------------------------------ PHOTOS ------------------------------*/
/**
 * Retourne tous les photos
 * 
 * @return array Un tableau de photos
 */
function RecupererDonneesPhotos(): array
{
    $pdo = connexionBdd();

    $sql = "SELECT * FROM Photo";

    $statement = $pdo->prepare($sql);

    $statement->execute();

    return $statement->fetchAll();
}

/**
 * Retourne la photo en fonction de l'id de la photo demandé
 * @return false Le photo n'existe pas
 * @return array Un tableau de photo
 */
/*
    function RecupererDonneesPhotoParIDPhoto(int $idPhoto): array|false
    {
        $pdo = connexionBdd();

        $sql = "SELECT * FROM Photo WHERE idPhoto = :idPhoto";

        $statement = $pdo->prepare($sql);

        $statement->execute([
            ':idPhoto' => $idPhoto,
        ]);

        return $statement->fetch();
    }
*/

/**
 * Retourne la photo en fonction de l'id du user demandé
 * @return false La photo n'existe pas
 * @return array Un tableau de photo
 */

function RecupererDonneesPhotoParIDUser(int $idUser): array|false
{
    $pdo = connexionBdd();

    $sql = "SELECT * FROM Photo WHERE user_id = :user_id";

    $statement = $pdo->prepare($sql);

    $statement->execute([
        ':user_id' => $idUser,
    ]);

    return $statement->fetch();
}

/**
 * Insert les données dans la base
 * @return int L'id de la photo ajoutée
 */
function InsererPhoto(int $idUser, string $photo_url): int
{
    $pdo = connexionBdd();

    // Utilisation de NOW() pour insérer la date actuelle dans la base
    $sql = 'INSERT INTO Photo (user_id, photo_url, created_at) VALUES (:user_id, :photo_url, NOW())';

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $idUser, PDO::PARAM_INT);
    $stmt->bindParam(':photo_url', $photo_url, PDO::PARAM_STR);
    
    $stmt->execute();

    // Retourne l'ID de la photo insérée
    return $pdo->lastInsertId();
}



/**
 * Modifie les informations de la bdd
 * @return void
 */
function ModifierPhoto(int $idPhoto, int $idUser, string $photo_url): void
{
    $pdo = connexionBdd();

    $sql = "UPDATE Photo SET user_id = :user_id, photo_url = :photo_url, created_at = NOW() WHERE idPhoto = :idPhoto";

    $statement = $pdo->prepare($sql);

    $statement->execute([
        ':idPhoto' => $idPhoto,
        ':user_id' => $idUser,
        ':photo_url' => $photo_url
    ]);
}


/**
 * Supprime la photo dont l'id est passé en paramètre
 * 
 * @param int $id
 * @return void
 */
function SupprimerPhoto(int $idPhoto): void
{
    //Ouvrir la connexion
    $pdo = connexionBdd();

    //Préparer la requête
    $sql = 'DELETE FROM Photo WHERE idPhoto = :idPhoto';

    $statement = $pdo->prepare($sql);

    //Exécuter la requête, passe l'Id saisi par l'utilisateur
    $statement->execute([
        ':idPhoto' => $idPhoto,
    ]);
}

/**
 * Filtre les données rentrés par le user 
 * 
 * @return array Le tableau verifié
 */
function filtrerPhoto(array $photos): array
{
    $user_id = null;
    if (array_key_exists("user_id", $photos)) {
        $user_id = filter_var($photos["user_id"], FILTER_SANITIZE_NUMBER_INT);
    }
    $photo_url = null;
    if (array_key_exists("photo_url", $photos)) {
        $photo_url = filter_var($photos["photo_url"], FILTER_VALIDATE_URL);
    }

    return ["user_id" => $user_id, "photo_url" => $photo_url];
}

/**
 * Vérifie les données rentrées par le user 
 * @return bool Si il n'y a pas d'erreurs
 * @return array Le tableau d'erreurs
 */
function verifierPhoto(array $photos): array|bool
{
    $erreurs = [];
    if ($photos["user_id"] === null || $photos["user_id"] === "") {
        $erreurs["user_id"] = "Le user_id de la photo est obligatoire";
    }
    if ($photos["photo_url"] === null || $photos["photo_url"] === "") {
        $erreurs["photo_url"] = "La photo_url de la photo est obligatoire";
    }
    if (empty($erreurs)) {
        return true;
    }
    return $erreurs;
}

/* ------------------------------ Friendship ------------------------------*/
/**
 * Retourne toutes les relations d'amitié.
 * 
 * @return array Un tableau de relations d'amitié
 */
function RecupererDonneesFriendships(): array
{
    $pdo = connexionBdd();

    $sql = "SELECT * FROM Friendship";

    $statement = $pdo->prepare($sql);
    $statement->execute();

    return $statement->fetchAll();
}

/**
 * Retourne les relations d'amitié pour un utilisateur donné.
 *
 * @param int $idUser
 * @return array Un tableau de relations d'amitié
 */
function RecupererFriendshipsParIDUser(int $idUser): array
{
    $pdo = connexionBdd();

    $sql = "SELECT * FROM Friendship WHERE user_id_1 = :idUser OR user_id_2 = :idUser";

    $statement = $pdo->prepare($sql);
    $statement->execute([':idUser' => $idUser]);

    return $statement->fetchAll();
}

/**
 * Crée une nouvelle relation d'amitié en respectant l'ordre des IDs.
 *
 * @param int $userId
 * @param int $friendId
 * @return int Le nombre de lignes affectées
 */
function InsererFriendship(int $userId, int $friendId): int
{
    $pdo = connexionBdd();

    // Assurer l'ordre des IDs pour respecter la contrainte CHECK
    [$user_id_1, $user_id_2] = ($userId < $friendId) ? [$userId, $friendId] : [$friendId, $userId];

    $sql = 'INSERT INTO Friendship (user_id_1, user_id_2) VALUES (:user_id_1, :user_id_2)';
    $statement = $pdo->prepare($sql);
    $statement->execute([
        ':user_id_1' => $user_id_1,
        ':user_id_2' => $user_id_2,
    ]);

    return $statement->rowCount();
}

/**
 * Supprime une relation d'amitié.
 *
 * @param int $userId
 * @param int $friendId
 * @return void
 */
function SupprimerFriendship(int $userId, int $friendId): void
{
    $pdo = connexionBdd();

    [$user_id_1, $user_id_2] = ($userId < $friendId) ? [$userId, $friendId] : [$friendId, $userId];

    $sql = 'DELETE FROM Friendship WHERE user_id_1 = :user_id_1 AND user_id_2 = :user_id_2';

    $statement = $pdo->prepare($sql);
    $statement->execute([
        ':user_id_1' => $user_id_1,
        ':user_id_2' => $user_id_2,
    ]);
}

/**
 * Vérifie si une relation d'amitié existe déjà.
 *
 * @param int $userId
 * @param int $friendId
 * @return bool
 */
function VerifierFriendshipExist(int $userId, int $friendId): bool
{
    $pdo = connexionBdd();

    [$user_id_1, $user_id_2] = ($userId < $friendId) ? [$userId, $friendId] : [$friendId, $userId];

    $sql = 'SELECT * FROM Friendship WHERE user_id_1 = :user_id_1 AND user_id_2 = :user_id_2';

    $statement = $pdo->prepare($sql);
    $statement->execute([
        ':user_id_1' => $user_id_1,
        ':user_id_2' => $user_id_2,
    ]);

    return $statement->fetch() !== false;
}

/* ------------------------------ Général ------------------------------*/
/**
 * Retourne les inforamtions aux users
 * @return void Une erreur s'est produit ou pas 
 */
function envoyerDonnees(array $donnees, int $codeHTTP): void
{
    http_response_code($codeHTTP);
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($donnees);
    die();
}

/**
 * Recupere les données rentrés par le user
 * 
 * @return array Un tableau de donées
 */
function recupererDonneesJson(): array
{
    $contenu = file_get_contents("php://input");
    if ($contenu == false) {
        return [];
    }
    $donnees = json_decode($contenu, true);
    if (!is_array($donnees)) {
        return [];
    }
    return $donnees;
}