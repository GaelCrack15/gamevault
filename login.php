<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

require_once "config.php";

//inicializamos las variables 
$correo = $contraseña = "";
$correo_err = $contraseña_err = $login_err = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // verifica si la entrada de username esta vacía 
    if(empty(trim($_POST["correo"]))){
        $correo_err = "Por favor, ingrese su correo.";
    } else{
        $correo = trim($_POST["correo"]);
    }
    
    // verifica si la entrada de password esta vacía
    if(empty(trim($_POST["contraseña"]))){
        $contraseña_err = "Por favor, ingrese su contraseña";
    } else{
        $contraseña = trim($_POST["contraseña"]);
    }
    
    // Valida credenciales 
    if(empty($correo_err) && empty($contraseña_err)){
        // Prepare a select statement
        $sql = "SELECT * FROM Usuarios WHERE correo = ?;";
        
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_correo);
            
            // Set parameters
            $param_correo = $correo;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->store_result();
                
                // Check if email exists, if yes then verify password
                if($stmt->num_rows == 1){                    
                    // Bind result variables
                    $stmt->bind_result($idUsuario, $correo, $nombreUsuario, $contrasenaHash, $adm);
                    if($stmt->fetch()){
                        if(password_verify($contraseña, $contrasenaHash)){
                            // Password is correct, so start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["idUsuario"] = $idUsuario;
                            $_SESSION["correo"] = $correo;
                            $_SESSION["nombreUsuario"] = $nombreUsuario;
                            $_SESSION["adm"] = $adm;              
                            
                            // Redirect user to welcome page
                            header("location: index.php");
                        } else{
                            // Password is not valid, display a generic error message
                            $login_err = "Correo o contraseña incorrecta.";
                        }
                    }
                } else{
                    // Username doesn't exist, display a generic error message
                    $login_err = "Correo o contraseña incorrecta.";
                }
            } else{
                echo "Ocurrió un error, intente de nuevo más tarde.";
            }

            // Close statement
            $stmt->close();
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
    <title>Iniciar sesión</title>
    <link rel="icon" href="img/favicon.png" type="">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/stylelogin.css">
  </head>
  <body>
    <section>
      <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
          <div class="col-12 col-md-8 col-lg-6 col-xl-5">
            <div class="card bg-dark text-white">
              <div class="card-body p-5 text-center">
                <div class="tarjeta">

                  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <h2 class="fw-bold mb-2 text-uppercase">Inicio de sesión</h2>
                    <p class="text-white-50 mb-5">Por favor, introduzca su correo y contraseña</p>

                    <div class="form-group">
                      <input type="email" name="correo" class="form-control <?php echo (!empty($correo_err)) ? 'is-invalid' : ''; ?>" aria-describedby="emailHelp" placeholder="Correo" id="email" value="<?php echo $correo; ?>" required>
                      <span class="invalid-feedback"><?php echo $correo_err; ?></span>
                    </div>

                    <div class="form-group">
                      <input type="password" name="contraseña" class="form-control <?php echo (!empty($contraseña_err)) ? 'is-invalid' : ''; ?>" placeholder="Contraseña" id="contraseña" value="<?php echo $contraseña; ?>" required>
                      <span class="invalid-feedback"><?php echo $contraseña_err; ?></span>
                    </div>

                    <button class="btn btn-outline-light btn-lg px-5" type="submit">Iniciar sesión</button>

                    <div>
                      <p id="tienes" class="mb-0">¿No tienes una cuenta? <a href="register.php" class="text-white-50 fw-bold">Registrarse</a></p>
                    </div>
                  </form>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </body>
</html>