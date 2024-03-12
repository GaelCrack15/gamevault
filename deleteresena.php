<?php
// Inicia la sesión (asegúrate de hacerlo antes de cualquier salida en la página)
session_start();

// Include config file
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['idUsuario']) && !empty($_SESSION['idUsuario'])) {
        // Validar los datos del formulario
        $idResena = $_POST["idResena"];
        $idJuego = $_POST["idJuego"];

        // Llamar al procedimiento almacenado para eliminar la reseña de la base de datos
        $sqlCallEliminarResena = "CALL EliminarResena(?)";
        if ($stmtCallEliminarResena = $mysqli->prepare($sqlCallEliminarResena)) {
            // Bind variable a la sentencia preparada como parámetro
            $stmtCallEliminarResena->bind_param("i", $idResena);

            // Ejecutar la sentencia preparada
            if ($stmtCallEliminarResena->execute()) {
                // Redirigir al detalle del juego después de eliminar la reseña
                header("location: game.php?id=" . $idJuego);
                exit();
            } else {
                echo "Error al eliminar la reseña.";
            }

            // Cerrar la sentencia preparada
            $stmtCallEliminarResena->close();
        } else {
            echo "Error en la preparación de la consulta.";
        }
    } else {
        // Si el usuario no ha iniciado sesión, redirigir a la página de inicio
        header("location: start.php");
        exit();
    }
} else {
    // Si el usuario intenta acceder directamente a esta página sin enviar el formulario, redirigir a la página de detalle del juego
    header("location: game.php?id=".$idJuego);
    exit();
}
?>
