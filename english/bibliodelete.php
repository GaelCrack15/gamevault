<?php
// Inicia la sesión (asegúrate de hacerlo antes de cualquier salida en la página)
session_start();

// Include config file
require_once "config.php";

    if (isset($_SESSION['idUsuario']) && !empty($_SESSION['idUsuario'])) {
        $idBibliotecaPersonal = $_POST["idBibliotecaPersonal"];
        $idUsuario = $_SESSION['idUsuario'];

        // Llamar al procedimiento almacenado para eliminar la reseña de la base de datos
        $sqlCallEliminarBibliotecaPersonal = "CALL EliminarBibliotecaPersonal(?)";
        if ($sqlCallEliminarBibliotecaPersonal = $mysqli->prepare($sqlCallEliminarBibliotecaPersonal)) {
            // Bind variable a la sentencia preparada como parámetro
            $sqlCallEliminarBibliotecaPersonal->bind_param("i", $idBibliotecaPersonal);

            // Ejecutar la sentencia preparada
            if ($sqlCallEliminarBibliotecaPersonal->execute()) {
                // Redirigir al detalle del juego después de eliminar la reseña
                header("location: profile.php?id=" . $idUsuario);
                exit();
            } else {
                echo "Failed to delete in library.";
            }

            // Cerrar la sentencia preparada
            $sqlCallEliminarBibliotecaPersonal->close();
        } else {
            echo "Query preparation error.";
        }
    } else {
        // Si el usuario no ha iniciado sesión, redirigir al inicio de sesión o página de registro
        header("location: start.php");
        exit();
    }
?>
