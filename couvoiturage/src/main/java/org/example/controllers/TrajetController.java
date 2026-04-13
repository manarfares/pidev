package org.example.controllers;

import javafx.beans.property.ReadOnlyStringWrapper;
import javafx.collections.FXCollections;
import javafx.fxml.FXML;
import javafx.scene.control.*;
import org.example.models.Trajet;
import org.example.services.DatabaseConnection;
import org.example.services.TrajetService;
import org.example.utils.InputValidator;
import org.example.utils.ViewNavigator;

import java.io.IOException;
import java.sql.SQLException;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.util.List;

public class TrajetController {
    private static final DateTimeFormatter UI_DATE_FORMAT = DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm");
    private final TrajetService trajetService = new TrajetService();

    @FXML
    private TextField departField;

    @FXML
    private TextField destinationField;

    @FXML
    private DatePicker dateDepartPicker;

    @FXML
    private TextField heureDepartField;

    @FXML
    private Spinner<Integer> placesSpinner;

    @FXML
    private Label statusLabel;

    @FXML
    private TableView<Trajet> trajetsTable;

    @FXML
    private TableColumn<Trajet, String> departColumn;

    @FXML
    private TableColumn<Trajet, String> destinationColumn;

    @FXML
    private TableColumn<Trajet, String> dateColumn;

    @FXML
    private TableColumn<Trajet, String> placesColumn;

    @FXML
    public void initialize() {
        configurerSpinner();
        configurerValidations();
        heureDepartField.setText("08:00");

        departColumn.setCellValueFactory(cell -> new ReadOnlyStringWrapper(cell.getValue().getDepart()));
        destinationColumn.setCellValueFactory(cell -> new ReadOnlyStringWrapper(cell.getValue().getDestination()));
        dateColumn.setCellValueFactory(cell -> new ReadOnlyStringWrapper(
                cell.getValue().getDateDepart().format(UI_DATE_FORMAT)
        ));
        placesColumn.setCellValueFactory(cell -> new ReadOnlyStringWrapper(
                String.valueOf(cell.getValue().getNombrePlaces())
        ));

        try {
            trajetService.initialiserBase();
            afficherMessageSucces("Connexion reussie a la base de donnees. Base initialisee.");
            chargerTrajets();
        } catch (SQLException exception) {
            afficherErreur("Erreur de connexion a MySQL/XAMPP : " + exception.getMessage());
        }
    }

    private void configurerValidations() {
        departField.textProperty().addListener((obs, oldVal, newVal) -> {
            if (newVal != null && !newVal.isEmpty()) {
                InputValidator.ValidationResult result = InputValidator.validerLieu(newVal, "Le depart");
                if (!result.isValid()) {
                    departField.setStyle("-fx-border-color: red; -fx-border-width: 2px;");
                } else {
                    departField.setStyle("");
                }
            } else {
                departField.setStyle("");
            }
        });

        destinationField.textProperty().addListener((obs, oldVal, newVal) -> {
            if (newVal != null && !newVal.isEmpty()) {
                InputValidator.ValidationResult result = InputValidator.validerLieu(newVal, "La destination");
                if (!result.isValid()) {
                    destinationField.setStyle("-fx-border-color: red; -fx-border-width: 2px;");
                } else {
                    destinationField.setStyle("");
                }
            } else {
                destinationField.setStyle("");
            }
        });

        heureDepartField.textProperty().addListener((obs, oldVal, newVal) -> {
            if (newVal != null && !newVal.isEmpty()) {
                InputValidator.ValidationResult result = InputValidator.validerHeure(newVal, "L'heure");
                if (!result.isValid()) {
                    heureDepartField.setStyle("-fx-border-color: red; -fx-border-width: 2px;");
                } else {
                    heureDepartField.setStyle("");
                }
            } else {
                heureDepartField.setStyle("");
            }
        });

        dateDepartPicker.valueProperty().addListener((obs, oldVal, newVal) -> {
            if (newVal != null) {
                InputValidator.ValidationResult result = InputValidator.validerDateDepart(newVal, false);
                if (!result.isValid()) {
                    dateDepartPicker.setStyle("-fx-border-color: red; -fx-border-width: 2px;");
                } else {
                    dateDepartPicker.setStyle("");
                }
            } else {
                dateDepartPicker.setStyle("");
            }
        });
    }

    @FXML
    private void enregistrerTrajet() {
        try {
            InputValidator.ValidationResult validation = InputValidator.validerTrajetComplet(
                    departField.getText(),
                    destinationField.getText(),
                    dateDepartPicker.getValue(),
                    heureDepartField.getText(),
                    placesSpinner.getValue()
            );

            if (!validation.isValid()) {
                afficherErreur(validation.getErrorMessage());
                return;
            }

            Trajet trajet = lireFormulaire();
            trajetService.enregistrer(trajet);
            afficherMessageSucces(DatabaseConnection.SUCCESS_MESSAGE + " Trajet enregistre.");
            viderFormulaire();
            chargerTrajets();
        } catch (IllegalArgumentException exception) {
            afficherErreur(exception.getMessage());
        } catch (SQLException exception) {
            afficherErreur("Impossible d'enregistrer le trajet : " + exception.getMessage());
        }
    }

    @FXML
    private void actualiserListe() {
        try {
            chargerTrajets();
        } catch (SQLException exception) {
            afficherErreur("Impossible de charger les trajets : " + exception.getMessage());
        }
    }

    @FXML
    private void ouvrirPageModification() {
        Trajet trajetSelectionne = trajetsTable.getSelectionModel().getSelectedItem();

        if (trajetSelectionne == null) {
            afficherErreur("Selectionne d'abord un trajet dans la liste.");
            return;
        }

        try {
            ViewNavigator.navigate(
                    statusLabel,
                    "/org/example/views/edit-trajet-view.fxml",
                    controller -> ((ModifierTrajetController) controller).setTrajet(trajetSelectionne)
            );
        } catch (IOException exception) {
            afficherErreur("Impossible d'ouvrir la page de modification : " + exception.getMessage());
        }
    }

    @FXML
    private void supprimerTrajet() {
        Trajet trajetSelectionne = trajetsTable.getSelectionModel().getSelectedItem();

        if (trajetSelectionne == null) {
            afficherErreur("Selectionne d'abord un trajet dans la liste a supprimer.");
            return;
        }

        Alert alert = new Alert(Alert.AlertType.CONFIRMATION);
        alert.setTitle("Confirmation de suppression");
        alert.setHeaderText("Supprimer le trajet ?");
        alert.setContentText("Voulez-vous vraiment supprimer le trajet de " +
                trajetSelectionne.getDepart() + " vers " +
                trajetSelectionne.getDestination() + " ?");

        var result = alert.showAndWait();
        if (result.isPresent() && result.get() == ButtonType.OK) {
            try {
                trajetService.supprimer(trajetSelectionne.getId());
                afficherMessageSucces(DatabaseConnection.SUCCESS_MESSAGE + " Trajet supprime.");
                chargerTrajets();
            } catch (SQLException exception) {
                afficherErreur("Impossible de supprimer le trajet : " + exception.getMessage());
            }
        }
    }

    private void chargerTrajets() throws SQLException {
        List<Trajet> trajets = trajetService.recupererTousLesTrajets();
        trajetsTable.setItems(FXCollections.observableArrayList(trajets));
        afficherMessageSucces(DatabaseConnection.SUCCESS_MESSAGE + " " + trajets.size() + " trajet(s) charge(s).");
    }

    private Trajet lireFormulaire() {
        String depart = departField.getText() == null ? "" : departField.getText().trim();
        String destination = destinationField.getText() == null ? "" : destinationField.getText().trim();
        LocalDate date = dateDepartPicker.getValue();
        String heure = heureDepartField.getText() == null ? "" : heureDepartField.getText().trim();
        Integer places = placesSpinner.getValue();

        LocalTime time = LocalTime.parse(heure, DateTimeFormatter.ofPattern("HH:mm"));
        LocalDateTime dateDepart = LocalDateTime.of(date, time);
        return new Trajet(depart, destination, dateDepart, places);
    }

    private void viderFormulaire() {
        departField.clear();
        destinationField.clear();
        dateDepartPicker.setValue(null);
        heureDepartField.setText("08:00");
        placesSpinner.getValueFactory().setValue(1);

        departField.setStyle("");
        destinationField.setStyle("");
        dateDepartPicker.setStyle("");
        heureDepartField.setStyle("");
    }

    private void configurerSpinner() {
        SpinnerValueFactory.IntegerSpinnerValueFactory valueFactory =
                new SpinnerValueFactory.IntegerSpinnerValueFactory(1, 20, 1);
        placesSpinner.setValueFactory(valueFactory);
    }

    private void afficherMessageSucces(String message) {
        statusLabel.setText(message);
        statusLabel.getStyleClass().remove("status-error");
        if (!statusLabel.getStyleClass().contains("status-success")) {
            statusLabel.getStyleClass().add("status-success");
        }
    }

    private void afficherErreur(String message) {
        statusLabel.setText(message);
        statusLabel.getStyleClass().remove("status-success");
        if (!statusLabel.getStyleClass().contains("status-error")) {
            statusLabel.getStyleClass().add("status-error");
        }
    }
}