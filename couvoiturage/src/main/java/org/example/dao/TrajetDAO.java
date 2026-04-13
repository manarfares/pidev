package org.example.dao;

import org.example.models.Trajet;
import org.example.services.DatabaseConnection;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.sql.Timestamp;
import java.util.ArrayList;
import java.util.List;

public class TrajetDAO {
    public void save(Trajet trajet) throws SQLException {
        String sql = """
                INSERT INTO trajets (depart, destination, date_depart, nombre_places)
                VALUES (?, ?, ?, ?)
                """;

        try (Connection connection = DatabaseConnection.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {

            statement.setString(1, trajet.getDepart());
            statement.setString(2, trajet.getDestination());
            statement.setTimestamp(3, Timestamp.valueOf(trajet.getDateDepart()));
            statement.setInt(4, trajet.getNombrePlaces());
            statement.executeUpdate();
        }
    }

    public void update(Trajet trajet) throws SQLException {
        String sql = """
                UPDATE trajets
                SET depart = ?, destination = ?, date_depart = ?, nombre_places = ?
                WHERE id = ?
                """;

        try (Connection connection = DatabaseConnection.getConnection();
             PreparedStatement statement = connection.prepareStatement(sql)) {

            statement.setString(1, trajet.getDepart());
            statement.setString(2, trajet.getDestination());
            statement.setTimestamp(3, Timestamp.valueOf(trajet.getDateDepart()));
            statement.setInt(4, trajet.getNombrePlaces());
            statement.setLong(5, trajet.getId());
            statement.executeUpdate();
        }
    }

    public List<Trajet> findAll() throws SQLException {
        List<Trajet> trajets = new ArrayList<>();
        String sql = """
                SELECT id, depart, destination, date_depart, nombre_places
                FROM trajets
                ORDER BY date_depart ASC
                """;

        try (Connection connection = DatabaseConnection.getConnection();
             Statement statement = connection.createStatement();
             ResultSet resultSet = statement.executeQuery(sql)) {

            while (resultSet.next()) {
                trajets.add(new Trajet(
                        resultSet.getLong("id"),
                        resultSet.getString("depart"),
                        resultSet.getString("destination"),
                        resultSet.getTimestamp("date_depart").toLocalDateTime(),
                        resultSet.getInt("nombre_places")
                ));
            }
        }

        return trajets;
    }
}
