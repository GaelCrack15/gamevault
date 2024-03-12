<?php
// Include config file
require_once "config.php";

// Iniciar la sesión antes de cualquier salida o acceso a la sesión
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['idUsuario']) && !empty($_SESSION['idUsuario'])) {
        // Validar los datos del formulario
        $idUsuario = $_POST["idUsuario"];
        $idJuego = $_POST["idJuego"];

        // Llamar al procedimiento almacenado para actualizar la reseña en la base de datos
        $sqlCallInsertarBibliotecaPersonal = "CALL InsertarBibliotecaPersonal(?, ?)";
        if ($sqlCallInsertarBibliotecaPersonal = $mysqli->prepare($sqlCallInsertarBibliotecaPersonal)) {
            // Bind variables a la sentencia preparada como parámetros
            $sqlCallInsertarBibliotecaPersonal->bind_param("ii", $idUsuario, $idJuego);

            // Ejecutar la sentencia preparada
            if ($sqlCallInsertarBibliotecaPersonal->execute()) {
                // Redirigir al detalle del juego después de actualizar la reseña
                header("location: profile.php?id=" . $idUsuario);
                exit();
            } else {
                echo "Error al actualizar la reseña.";
            }

            // Cerrar la sentencia preparada
            $sqlCallInsertarBibliotecaPersonal->close();
        } else {
            echo "Error en la preparación de la consulta.";
        }
    } else {
        // Si el usuario no ha iniciado sesión, redirigir al inicio de sesión o página de registro
        header("location: start.php");
        exit();
    }
}   else {
    // Si el usuario intenta acceder directamente a esta página sin enviar el formulario, redirigir a la página de inicio o detalle del juego
    header("location: game.php?id=" . $_POST["idJuego"]);
    exit();
}

// Cerrar la conexión a la base de datos
$mysqli->close();
?>
