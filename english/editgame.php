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

if (isset($_SESSION['idUsuario']) && !empty($_SESSION['idUsuario'])) {
    // Validar los datos del formulario
    $idUsuario = $_SESSION['idUsuario'];
    $idJuego = $_GET['id'];

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
                // Cerramos el statement
                $stmt->close();
                header("location: index.php");
                exit;
            }

            // Cerramos el statement
            $stmt->close();
        } else {
            // Manejo de error de consulta
            echo "Error in the query for administrator permissions.";
            exit;
        }
    } else {
        // Manejo de error de preparación de consulta
        echo "Query preparation error.";
        exit;
    }

     

        // Obtener los detalles del juego según su ID
        $sql = "CALL ConsultarJuego(?)";
        if ($stmt = $mysqli->prepare($sql)) {
            // Bind variables a la sentencia preparada como parámetros
            $stmt->bind_param("i", $idJuego);

            // Ejecutar la sentencia preparada
            if ($stmt->execute()) {
                $resultado = $stmt->get_result();

                if ($resultado->num_rows == 1) {
                    // Obtener los datos del juego
                    $juego = $resultado->fetch_assoc();
                    $titulo = $juego["titulo"];
                    $genero = $juego["genero"];
                    $plataforma = $juego["plataformas"];
                    $desarrolladora = $juego["desarrollador"];
                    $fechaLanzamiento = $juego["fechaLanzamiento"];
                    $descripcion = $juego["descripcion"];
                    $promedio = $juego["promedio"];
                    $fotoJuegol = $juego["fotoJuego"];
                    $fotoJuego = "../".$fotoJuegol;
                } else {
                    // Si no se encuentra el juego, redirigir al índice
                    header("location: index.php");
                    exit();
                }
            } else {
                // Si ocurre algún error en la consulta, mostrar mensaje de error y redirigir al índice
                echo "Error getting game details.";
                header("location: index.php");
                exit();
            }

            // Cerrar la sentencia preparada
            $stmt->close();
        } else {
            // Si ocurre algún Query preparation error, mostrar mensaje de error y redirigir al índice
            echo "Query preparation error.";
            header("location: index.php");
            exit();
        }

        // Define variables and initialize with empty values
        $titulo_err = $genero_err = $plataforma_err = $desarrolladora_err = $fechaLanzamiento_err = $descripcion_err = $fotoJuego_err = "";

        // Processing form data when form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Validate titulo
            $input_titulo = trim($_POST["titulo"]);
            if ($input_titulo !== $titulo) {
                if (empty($input_titulo)) {
                    $titulo_err = "Please enter a game title.";
                } else {
                    // Prepare a select statement
                    $sql = "SELECT idJuego FROM Juegos WHERE titulo = ? AND idJuego != ?";
                    
                    if($stmt = $mysqli->prepare($sql)){
                        // Bind variables to the prepared statement as parameters
                        $stmt->bind_param("si", $input_titulo, $idJuego);
                        
                        // Attempt to execute the prepared statement
                        if($stmt->execute()){
                            // store result
                            $stmt->store_result();
                            
                            if($stmt->num_rows == 1){
                                $titulo_err = "This title is already registered.";
                            } else{
                                $titulo = $input_titulo;
                            }
                        } else{
                            echo "An error has occurred, try again later.";
                        }
                        // Close statement
                        $stmt->close();
                    }
                }   
            }

            // Validate genero
            $input_genero = trim($_POST["genero"]);
            if ($input_genero !== $genero) {
                if (empty($input_genero)) {
                    $genero_err = "Please enter game gender(s).";
                } else {
                    $genero = $input_genero;
                }   
            }

            // Validate plataforma
            $input_plataforma = trim($_POST["plataforma"]);
            if ($input_plataforma !== $plataforma) {
                if (empty($input_plataforma)) {
                    $plataforma_err = "Please enter the platform(s).";
                } else {
                    $plataforma = $input_plataforma;
                }
            }

            // Validate desarrolladora
            $input_desarrolladora = trim($_POST["desarrolladora"]);
            if ($input_desarrolladora !== $desarrolladora) {
                if (empty($input_desarrolladora)) {
                    $desarrolladora_err = "Please enter the developer(s).";
                } else {
                    $desarrolladora = $input_desarrolladora;
                }
            }
        
            // Validate fecha de lanzamiento
            $input_fechaLanzamiento = trim($_POST["fechaLanzamiento"]);
            if ($input_fechaLanzamiento !== $fechaLanzamiento) {
                if (empty($input_fechaLanzamiento)) {
                    $fechaLanzamiento_err = "Please enter the release date.";
                } else {
                    $fechaLanzamiento = $input_fechaLanzamiento;
                }
            }

            // Validate descripcion
            $input_descripcion = trim($_POST["descripcion"]);
            if ($input_descripcion !== $descripcion) {
                if (empty($input_descripcion)) {
                    $descripcion_err = "Please enter a description.";
                } else {
                    $descripcion = $input_descripcion;
                }
            }

            // Check input errors before inserting in database
            if (empty($titulo_err) && empty($genero_err) && empty($plataforma_err) && empty($desarrolladora_err) && empty($fechaLanzamiento_err) && empty($descripcion_err)) {

                // Prepare an insert statement
                $sql = "CALL ActualizarJuego(?, ?, ?, ?, ?, ?, ?)";
                if ($stmt = $mysqli->prepare($sql)) {
                    // Bind variables to the prepared statement as parameters
                    $stmt->bind_param("issssss", $idJuego, $titulo, $genero, $plataforma, $desarrolladora, $fechaLanzamiento, $descripcion);
            

                    if ($stmt->execute()) {
                        // Records created successfully. Redirect to landing page
                        if (isset($_FILES["image"]["name"])) { // Verificamos el método de solicitud
                            $name = $_POST["name"];

                            $imageName = $_FILES["image"]["name"];
                            $imageSize = $_FILES["image"]["size"];
                            $tmpName = $_FILES["image"]["tmp_name"];

                            // Image validation
                            $validImageExtension = ['jpg', 'jpeg', 'png'];
                            $imageExtension = explode('.', $imageName);
                            $fileActualExt = strtolower(end($imageExtension));
                            
                            if (!in_array($fileActualExt, $validImageExtension)) {
                                echo "<script>
                                        alert('Wrong photo format');
                                    </script>";
                                header("location: game.php?id=" . $idJuego);
                                exit();
                            } else {
                                if ($imageSize > 5242880) {
                                    echo "<script>
                                            alert('Wrong photo size');
                                        </script>";
                                    header("location: game.php?id=" . $idJuego);
                                    exit();
                                } else{
                                    $fileNameNew = uniqid('', true).".".$fileActualExt;
                                    $fileDestination = 'uploads/'.$fileNameNew;
                                    // Update database
                                    $query = "UPDATE Juegos SET fotoJuego = '$fileDestination' WHERE idJuego = $idJuego";
                                    $result = mysqli_query($mysqli, $query);

                                    if ($result) { // Check if the query was successful
                                        unlink($fotoJuego);
                                        move_uploaded_file($tmpName, "../".$fileDestination);
                                        header("location: game.php?id=". $idJuego);
                                    exit();
                                    } else {
                                        echo "<script>
                                                alert('Error updating database');
                                            </script>";
                                            header("location: game.php?id=" . $idJuego);
                                            exit();
                                    }
                                }
                            }
                        }
                    } else {
                        echo "An error has occurred, try again later.";
                    }

                    // Close statement
                    $stmt->close();
                }
            }
        
            // Close connection
            $mysqli->close();
        }
    
} else {
    // Si el usuario no ha iniciado sesión, redirigir al inicio de sesión o página de registro
    header("location: start.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo $titulo?></title>
    <link rel="icon" href="img/favicon.png" type="">
    <link rel="stylesheet" href="css/stylecreate.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>
<body>
    <div class="container-contact100">
        <div class="wrap-contact100  bg-dark">
            <form class="contact100-form validate-form" action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="POST" enctype="multipart/form-data">
                <span class="contact100-form-title">
                    EDITING: <?php echo $titulo?>
                </span>
                <h6 class="text-center text-white">Edit what's necessary</h6>

                <label for="titulo">Game title</label>
                <div class="wrap-input100 validate-input" data-validate="Add the game title">
                    <input class="input100 <?php echo(!empty($titulo_err)) ? 'is-invalid' : ''; ?>" type="text" name="titulo" placeholder="Título" value="<?php echo $titulo?>">
                    <span class="error-message"><?php echo $titulo_err; ?></span>
                </div>

                <label for="genero">Genre(s)</label>
                <div class="wrap-input100 validate-input" data-validate="Add the genre(s) of the game">
                    <input class="input100 <?php echo(!empty($genero_err)) ? 'is-invalid' : ''; ?>" type="text" name="genero" placeholder="Género" value="<?php echo $genero?>">
                    <span class="error-message"><?php echo $genero_err; ?></span>
                </div>

                <label for="plataforma">Platform(s)</label>
                <div class="wrap-input100 validate-input" data-validate="Add the platform(s)">
                    <input class="input100 <?php echo(!empty($plataforma_err)) ? 'is-invalid' : ''; ?>" name="plataforma" placeholder="Plataformas" value="<?php echo $plataforma?>">
                    <span class="error-message"><?php echo $plataforma_err; ?></span>
                </div>

                <label for="desarrolladora">Developer(s)</label>
                <div class="wrap-input100 validate-input" data-validate="Add the developer(s)">
                    <input class="input100 <?php echo(!empty($desarrolladora_err)) ? 'is-invalid' : ''; ?>" name="desarrolladora" placeholder="Desarrolladora" value="<?php echo $desarrolladora?>">
                    <span class="error-message"><?php echo $desarrolladora_err; ?></span>
                </div>

                <label for="fechaLanzamiento">Release date</label>
                <div class="wrap-input100 validate-input" data-validate="Add the release date">
                    <input type="date" class="input100 <?php echo(!empty($fechaLanamiento_err)) ? 'is-invalid' : ''; ?>" name="fechaLanzamiento" value="<?php echo $fechaLanzamiento?>">
                    <span class="error-message"><?php echo $fechaLanzamiento_err; ?></span>
                </div>

                <label for="descripcion">Game description</label>
                <div class="wrap-input100 validate-input" data-validate="Add a game description">
                    <textarea class="input100 <?php echo(!empty($descripcion_err)) ? 'is-invalid' : ''; ?>" name="descripcion" cols="30" rows="10" placeholder="Descripcion"><?php echo $descripcion?></textarea>
                    <span class="error-message"><?php echo $descripcion_err; ?></span>
                </div>

                <label for="foto">Game photo</label>
                <div class="wrap-input100 validate-input" id="fotoJuego" data-validate="Add a picture of the game">
                    <input <?php echo(!empty($fotoJuego_err)) ? 'is-invalid' : ''; ?>" type="file" name="image" accept="image/png, image/jpeg">
                    <span class="error-message"><?php echo $fotoJuego_err; ?></span>
                </div>

                <div class="container-contact100-form-btn">
                    <button type="submit" class="contact100-form-btn">
                        Submit
                    </button>
                    <button type="reset" class="contact99-form-btn" onclick="window.location.href = 'game.php?id=<?php echo $idJuego; ?>';">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
