<?php
namespace Models;

use Config\PDOSingleton;
use Interfaces\IActiveRecord;
use PDO;
use Exception;

//A réparer
class ARFriendship extends BaseModel
{
    public $userId1;
    public $userId2;

    protected static $table = 'Friendship';

    public function __construct($userId1, $userId2)
    {
        parent::__construct();

        // Convention : toujours avoir userId1 < userId2
        if ($userId1 > $userId2) {
            [$userId1, $userId2] = [$userId2, $userId1];
        }

        if ($userId1 === $userId2) {
            throw new Exception("Un utilisateur ne peut pas être ami avec lui-même.");
        }

        $this->userId1 = $userId1;
        $this->userId2 = $userId2;
    }

    public function create()
    {
        $sql = "INSERT INTO " . static::$table . " (user_id_1, user_id_2) VALUES (:user_id_1, :user_id_2);";
        $this->executeQuery($sql, ['user_id_1' => $this->userId1, 'user_id_2' => $this->userId2]);
    }

    public static function find($userId1, $userId2)
    {
        if ($userId1 > $userId2) {
            [$userId1, $userId2] = [$userId2, $userId1];
        }
        
        $sql = "SELECT * FROM " . static::$table . " WHERE user_id_1 = :user_id_1 AND user_id_2 = :user_id_2;";
        $stmt = PDOSingleton::getInstance()->getConnection()->prepare($sql);
        $stmt->execute(['user_id_1' => $userId1, 'user_id_2' => $userId2]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            return new self($data['user_id_1'], $data['user_id_2']);
        }
        return null;
    }

    public function delete()
    {
        $sql = "DELETE FROM " . static::$table . " WHERE user_id_1 = :user_id_1 AND user_id_2 = :user_id_2;";
        $this->executeQuery($sql, ['user_id_1' => $this->userId1, 'user_id_2' => $this->userId2]);
    }

    public static function findAllForUser($userId)
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE user_id_1 = :user_id OR user_id_2 = :user_id;";
        $stmt = PDOSingleton::getInstance()->getConnection()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
