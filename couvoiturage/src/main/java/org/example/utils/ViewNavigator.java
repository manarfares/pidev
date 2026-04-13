package org.example.utils;

import javafx.fxml.FXMLLoader;
import javafx.scene.Node;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.stage.Stage;
import org.example.MainApp;

import java.io.IOException;
import java.util.function.Consumer;

public final class ViewNavigator {
    private ViewNavigator() {
    }

    public static void navigate(Node sourceNode, String fxmlPath) throws IOException {
        navigate(sourceNode, fxmlPath, controller -> {
        });
    }

    public static void navigate(Node sourceNode, String fxmlPath, Consumer<Object> controllerConfigurer) throws IOException {
        FXMLLoader loader = new FXMLLoader(MainApp.class.getResource(fxmlPath));
        Parent root = loader.load();
        controllerConfigurer.accept(loader.getController());

        Stage stage = (Stage) sourceNode.getScene().getWindow();
        Scene scene = new Scene(root, 1100, 720);
        scene.getStylesheets().add(MainApp.class.getResource("/org/example/styles/app.css").toExternalForm());
        stage.setScene(scene);
        stage.show();
    }
}
