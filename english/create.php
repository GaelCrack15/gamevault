<?php
    // Include config file
    require_once "config.php";

    session_start();

    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: start.html");
        exit;
    }

    // Define variables and initialize with empty values
    $titulo = $genero = $plataforma = $desarrolladora = $fechaLanzamiento = $descripcion = "";
    $titulo_err = $genero_err = $plataforma_err = $desarrolladora_err = $fechaLanzamiento_err = $descripcion_err = $fotoJuego_err = "";

    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate titulo
        $input_titulo = trim($_POST["titulo"]);
        if (empty($input_titulo)) {
            $titulo_err = "Please enter a game title.";
        } else {
            // Prepare a select statement
            $sql = "SELECT idJuego FROM Juegos WHERE titulo = ?";
            
            if($stmt = $mysqli->prepare($sql)){
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("s", $input_titulo);
                
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

        // Validate genero
        $input_genero = trim($_POST["genero"]);
        if (empty($input_genero)) {
            $genero_err = "Please enter game gender(s).";
        } else {
            $genero = $input_genero;
        }

        // Validate plataforma
        $input_plataforma = trim($_POST["plataforma"]);
        if (empty($input_plataforma)) {
            $plataforma_err = "Please enter the platform(s).";
        } else {
            $plataforma = $input_plataforma;
        }

        // Validate desarrolladora
        $input_desarrolladora = trim($_POST["desarrolladora"]);
        if (empty($input_desarrolladora)) {
            $desarrolladora_err = "Please enter the developer(s).";
        } else {
            $desarrolladora = $input_desarrolladora;
        }
    
        // Validate fecha de lanzamiento
        $input_fechaLanzamiento = trim($_POST["fechaLanzamiento"]);
        if (empty($input_fechaLanzamiento)) {
            $fechaLanzamiento_err = "Please enter the release date.";
        } else {
            $fechaLanzamiento = $input_fechaLanzamiento;
        }

        // Validate descripcion
        $input_descripcion = trim($_POST["descripcion"]);
        if (empty($input_descripcion)) {
            $descripcion_err = "Please enter a description..";
        } else {
            $descripcion = $input_descripcion;
        }

        // Validate foto del juego
            $file = $_FILES["fotoJuego"];
            $fileName =  $_FILES["fotoJuego"]["name"];
            $fileTmpName =  $_FILES["fotoJuego"]["tmp_name"];
            $fileSize =  $_FILES["fotoJuego"]["size"];
            $fileError =  $_FILES["fotoJuego"]["error"];
            $fileType =  $_FILES["fotoJuego"]["type"];

            $fileExt = explode('.', $fileName);
            $fileActualExt = strtolower(end($fileExt));

            $allowed = array('jpg', 'jpeg', 'png');

            if (in_array($fileActualExt, $allowed)) {
                if ($fileSize < 5242880) {
                    if ($fileError === 0) {
                        $fileNameNew = uniqid('', true).".".$fileActualExt;
                        $fileDestination = 'uploads/'.$fileNameNew;
                    } else {
                        echo '<script>alert("An error has occurred with the image, please try again later");</script>';
                        $fotoJuego_err = "An error has occurred with the image, please try again later.";
                    }
                } else {
                    echo '<script>alert("The image is too big, please choose a smaller image");</script>';
                    $fotoJuego_err = "The image is too big, please choose a smaller image.";
                }
            } else{
                echo '<script>alert("Format not accepted, choose another image please");</script>';
                $fotoJuego_err = "Format not accepted, choose another image please.";
            }

        // Check input errors before inserting in database
        if (empty($titulo_err) && empty($genero_err) && empty($plataforma_err) && empty($desarrolladora_err) && empty($fechaLanzamiento_err) && empty($descripcion_err) && empty($fotoJuego_err)) {
            move_uploaded_file($fileTmpName, "../".$fileDestination);
            // Prepare an insert statement
            $sql = "CALL InsertarJuego(?, ?, ?, ?, ?, ?, ?)";
            if ($stmt = $mysqli->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("sssssss", $param_titulo, $param_genero, $param_plataforma, $param_desarrolladora, $param_fechaLanzamiento, $param_descripcion, $param_fotoJuego);
            
                // Set parameters
                $param_titulo = $titulo;
                $param_genero = $genero;
                $param_plataforma = $plataforma;
                $param_desarrolladora = $desarrolladora;
                $param_fechaLanzamiento = $fechaLanzamiento;
                $param_descripcion = $descripcion;
                $param_fotoJuego = $fileDestination;

                if ($stmt->execute()) {
                    // Records created successfully. Redirect to landing page
                    header("location: index.php");
                    exit();
                } else {
                    echo "There was an error, please try again later.";
                }

                // Close statement
                $stmt->close();
            }
        } else {
            // Mostrar los mensajes de aviso si alguna variable está vacía
            if (empty($input_titulo)) {
                echo '<script>alert("Please enter a game title.");</script>';
            }
            if (empty($input_genero)) {
                echo '<script>alert("Please enter game gender(s).");</script>';
            }
            if (empty($input_plataforma)) {
                echo '<script>alert("Please enter the platform(s).");</script>';
            }
            if (empty($input_desarrolladora)) {
                echo '<script>alert("Please enter the developer(s).");</script>';
            }
            if (empty($input_fechaLanzamiento)) {
                echo '<script>alert("Please enter the release date.");</script>';
            }
            if (empty($input_descripcion)) {
                echo '<script>alert("Please enter a description.");</script>';
            }
        }
    
        // Close connection
        $mysqli->close();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Game</title>
    <link rel="icon" href="img/favicon.png" type="">
    <link rel="stylesheet" href="css/stylecreate.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>
<body>
    <div class="container-contact100">
        <div class="wrap-contact100  bg-dark">
            <form class="contact100-form validate-form" action="create.php" method="POST" enctype="multipart/form-data">
                <span class="contact100-form-title">
                   Add a Game
                </span>
                <h6 class="text-center text-white">Please fill out this information</h6>

                <label for="titulo">Title</label>
                <div class="wrap-input100 validate-input" data-validate="Please add the title of the game!">
                    <input class="input100 <?php echo(!empty($titulo_err)) ? 'is-invalid' : ''; ?>" type="text" name="titulo" placeholder="Game title" required>
                    <span class="error-message"><?php echo $titulo_err; ?></span>
                </div>

                <label for="genero">Genre</label>
                <div class="wrap-input100 validate-input" data-validate="Please add at least 1 genre!">
                    <input class="input100 <?php echo(!empty($genero_err)) ? 'is-invalid' : ''; ?>" type="text" name="genero" placeholder="Genre" required>
                    <span class="error-message"><?php echo $genero_err; ?></span>
                </div>

                <label for="plataforma">Platform</label>
                <div class="wrap-input100 validate-input" data-validate="Please add at least 1 platform!">
                    <input class="input100 <?php echo(!empty($plataforma_err)) ? 'is-invalid' : ''; ?>" name="plataforma" placeholder="Platforms" required>
                    <span class="error-message"><?php echo $plataforma_err; ?></span>
                </div>

                <label for="desarrolladora">Developer</label>
                <div class="wrap-input100 validate-input" data-validate="Please add the developer of the game!">
                    <input class="input100 <?php echo(!empty($desarrolladora_err)) ? 'is-invalid' : ''; ?>" name="desarrolladora" placeholder="Developer" required>
                    <span class="error-message"><?php echo $desarrolladora_err; ?></span>
                </div>

                <label for="fechaLanzamiento">Launch date</label>
                <div class="wrap-input100 validate-input" data-validate="Please specify the launch date of the game!">
                    <input type="date" class="input100 <?php echo(!empty($fechaLanamiento_err)) ? 'is-invalid' : ''; ?>" name="fechaLanzamiento" required>
                    <span class="error-message"><?php echo $fechaLanzamiento_err; ?></span>
                </div>

                <label for="descripcion">Description</label>
                <div class="wrap-input100 validate-input" data-validate="Please add a description to the game!">
                    <textarea class="input100 <?php echo(!empty($descripcion_err)) ? 'is-invalid' : ''; ?>" name="descripcion" cols="30" rows="10" placeholder="Small description of the game" required></textarea>
                    <span class="error-message"><?php echo $descripcion_err; ?></span>
                </div>

                <label for="foto">Game photo</label>
                <div class="wrap-input100 validate-input" id="fotoJuego" data-validate="Please select a valid image for the game's page!">
                    <input <?php echo(!empty($fotoJuego_err)) ? 'is-invalid' : ''; ?>" type="file" name="fotoJuego" accept="image/png, image/jpeg" required>
                    <span class="error-message"><?php echo $fotoJuego_err; ?></span>
                </div>

                <div class="container-contact100-form-btn">
                    <button type="submit" class="contact100-form-btn">
                        Submit
                    </button>
                    <button type="reset" class="contact99-form-btn" onclick="window.location.href = 'index.php';">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
