CREATE DATABASE IF NOT EXISTS couvoiturage;
USE couvoiturage;

CREATE TABLE IF NOT EXISTS trajets (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    depart VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    date_depart DATETIME NOT NULL,
    nombre_places INT NOT NULL
);
