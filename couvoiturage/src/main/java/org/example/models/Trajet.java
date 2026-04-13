package org.example.models;

import java.time.LocalDateTime;

public class Trajet {
    private long id;
    private String depart;
    private String destination;
    private LocalDateTime dateDepart;
    private int nombrePlaces;

    public Trajet() {
    }

    public Trajet(String depart, String destination, LocalDateTime dateDepart, int nombrePlaces) {
        this.depart = depart;
        this.destination = destination;
        this.dateDepart = dateDepart;
        this.nombrePlaces = nombrePlaces;
    }

    public Trajet(long id, String depart, String destination, LocalDateTime dateDepart, int nombrePlaces) {
        this.id = id;
        this.depart = depart;
        this.destination = destination;
        this.dateDepart = dateDepart;
        this.nombrePlaces = nombrePlaces;
    }

    public long getId() {
        return id;
    }

    public void setId(long id) {
        this.id = id;
    }

    public String getDepart() {
        return depart;
    }

    public void setDepart(String depart) {
        this.depart = depart;
    }

    public String getDestination() {
        return destination;
    }

    public void setDestination(String destination) {
        this.destination = destination;
    }

    public LocalDateTime getDateDepart() {
        return dateDepart;
    }

    public void setDateDepart(LocalDateTime dateDepart) {
        this.dateDepart = dateDepart;
    }

    public int getNombrePlaces() {
        return nombrePlaces;
    }

    public void setNombrePlaces(int nombrePlaces) {
        this.nombrePlaces = nombrePlaces;
    }
}
