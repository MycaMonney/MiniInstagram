<?php
namespace Config;

use PDO;
use PDOException;

class PDOSingleton
{
    //Stock
    private static $instance = null; //Instance unique de la classe
    private $pdo; // La connexion PDO

    // Informations de connexion à la base de donées (généralement déportées)
    private $host = 'localhost';
    private $db = 'miniInstagram';
    private $user = 'monUserName';
    private $pass = 'monMDP';
    private $charset = 'utf8mb4';

    //Constructeur
    private function __construct() {
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    // Empêche la duplication d'instances par clonage
    private function __clone() {}

    // Empêche la désérialisation
    private function __wakeup(){}

    //Méthode pour obtenir l'instance unique 
    public static function getInstance() 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    //Récupère l'objet PDO pour exécuter des requêtes SQL
    public function getConnection(){
        return $this->pdo;
    }
}
