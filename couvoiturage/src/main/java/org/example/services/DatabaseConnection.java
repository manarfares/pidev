package org.example.services;

import java.io.IOException;
import java.io.InputStream;
import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;
import java.util.Properties;

public final class DatabaseConnection {
    public static final String SUCCESS_MESSAGE = "Connexion reussie a la base de donnees.";
    private static final String PROPERTIES_FILE = "database.properties";
    private static final Properties PROPERTIES = loadProperties();

    private DatabaseConnection() {
    }

    public static Connection getConnection() throws SQLException {
        loadDriver();

        Connection connection = DriverManager.getConnection(
                PROPERTIES.getProperty("db.url"),
                PROPERTIES.getProperty("db.user"),
                PROPERTIES.getProperty("db.password")
        );

        System.out.println(SUCCESS_MESSAGE);
        return connection;
    }

    public static Connection getServerConnection() throws SQLException {
        loadDriver();

        Connection connection = DriverManager.getConnection(
                PROPERTIES.getProperty("db.server.url"),
                PROPERTIES.getProperty("db.user"),
                PROPERTIES.getProperty("db.password")
        );

        System.out.println(SUCCESS_MESSAGE);
        return connection;
    }

    private static void loadDriver() {
        try {
            Class.forName(PROPERTIES.getProperty("db.driver"));
        } catch (ClassNotFoundException exception) {
            throw new IllegalStateException("Driver MySQL introuvable dans le projet.", exception);
        }
    }

    private static Properties loadProperties() {
        Properties properties = new Properties();

        try (InputStream inputStream = DatabaseConnection.class
                .getClassLoader()
                .getResourceAsStream(PROPERTIES_FILE)) {

            if (inputStream == null) {
                throw new IllegalStateException("Fichier " + PROPERTIES_FILE + " introuvable.");
            }

            properties.load(inputStream);
            return properties;
        } catch (IOException exception) {
            throw new IllegalStateException("Impossible de lire la configuration de la base.", exception);
        }
    }
}
