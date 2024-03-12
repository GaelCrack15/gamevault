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
            $titulo_err = "Por favor ingrese un título.";
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
                        $titulo_err = "Este título ya está registrado.";
                    } else{
                        $titulo = $input_titulo;
                    }
                } else{
                    echo "Ocurrió un error, intente de nuevo más tarde.";
                }
                // Close statement
                $stmt->close();
            }
        }

        // Validate genero
        $input_genero = trim($_POST["genero"]);
        if (empty($input_genero)) {
            $genero_err = "Por favor ingrese un género.";
        } else {
            $genero = $input_genero;
        }

        // Validate plataforma
        $input_plataforma = trim($_POST["plataforma"]);
        if (empty($input_plataforma)) {
            $plataforma_err = "Por favor ingrese una plataforma.";
        } else {
            $plataforma = $input_plataforma;
        }

        // Validate desarrolladora
        $input_desarrolladora = trim($_POST["desarrolladora"]);
        if (empty($input_desarrolladora)) {
            $desarrolladora_err = "Por favor ingrese la desarrolladora.";
        } else {
            $desarrolladora = $input_desarrolladora;
        }
    
        // Validate fecha de lanzamiento
        $input_fechaLanzamiento = trim($_POST["fechaLanzamiento"]);
        if (empty($input_fechaLanzamiento)) {
            $fechaLanzamiento_err = "Por favor ingrese la fecha de lanzamiento.";
        } else {
            $fechaLanzamiento = $input_fechaLanzamiento;
        }

        // Validate descripcion
        $input_descripcion = trim($_POST["descripcion"]);
        if (empty($input_descripcion)) {
            $descripcion_err = "Por favor ingrese una descripción.";
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
                        echo '<script>alert("Ocurrió un error con la imagen, intente de nuevo por favor");</script>';
                        $fotoJuego_err = "Ocurrió un error con la imagen, intente de nuevo por favor";
                    }
                } else {
                    echo '<script>alert("La imagen es demasiado grande, elija una imagen más pequeña por favor");</script>';
                    $fotoJuego_err = "La imagen es demasiado grande, elija una imagen más pequeña por favor";
                }
            } else{
                echo '<script>alert("Formato no aceptado, elija otra imagen por favor");</script>';
                $fotoJuego_err = "Formato no aceptado, elija otra imagen por favor";
            }

        // Check input errors before inserting in database
        if (empty($titulo_err) && empty($genero_err) && empty($plataforma_err) && empty($desarrolladora_err) && empty($fechaLanzamiento_err) && empty($descripcion_err) && empty($fotoJuego_err)) {
            move_uploaded_file($fileTmpName, $fileDestination);
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
                    echo "Hubo un error, intente de nuevo más tarde.";
                }

                // Close statement
                $stmt->close();
            }
        } else {
            // Mostrar los mensajes de aviso si alguna variable está vacía
            if (empty($input_titulo)) {
                echo '<script>alert("Por favor ingrese un título.");</script>';
            }
            if (empty($input_genero)) {
                echo '<script>alert("Por favor ingrese un género.");</script>';
            }
            if (empty($input_plataforma)) {
                echo '<script>alert("Por favor ingrese una plataforma.");</script>';
            }
            if (empty($input_desarrolladora)) {
                echo '<script>alert("Por favor ingrese una desarrolladora.");</script>';
            }
            if (empty($input_fechaLanzamiento)) {
                echo '<script>alert("Por favor ingrese una fecha de lanzamiento.");</script>';
            }
            if (empty($input_descripcion)) {
                echo '<script>alert("Por favor ingrese una descripción.");</script>';
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
    <title>Agrega un juego</title>
    <link rel="icon" href="img/favicon.png" type="">
    <link rel="stylesheet" href="css/stylecreate.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>
<body>
    <div class="container-contact100">
        <div class="wrap-contact100  bg-dark">
            <form class="contact100-form validate-form" action="create.php" method="POST" enctype="multipart/form-data">
                <span class="contact100-form-title">
                    AGREGA UN JUEGO
                </span>
                <h6 class="text-center text-white">¡Completa la siguiente información!</h6>

                <label for="titulo">Título</label>
                <div class="wrap-input100 validate-input" data-validate="Añade el título del juego">
                    <input class="input100 <?php echo(!empty($titulo_err)) ? 'is-invalid' : ''; ?>" type="text" name="titulo" placeholder="Título" required>
                    <span class="error-message"><?php echo $titulo_err; ?></span>
                </div>

                <label for="genero">Género</label>
                <div class="wrap-input100 validate-input" data-validate="Añade el/los géneros del juego">
                    <input class="input100 <?php echo(!empty($genero_err)) ? 'is-invalid' : ''; ?>" type="text" name="genero" placeholder="Género" required>
                    <span class="error-message"><?php echo $genero_err; ?></span>
                </div>

                <label for="plataforma">Plataforma</label>
                <div class="wrap-input100 validate-input" data-validate="Añade una plataforma">
                    <input class="input100 <?php echo(!empty($plataforma_err)) ? 'is-invalid' : ''; ?>" name="plataforma" placeholder="Plataformas" required>
                    <span class="error-message"><?php echo $plataforma_err; ?></span>
                </div>

                <label for="desarrolladora">Desarrolladora</label>
                <div class="wrap-input100 validate-input" data-validate="Añade el desarrollador">
                    <input class="input100 <?php echo(!empty($desarrolladora_err)) ? 'is-invalid' : ''; ?>" name="desarrolladora" placeholder="Desarrolladora" required>
                    <span class="error-message"><?php echo $desarrolladora_err; ?></span>
                </div>

                <label for="fechaLanzamiento">Fecha de lanzamiento</label>
                <div class="wrap-input100 validate-input" data-validate="Añade la fecha de lanzamiento">
                    <input type="date" class="input100 <?php echo(!empty($fechaLanamiento_err)) ? 'is-invalid' : ''; ?>" name="fechaLanzamiento" required>
                    <span class="error-message"><?php echo $fechaLanzamiento_err; ?></span>
                </div>

                <label for="descripcion">Descripción</label>
                <div class="wrap-input100 validate-input" data-validate="Añade una descripción del juego">
                    <textarea class="input100 <?php echo(!empty($descripcion_err)) ? 'is-invalid' : ''; ?>" name="descripcion" cols="30" rows="10" placeholder="Descripcion" required></textarea>
                    <span class="error-message"><?php echo $descripcion_err; ?></span>
                </div>

                <label for="foto">Foto del juego</label>
                <div class="wrap-input100 validate-input" id="fotoJuego" data-validate="Añade una foto del juego">
                    <input <?php echo(!empty($fotoJuego_err)) ? 'is-invalid' : ''; ?>" type="file" name="fotoJuego" accept="image/png, image/jpeg" required>
                    <span class="error-message"><?php echo $fotoJuego_err; ?></span>
                </div>

                <div class="container-contact100-form-btn">
                    <button type="submit" class="contact100-form-btn">
                        Enviar
                    </button>
                    <button type="reset" class="contact99-form-btn" onclick="window.location.href = 'index.php';">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>
