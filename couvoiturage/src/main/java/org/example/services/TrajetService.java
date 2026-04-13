package org.example.services;

import org.example.models.Trajet;
import java.sql.*;
import java.time.LocalDateTime;
import java.util.ArrayList;
import java.util.List;

public class TrajetService {

    public void initialiserBase() throws SQLException {
        String sql = "CREATE TABLE IF NOT EXISTS trajets (" +
                "id INT AUTO_INCREMENT PRIMARY KEY," +
                "depart VARCHAR(100) NOT NULL," +
                "destination VARCHAR(100) NOT NULL," +
                "date_depart DATETIME NOT NULL," +
                "nombre_places INT NOT NULL" +
                ")";

        try (Connection conn = DatabaseConnection.getConnection();
             Statement stmt = conn.createStatement()) {
            stmt.execute(sql);
        }
    }

    public void enregistrer(Trajet trajet) throws SQLException {
        String sql = "INSERT INTO trajets (depart, destination, date_depart, nombre_places) VALUES (?, ?, ?, ?)";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, trajet.getDepart());
            pstmt.setString(2, trajet.getDestination());
            pstmt.setTimestamp(3, Timestamp.valueOf(trajet.getDateDepart()));
            pstmt.setInt(4, trajet.getNombrePlaces());
            pstmt.executeUpdate();
        }
    }

    public List<Trajet> recupererTousLesTrajets() throws SQLException {
        List<Trajet> trajets = new ArrayList<>();
        String sql = "SELECT * FROM trajets ORDER BY date_depart";

        try (Connection conn = DatabaseConnection.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery(sql)) {

            while (rs.next()) {
                Trajet trajet = new Trajet(
                        rs.getInt("id"),
                        rs.getString("depart"),
                        rs.getString("destination"),
                        rs.getTimestamp("date_depart").toLocalDateTime(),
                        rs.getInt("nombre_places")
                );
                trajets.add(trajet);
            }
        }
        return trajets;
    }

    public void mettreAJour(Trajet trajet) throws SQLException {
        String sql = "UPDATE trajets SET depart = ?, destination = ?, date_depart = ?, nombre_places = ? WHERE id = ?";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setString(1, trajet.getDepart());
            pstmt.setString(2, trajet.getDestination());
            pstmt.setTimestamp(3, Timestamp.valueOf(trajet.getDateDepart()));
            pstmt.setInt(4, trajet.getNombrePlaces());
            pstmt.setLong(5, trajet.getId());  // Changé de setInt à setLong
            pstmt.executeUpdate();
        }
    }

    public void supprimer(long id) throws SQLException {  // Changé de int à long
        String sql = "DELETE FROM trajets WHERE id = ?";

        try (Connection conn = DatabaseConnection.getConnection();
             PreparedStatement pstmt = conn.prepareStatement(sql)) {

            pstmt.setLong(1, id);  // Changé de setInt à setLong
            int rowsAffected = pstmt.executeUpdate();

            if (rowsAffected == 0) {
                throw new SQLException("Aucun trajet trouvé avec l'ID: " + id);
            }
        }
    }

    public void supprimer(Trajet trajet) throws SQLException {
        supprimer(trajet.getId());
    }
}