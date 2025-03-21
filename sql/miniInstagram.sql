DROP DATABASE IF EXISTS miniInstagram;
CREATE DATABASE IF NOT EXISTS miniInstagram;

USE miniInstagram;

CREATE TABLE User (
    idUser INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL
);

CREATE TABLE Photo (
    idPhoto INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    photo_url VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES User(idUser) ON DELETE CASCADE
);

CREATE TABLE Friendship (
    user_id_1 INT(11) NOT NULL,
    user_id_2 INT(11) NOT NULL,
    PRIMARY KEY (user_id_1, user_id_2),
    FOREIGN KEY (user_id_1) REFERENCES User(idUser) ON DELETE CASCADE,
    FOREIGN KEY (user_id_2) REFERENCES User(idUser) ON DELETE CASCADE
);
