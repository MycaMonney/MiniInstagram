DROP DATABASE IF EXISTS miniInstagram;
CREATE DATABASE IF NOT EXISTS miniInstagram;

USE miniInstagram;

CREATE TABLE Users (
    idUser INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    urlPdP VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE Photo (
    idPhoto INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    photo_url VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(idUser) ON DELETE CASCADE
);

CREATE TABLE Friendship (
    user_id_1 INT NOT NULL,
    user_id_2 INT NOT NULL,
    PRIMARY KEY (user_id_1, user_id_2),
    CHECK (user_id_1 < user_id_2),
    FOREIGN KEY (user_id_1) REFERENCES Users(idUser) ON DELETE CASCADE,
    FOREIGN KEY (user_id_2) REFERENCES Users(idUser) ON DELETE CASCADE
);
