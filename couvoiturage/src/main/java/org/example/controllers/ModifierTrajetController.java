package org.example.controllers;

import javafx.fxml.FXML;
import javafx.scene.control.DatePicker;
import javafx.scene.control.Label;
import javafx.scene.control.Spinner;
import javafx.scene.control.SpinnerValueFactory;
import javafx.scene.control.TextField;
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
import java.time.format.DateTimeParseException;

public class ModifierTrajetController {
    private final TrajetService trajetService = new TrajetService();
    private Trajet trajetSelectionne;

    @FXML
    private Label trajetIdLabel;

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
    public void initialize() {
        SpinnerValueFactory.IntegerSpinnerValueFactory valueFactory =
                new SpinnerValueFactory.IntegerSpinnerValueFactory(1, 20, 1);
        placesSpinner.setValueFactory(valueFactory);
        heureDepartField.setText("08:00");
        configurerValidations();
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
                InputValidator.ValidationResult result = InputValidator.validerDateDepart(newVal, true);
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

    public void setTrajet(Trajet trajet) {
        this.trajetSelectionne = new Trajet(
                trajet.getId(),
                trajet.getDepart(),
                trajet.getDestination(),
                trajet.getDateDepart(),
                trajet.getNombrePlaces()
        );

        trajetIdLabel.setText("Trajet #" + trajet.getId());
        departField.setText(trajet.getDepart());
        destinationField.setText(trajet.getDestination());
        dateDepartPicker.setValue(trajet.getDateDepart().toLocalDate());
        heureDepartField.setText(trajet.getDateDepart().toLocalTime().format(DateTimeFormatter.ofPattern("HH:mm")));
        placesSpinner.getValueFactory().setValue(trajet.getNombrePlaces());
        afficherSucces("Trajet charge. Tu peux maintenant le modifier.");
    }

    @FXML
    private void enregistrerModification() {
        if (trajetSelectionne == null) {
            afficherErreur("Aucun trajet n'a ete selectionne.");
            return;
        }

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

            mettreAJourTrajetDepuisFormulaire();
            trajetService.mettreAJour(trajetSelectionne);
            afficherSucces(DatabaseConnection.SUCCESS_MESSAGE + " Trajet modifie.");
        } catch (IllegalArgumentException exception) {
            afficherErreur(exception.getMessage());
        } catch (SQLException exception) {
            afficherErreur("Impossible de modifier le trajet : " + exception.getMessage());
        }
    }

    @FXML
    private void retourListe() {
        try {
            ViewNavigator.navigate(statusLabel, "/org/example/views/main-view.fxml");
        } catch (IOException exception) {
            afficherErreur("Impossible de revenir a la liste : " + exception.getMessage());
        }
    }

    private void mettreAJourTrajetDepuisFormulaire() {
        String depart = departField.getText() == null ? "" : departField.getText().trim();
        String destination = destinationField.getText() == null ? "" : destinationField.getText().trim();
        LocalDate date = dateDepartPicker.getValue();
        String heure = heureDepartField.getText() == null ? "" : heureDepartField.getText().trim();
        Integer places = placesSpinner.getValue();

        if (depart.isEmpty()) {
            throw new IllegalArgumentException("Le champ depart est obligatoire.");
        }

        if (destination.isEmpty()) {
            throw new IllegalArgumentException("Le champ destination est obligatoire.");
        }

        if (date == null) {
            throw new IllegalArgumentException("Choisis une date de depart.");
        }

        if (heure.isEmpty()) {
            throw new IllegalArgumentException("Saisis une heure au format HH:mm.");
        }

        if (places == null || places < 1) {
            throw new IllegalArgumentException("Le nombre de places doit etre superieur a 0.");
        }

        try {
            LocalTime heureDepart = LocalTime.parse(heure, DateTimeFormatter.ofPattern("HH:mm"));
            LocalDateTime dateDepart = LocalDateTime.of(date, heureDepart);

            trajetSelectionne.setDepart(depart);
            trajetSelectionne.setDestination(destination);
            trajetSelectionne.setDateDepart(dateDepart);
            trajetSelectionne.setNombrePlaces(places);
        } catch (DateTimeParseException exception) {
            throw new IllegalArgumentException("Format d'heure invalide. Utilise HH:mm, par exemple 08:30.");
        }
    }

    private void afficherSucces(String message) {
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