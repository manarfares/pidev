package org.example.utils;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.time.format.DateTimeParseException;
import java.util.regex.Pattern;

public class InputValidator {

    private static final Pattern LETTERS_ONLY = Pattern.compile("^[a-zA-ZÀ-ÿ\\s\\-']+$");
    private static final Pattern TIME_PATTERN = Pattern.compile("^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$");
    private static final int MAX_LOCATION_LENGTH = 100;
    private static final int MAX_PLACES = 20;
    private static final int MIN_PLACES = 1;

    public static ValidationResult validerLieu(String lieu, String nomChamp) {
        if (lieu == null || lieu.trim().isEmpty()) {
            return ValidationResult.error(nomChamp + " est obligatoire.");
        }

        String trimmed = lieu.trim();

        if (trimmed.length() < 2) {
            return ValidationResult.error(nomChamp + " doit contenir au moins 2 caracteres.");
        }

        if (trimmed.length() > MAX_LOCATION_LENGTH) {
            return ValidationResult.error(nomChamp + " ne peut pas depasser " + MAX_LOCATION_LENGTH + " caracteres.");
        }

        if (!LETTERS_ONLY.matcher(trimmed).matches()) {
            return ValidationResult.error(nomChamp + " ne doit contenir que des lettres, espaces, tirets ou apostrophes.");
        }

        return ValidationResult.success(trimmed);
    }

    public static ValidationResult validerHeure(String heure, String nomChamp) {
        if (heure == null || heure.trim().isEmpty()) {
            return ValidationResult.error(nomChamp + " est obligatoire.");
        }

        String trimmed = heure.trim();

        if (!TIME_PATTERN.matcher(trimmed).matches()) {
            return ValidationResult.error("Format d'heure invalide. Utilisez HH:mm (ex: 14:30)");
        }

        try {
            LocalTime.parse(trimmed, DateTimeFormatter.ofPattern("HH:mm"));
            return ValidationResult.success(trimmed);
        } catch (DateTimeParseException e) {
            return ValidationResult.error("Heure invalide. Verifiez le format HH:mm");
        }
    }

    public static ValidationResult validerDateDepart(LocalDate date, boolean allowPast) {
        if (date == null) {
            return ValidationResult.error("Veuillez choisir une date de depart.");
        }

        if (!allowPast && date.isBefore(LocalDate.now())) {
            return ValidationResult.error("La date de depart ne peut pas etre dans le passe.");
        }

        return ValidationResult.success(date);
    }

    public static ValidationResult validerDateTimeDepart(LocalDate date, String heure, boolean allowPast) {
        ValidationResult dateResult = validerDateDepart(date, allowPast);
        if (!dateResult.isValid()) {
            return dateResult;
        }

        ValidationResult heureResult = validerHeure(heure, "L'heure");
        if (!heureResult.isValid()) {
            return heureResult;
        }

        try {
            LocalTime time = LocalTime.parse(heure.trim(), DateTimeFormatter.ofPattern("HH:mm"));
            LocalDateTime dateTime = LocalDateTime.of(date, time);

            if (!allowPast && dateTime.isBefore(LocalDateTime.now())) {
                return ValidationResult.error("La date et l'heure de depart ne peuvent pas etre dans le passe.");
            }

            return ValidationResult.success(dateTime);
        } catch (DateTimeParseException e) {
            return ValidationResult.error("Format de date/heure invalide.");
        }
    }

    public static ValidationResult validerPlaces(Integer places) {
        if (places == null) {
            return ValidationResult.error("Le nombre de places est obligatoire.");
        }

        if (places < MIN_PLACES) {
            return ValidationResult.error("Le nombre de places doit etre au moins " + MIN_PLACES + ".");
        }

        if (places > MAX_PLACES) {
            return ValidationResult.error("Le nombre de places ne peut pas depasser " + MAX_PLACES + ".");
        }

        return ValidationResult.success(places);
    }

    public static ValidationResult validerTrajetComplet(String depart, String destination,
                                                        LocalDate date, String heure, Integer places) {
        ValidationResult departResult = validerLieu(depart, "Le depart");
        if (!departResult.isValid()) {
            return departResult;
        }

        ValidationResult destResult = validerLieu(destination, "La destination");
        if (!destResult.isValid()) {
            return destResult;
        }

        String departClean = (String) departResult.getValue();
        String destClean = (String) destResult.getValue();
        if (departClean.equalsIgnoreCase(destClean)) {
            return ValidationResult.error("Le depart et la destination ne peuvent pas etre identiques.");
        }

        ValidationResult dateTimeResult = validerDateTimeDepart(date, heure, false);
        if (!dateTimeResult.isValid()) {
            return dateTimeResult;
        }

        ValidationResult placesResult = validerPlaces(places);
        if (!placesResult.isValid()) {
            return placesResult;
        }

        return ValidationResult.success(null);
    }

    public static class ValidationResult {
        private final boolean valid;
        private final String errorMessage;
        private final Object value;

        private ValidationResult(boolean valid, String errorMessage, Object value) {
            this.valid = valid;
            this.errorMessage = errorMessage;
            this.value = value;
        }

        public static ValidationResult success(Object value) {
            return new ValidationResult(true, null, value);
        }

        public static ValidationResult error(String message) {
            return new ValidationResult(false, message, null);
        }

        public boolean isValid() {
            return valid;
        }

        public String getErrorMessage() {
            return errorMessage;
        }

        public Object getValue() {
            return value;
        }
    }
}