<?php
// Inicia la sesión (asegúrate de hacerlo antes de cualquier salida en la página)
session_start();

// Include config file
require_once "config.php";

if (isset($_SESSION['idUsuario']) && !empty($_SESSION['idUsuario'])) {
    // Verificar si se envió el formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $idBibliotecaPersonal = $_POST['idBibliotecaPersonal'];
        $estadoJuego = $_POST['estadoJuego'];
        $idUsuario = $_SESSION['idUsuario'];

        // Preparar la llamada al procedimiento almacenado
        $sql = "CALL ActualizarBibliotecaPersonal(?, ?)";

        // Preparar y ejecutar la consulta
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("ii", $idBibliotecaPersonal, $estadoJuego);

        if ($stmt->execute()) {
            // Actualización exitosa
            header("Location: profile.php?id=" . $idUsuario); // Redireccionar a la página de la lista de juegos
            exit();
        } else {
            echo "Error updating game state " . $stmt->error;
        }

        $stmt->close();
    }
} else {
    // Si el usuario no ha iniciado sesión, redirigir al inicio de sesión o página de registro
    header("location: start.php");
    exit();
}

$mysqli->close();
?>
