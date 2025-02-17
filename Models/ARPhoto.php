<?php
namespace Models;

use Config\PDOSingleton;
use Interfaces\IActiveRecord;
use PDO;
use Exception;

class ARPhoto extends BaseModel implements IActiveRecord
{
    public $idPhoto = null;
    public $userId = null;
    public $photoUrl = '';
    public $createdAt = null;

    protected static $table = 'Photo';

    public function __construct($idPhoto = null, $userId = null, $photoUrl = '', $createdAt = null)
    {
        parent::__construct();
        $this->idPhoto = $idPhoto;
        $this->userId = $userId;
        $this->photoUrl = $photoUrl;
        $this->createdAt = $createdAt;
    }

    public static function find($idPhoto)
    {
        try {
            $sql = "SELECT * FROM " . static::$table . " WHERE idPhoto = :idPhoto;";
            $pdoInstance = PDOSingleton::getInstance();
            $stmt = $pdoInstance->getConnection()->prepare($sql);
            $stmt->execute(['idPhoto' => $idPhoto]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                return new self($data['idPhoto'], $data['user_id'], $data['photo_url'], $data['created_at']);
            }
            return null;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche de la photo : " . $e->getMessage());
        }
    }

    public function create()
    {
        if (empty($this->userId) || empty($this->photoUrl)) {
            throw new Exception("Les champs userId et photoUrl sont obligatoires.");
        }
        $sql = "INSERT INTO " . static::$table . " (user_id, photo_url) VALUES (:user_id, :photo_url);";
        $this->executeQuery($sql, ['user_id' => $this->userId, 'photo_url' => $this->photoUrl]);
        $this->idPhoto = $this->pdoConnection->lastInsertId();
    }

    public function update()
    {
        if ($this->idPhoto === null) {
            throw new Exception("Impossible de mettre à jour une photo inexistante.");
        }
        $sql = "UPDATE " . static::$table . " SET photo_url = :photo_url WHERE idPhoto = :idPhoto;";
        $this->executeQuery($sql, ['photo_url' => $this->photoUrl, 'idPhoto' => $this->idPhoto]);
    }

    public function save()
    {
        if ($this->idPhoto === null) {
            $this->create();
        } else {
            $this->update();
        }
    }

    public function delete()
    {
        if ($this->idPhoto === null) {
            throw new Exception("Impossible de supprimer une photo inexistante.");
        }
        $sql = "DELETE FROM " . static::$table . " WHERE idPhoto = :idPhoto;";
        $this->executeQuery($sql, ['idPhoto' => $this->idPhoto]);
    }

    public static function findAllByUserId($userId)
    {
        $sql = "
            SELECT p.* 
            FROM " . static::$table . " p
            WHERE p.user_id = :user_id  -- Récupère les photos de l'utilisateur
            OR p.user_id IN (  
                -- Récupère les photos des amis de l'utilisateur
                SELECT CASE 
                    WHEN f.user_id_1 = :user_id THEN f.user_id_2 
                    ELSE f.user_id_1 
                END 
                FROM Friendship f
                WHERE f.user_id_1 = :user_id OR f.user_id_2 = :user_id
            )
            ORDER BY p.created_at DESC;  -- Trie les photos par date de création (les plus récentes en premier)
        ";

        $stmt = PDOSingleton::getInstance()->getConnection()->prepare($sql);

        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
