<?php
// Include config file
require_once "config.php";

// Iniciar la sesión antes de cualquier salida o acceso a la sesión
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $idJuego = $_POST["idJuego"];
    $calificacion = $_POST["calificacion"];
    $comentario = $_POST["comentario"];

    // Validar los datos del formulario aquí si es necesario
    if (empty($idJuego) || empty($calificacion)) {
        echo "Please add a rating.";
        exit;
    }

    // Insertar la reseña en la base de datos utilizando el procedimiento almacenado
    $sql = "CALL InsertarResena(?, ?, ?, ?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("iiis", $_SESSION['idUsuario'], $idJuego, $calificacion, $comentario);
        if ($stmt->execute()) {
            // La reseña se guardó exitosamente, redirigir de vuelta a la página del juego con el ID correspondiente
            header("Location: game.php?id=" . $idJuego);
            exit();
        } else {
            echo "Error saving review.";
        }
        $stmt->close();
    } else {
        echo "<alert>An error has occurred, try again later</alert>";
        exit();
    }
}

// Cerrar la conexión a la base de datos
$mysqli->close();
?>
