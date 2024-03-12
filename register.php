<?php
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$nombreUsuario = $correo = $password = $confirm_password = "";
$nombreUsuario_err = $correo_err = $password_err = $confirm_password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate username
    if(empty(trim($_POST["nombreUsuario"]))){
        $nombreUsuario_err = "Por favor, ingrese un nombre del usuario.";
    } elseif(!preg_match('/^[a-zA-Z0-9._]+$/', trim($_POST["nombreUsuario"]))){
        $nombreUsuario_err = "El nombre de usuario solo puede tener letras, números, puntos y guion bajo.";
    } elseif (strlen(trim($_POST["nombreUsuario"])) > 18) {
      $nombreUsuario_err = "El nombre de usuario no puede tener más de 18 carácteres.";
    } else{
        // Prepare a select statement
        $sql = "SELECT idUsuario FROM usuarios WHERE nombreUsuario = ?";
        
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_nombreUsuario);
            
            // Set parameters
            $param_nombreUsuario = trim($_POST["nombreUsuario"]);
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // store result
                $stmt->store_result();
                
                if($stmt->num_rows == 1){
                    $nombreUsuario_err = "Este nombre de usuario ya está tomado.";
                } else{
                    $nombreUsuario = trim($_POST["nombreUsuario"]);
                }
            } else{
                echo "Ocurrió un error, intente de nuevo más tarde.";
            }

            // Close statement
            $stmt->close();
        }
    }

    //Validate email
    if(empty(trim($_POST["correo"]))){
      $correo_err = "Por favor, ingrese un correo electrónico.";
  } else{
      // Prepare a select statement
      $sql = "SELECT nombreUsuario FROM usuarios WHERE correo = ?";
      
      if($stmt = $mysqli->prepare($sql)){
          // Bind variables to the prepared statement as parameters
          $stmt->bind_param("s", $param_correo);
          
          // Set parameters
          $param_correo = trim($_POST["correo"]);
          
          // Attempt to execute the prepared statement
          if($stmt->execute()){
              // store result
              $stmt->store_result();
              
              if($stmt->num_rows == 1){
                  $correo_err = "Este correo ya está en uso.";
              } else{
                  $correo = trim($_POST["correo"]);
              }
          } else{
              echo "Ocurrió un error, intente de nuevo más tarde.";
          }

          // Close statement
          $stmt->close();
      }
  }
    
    // Validate password
    if(empty(trim($_POST["contraseña"]))){
        $password_err = "Por favor, ingrese una contraseña.";     
    } elseif(strlen(trim($_POST["contraseña"])) < 8){
        $password_err = "La contraseña debe tener al menos 8 carácteres.";
    } else{
        $password = trim($_POST["contraseña"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirmarContraseña"]))){
        $confirm_password_err = "Por favor, confirme la contraseña.";     
    } else{
        $confirm_password = trim($_POST["confirmarContraseña"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Las contraseñas no coinciden.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($nombreUsuario_err) && empty($correo_err) && empty($password_err) && empty($confirm_password_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO Usuarios (correo, nombreUsuario, contrasenaHash)
        VALUES (?, ?, ?)";
         
        if($stmt = $mysqli->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sss", $param_correo, $param_nombreUsuario, $param_password );
            
            // Set parameters
            $param_nombreUsuario = $nombreUsuario;
            $param_correo = $correo;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
              // Set parameter for the profile creation
              $idUsuario = $stmt->insert_id; // Obtener el ID del usuario recién insertado
              echo "ID del usuario recién insertado: " . $idUsuario;

              // Call the stored procedure to create a profile
              $sqlCreateProfile = "CALL InsertarPerfil(?, ?, ?, ?)";
              
              if($stmtCreateProfile = $mysqli->prepare($sqlCreateProfile)){
                  $param_nombrePerfil = $nombreUsuario;
                  $param_fotoPerfil = "img/default.png";
                  $param_descripcion = "Hola, soy nuevo!";
                  
                  // Bind variables to the prepared statement as parameters
                  $stmtCreateProfile->bind_param("isss", $idUsuario, $param_nombrePerfil, $param_fotoPerfil, $param_descripcion);
                  
                  
                  
                  // Attempt to execute the prepared statement
                  if($stmtCreateProfile->execute()){
                      // Redirect to login page
                      header("location: login.php");
                  } else{
                      echo "Ocurrió un error al crear el perfil, intente de nuevo más tarde.";
                  }

                  // Close statement
                  $stmtCreateProfile->close();
              } else {
                  echo "Error en la preparación de la consulta para crear el perfil.";
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
    <title>Crear cuenta</title>
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
                    
                    <h2 class="fw-bold mb-2 text-uppercase">Crear cuenta</h2>
                    <p class="text-white-50 mb-5">Por favor, introduzca su nombre de usuario, correo y contraseña</p>

                    <div class="form-group">
                      <input type="text" class="form-control <?php echo (!empty($nombreUsuario_err)) ? 'is-invalid' : ''; ?>" name="nombreUsuario" id="username" placeholder="Nombre de usuario" value="<?php echo $nombreUsuario; ?>" required>
                      <span class="invalid-feedback"><?php echo $nombreUsuario_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                      <input type="email" name="correo" id="email" class="form-control <?php echo (!empty($correo_err)) ? 'is-invalid' : ''; ?>" aria-describedby="emailHelp" placeholder="Correo" value="<?php echo $correo; ?>" required>
                      <span class="invalid-feedback"><?php echo $correo_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                      <input type="password" name="contraseña" id="contraseña" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Contraseña" value="<?php echo $password; ?>" required>
                      <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                      <input type="password" name="confirmarContraseña" id="confirmarContraseña" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" placeholder="Confirmar contraseña" value="<?php echo $confirm_password; ?>" required>
                      <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                    </div>

                    <button class="btn btn-outline-danger btn-lg px-5" type="reset">Limpiar</button>
                    <button class="btn btn-outline-light btn-lg px-5" type="submit">Registrarse</button>
                    
                    <div>
                      <p id="tienes" class="mb-0">¿Ya tienes una cuenta? <a href="login.php" class="text-white-50 fw-bold">Iniciar sesión</a></p>
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