<?php

//Importation du fichier "constantes.php"
require_once '../PHP/constantes.php';

/**
 * Initialise et retourne la connection à la base de données
 *
 * @return PDO Une instance de la classe PDO
 */
function connexionBdd() : PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . BDD_HOTE . ';dbname=' . BDD_NOM . ';charset=' . BDD_CHARSET;
        

        //Configurer l'accès à la DB
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, BDD_UTILISATEUR, BDD_MOT_DE_PASSE, $options);
    }

    return $pdo;
}
