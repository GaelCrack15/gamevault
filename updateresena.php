<?php
// Inicia la sesión (asegúrate de hacerlo antes de cualquier salida en la página)
session_start();

// Include config file
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['idUsuario']) && !empty($_SESSION['idUsuario'])) {
        // Validar los datos del formulario
        $idResena = $_POST["idResena"];
        $calificacion = $_POST["calificacion"];
        $comentario = $_POST["comentario"];

        // Llamar al procedimiento almacenado para actualizar la reseña en la base de datos
        $sqlCallActualizarResena = "CALL ActualizarResena(?, ?, ?)";
        if ($stmtCallActualizarResena = $mysqli->prepare($sqlCallActualizarResena)) {
            // Bind variables a la sentencia preparada como parámetros
            $stmtCallActualizarResena->bind_param("iis", $idResena, $calificacion, $comentario);

            // Ejecutar la sentencia preparada
            if ($stmtCallActualizarResena->execute()) {
                // Redirigir al detalle del juego después de actualizar la reseña
                header("location: game.php?id=" . $_POST["idJuego"]);
                exit();
            } else {
                echo "Error al actualizar la reseña.";
            }

            // Cerrar la sentencia preparada
            $stmtCallActualizarResena->close();
        } else {
            echo "Error en la preparación de la consulta.";
        }
    } else {
        // Si el usuario no ha iniciado sesión, redirigir a página de inicio
        header("location: start.php");
        exit();
    }
} else {
    // Si el usuario intenta acceder directamente a esta página sin enviar el formulario, redirigir a la página de detalle del juego
    header("location: game.php?id=" . $_POST["idJuego"]);
    exit();
}
?>
