<?php
namespace Models;

use Config\PDOSingleton;
use Interfaces\IActiveRecord;
use PDO;
use Exception;

class ARUser extends BaseModel implements IActiveRecord
{
    public $idUser = null;
    public $username = '';

    protected static $table = 'User';

    public function __construct($idUser = null, $username = '')
    {
        parent::__construct();
        $this->idUser = $idUser;
        $this->username = $username;
    }

    public static function find($idUser)
    {
        try {
            $sql = "SELECT * FROM " . static::$table . " WHERE idUser = :idUser;";
            $pdoInstance = PDOSingleton::getInstance();
            $stmt = $pdoInstance->getConnection()->prepare($sql);
            $stmt->execute(['idUser' => $idUser]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                return new self($data['idUser'], $data['username']);
            }
            return null;
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la recherche de l'utilisateur : " . $e->getMessage());
        }
    }

    public function create()
    {
        if (empty($this->username)) {
            throw new Exception("Le nom d'utilisateur ne peut pas être vide.");
        }
        $sql = "INSERT INTO " . static::$table . " (username) VALUES (:username);";
        $this->executeQuery($sql, ['username' => $this->username]);
        $this->idUser = $this->pdoConnection->lastInsertId();
    }

    public function update()
    {
        if ($this->idUser === null) {
            throw new Exception("Impossible de mettre à jour un utilisateur inexistant.");
        }
        $sql = "UPDATE " . static::$table . " SET username = :username WHERE idUser = :idUser;";
        $this->executeQuery($sql, ['username' => $this->username, 'idUser' => $this->idUser]);
    }

    public function save()
    {
        if ($this->idUser === null) {
            $this->create();
        } else {
            $this->update();
        }
    }

    public function delete()
    {
        if ($this->idUser === null) {
            throw new Exception("Impossible de supprimer un utilisateur inexistant.");
        }
        $sql = "DELETE FROM " . static::$table . " WHERE idUser = :idUser;";
        $this->executeQuery($sql, ['idUser' => $this->idUser]);
    }

    public static function findAll()
    {
        $sql = "SELECT * FROM " . static::$table;
        $stmt = PDOSingleton::getInstance()->getConnection()->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
