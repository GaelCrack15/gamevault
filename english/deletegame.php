<?php
// Include config file
require_once "config.php";

// Inicia la sesión
session_start();

// Check if the user is logged in, if not then redirect him to start page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: start.html");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['idUsuario']) && !empty($_SESSION['idUsuario'])) {
        // Validar los datos del formulario
        $idUsuario = $_SESSION['idUsuario'];
        $idJuego = $_POST["idJuego"];

        // Preparamos la consulta para obtener el rol de administrador del usuario
        $sql = "SELECT adm FROM Usuarios WHERE idUsuario = ?";
        if ($stmt = $mysqli->prepare($sql)) {
            // Vinculamos el id del usuario como parámetro
            $stmt->bind_param("i", $idUsuario);

            // Ejecutamos la consulta
            if ($stmt->execute()) {
                // Vinculamos el resultado de la consulta a una variable
                $stmt->bind_result($adm);

                // Obtenemos el resultado de la consulta
                $stmt->fetch();

                if (!$adm) {
                    echo "<script>alert('Restricted access, administrators only.');</script>";
                } else {
                    // Cerramos el statement
                    $stmt->close();

                    // Llamar al procedimiento almacenado para eliminar la reseña de la base de datos
                    $sqlEliminarReseñas = "DELETE FROM Resenas WHERE idJuego = ?";
                    if ($stmtEliminarReseñas = $mysqli->prepare($sqlEliminarReseñas)) {
                        // Bind variable a la sentencia preparada como parámetro
                        $stmtEliminarReseñas->bind_param("i", $idJuego);

                        // Ejecutar la sentencia preparada
                        if ($stmtEliminarReseñas->execute()) {
                            // Cerrar la sentencia preparada
                            $stmtEliminarReseñas->close();

                            // Llamar al procedimiento almacenado para eliminar la reseña de la base de datos
                            $sqlEliminarBiblio = "DELETE FROM BibliotecaPersonal WHERE idJuego = ?";
                            if ($stmtEliminarBiblio = $mysqli->prepare($sqlEliminarBiblio)) {
                                // Bind variable a la sentencia preparada como parámetro
                                $stmtEliminarBiblio->bind_param("i", $idJuego);

                                // Ejecutar la sentencia preparada
                                if ($stmtEliminarBiblio->execute()) {
                                    // Cerrar la sentencia preparada
                                    $stmtEliminarBiblio->close();

                                    // Preparamos la consulta para obtener la ruta de la foto de perfil
                                    $sql = "SELECT fotoJuego FROM Juegos WHERE idJuego = ?";
                                    if ($stmt = $mysqli->prepare($sql)) {
                                        // Vinculamos el id del usuario como parámetro
                                        $stmt->bind_param("i", $idJuego);

                                        // Ejecutamos la consulta
                                        if ($stmt->execute()) {
                                            // Vinculamos el resultado de la consulta a una variable
                                            $stmt->bind_result($rutaFotol);

                                            // Obtenemos el resultado de la consulta
                                            $stmt->fetch();

                                            // Cerramos el statement
                                            $stmt->close();
                                        }
                                    }

                                    $rutaFoto = "../".$rutaFotol;
                                    unlink($rutaFoto);

                                    // Llamar al procedimiento almacenado para eliminar el juego de la base de datos
                                    $sqlEliminarJuego = "CALL EliminarJuego(?)";
                                    if ($stmtEliminarJuego = $mysqli->prepare($sqlEliminarJuego)) {
                                        // Bind variable a la sentencia preparada como parámetro
                                        $stmtEliminarJuego->bind_param("i", $idJuego);

                                        // Ejecutar la sentencia preparada
                                        if ($stmtEliminarJuego->execute()) {
                                            // Redirigir al detalle del juego después de eliminar el juego
                                            header("location: index.php");
                                            exit();
                                        } else {
                                            echo "Error deleting the game.";
                                        }

                                        // Cerrar la sentencia preparada
                                        $stmtEliminarJuego->close();
                                    } else {
                                        echo "Query preparation error.";
                                    }
                                } else {
                                    echo "Error deleting game from personal library.";
                                }
                            } else {
                                echo "Query preparation error.";
                            }
                        } else {
                            echo "Error deleting reviews.";
                        }
                    } else {
                        echo "Query preparation error.";
                    }
                }
            }
        }
    } else {
        // Si el usuario no ha iniciado sesión, redirigir al inicio de sesión o página de registro
        header("location: start.php");
        exit();
    }
} else {
    // Si el usuario intenta acceder directamente a esta página sin enviar el formulario, redirigir a la página de inicio o detalle del juego
    header("location: index.php");
    exit();
}
?>
