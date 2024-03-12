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
        $nombreUsuario_err = "Please enter a username.";
    } elseif(!preg_match('/^[a-zA-Z0-9._]+$/', trim($_POST["nombreUsuario"]))){
        $nombreUsuario_err = "The username can only contain letters, numbers, periods, and underscores.";
    } elseif (strlen(trim($_POST["nombreUsuario"])) > 18) {
      $nombreUsuario_err = "The username cannot be longer than 18 characters.";
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
                    $nombreUsuario_err = "This username is already taken.";
                } else{
                    $nombreUsuario = trim($_POST["nombreUsuario"]);
                }
            } else{
                echo "An error has occurred, try again later.";
            }

            // Close statement
            $stmt->close();
        }
    }

    //Validate email
    if(empty(trim($_POST["correo"]))){
      $correo_err = "Please enter an email.";
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
                  $correo_err = "This email is already in use.";
              } else{
                  $correo = trim($_POST["correo"]);
              }
          } else{
              echo "An error has occurred, try again later.";
          }

          // Close statement
          $stmt->close();
      }
  }
    
    // Validate password
    if(empty(trim($_POST["contraseña"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["contraseña"])) < 8){
        $password_err = "Password must have at least 8 characters.";
    } else{
        $password = trim($_POST["contraseña"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirmarContraseña"]))){
        $confirm_password_err = "Please confirm the password.";     
    } else{
        $confirm_password = trim($_POST["confirmarContraseña"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Passwords do not match.";
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

              // Call the stored procedure to create a profile
              $sqlCreateProfile = "CALL InsertarPerfil(?, ?, ?, ?)";
              
              if($stmtCreateProfile = $mysqli->prepare($sqlCreateProfile)){
                  $param_nombrePerfil = $nombreUsuario;
                  $param_fotoPerfil = "img/default.png";
                  $param_descripcion = "Hello, I'm new!";
                  
                  // Bind variables to the prepared statement as parameters
                  $stmtCreateProfile->bind_param("isss", $idUsuario, $param_nombrePerfil, $param_fotoPerfil, $param_descripcion);
                  
                  
                  
                  // Attempt to execute the prepared statement
                  if($stmtCreateProfile->execute()){
                      // Redirect to login page
                      header("location: login.php");
                  } else{
                      echo "An error occurred while creating the profile, try again later.";
                  }

                  // Close statement
                  $stmtCreateProfile->close();
              } else {
                  echo "Error preparing the query to create the profile.";
              }
            } else{
                echo "An error has occurred, try again later.";
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
    <title>Create Account</title>
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
                    
                    <h2 class="fw-bold mb-2 text-uppercase">Create account</h2>
                    <p class="text-white-50 mb-5">Please enter your username, email and password</p>

                    <div class="form-group">
                      <input type="text" class="form-control <?php echo (!empty($nombreUsuario_err)) ? 'is-invalid' : ''; ?>" name="nombreUsuario" id="username" placeholder="Username" value="<?php echo $nombreUsuario; ?>" required>
                      <span class="invalid-feedback"><?php echo $nombreUsuario_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                      <input type="email" name="correo" id="email" class="form-control <?php echo (!empty($correo_err)) ? 'is-invalid' : ''; ?>" aria-describedby="emailHelp" placeholder="Email" value="<?php echo $correo; ?>" required>
                      <span class="invalid-feedback"><?php echo $correo_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                      <input type="password" name="contraseña" id="contraseña" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Password" value="<?php echo $password; ?>" required>
                      <span class="invalid-feedback"><?php echo $password_err; ?></span>
                    </div>
                    
                    <div class="form-group">
                      <input type="password" name="confirmarContraseña" id="confirmarContraseña" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" placeholder="Confirm password" value="<?php echo $confirm_password; ?>" required>
                      <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                    </div>

                    <button class="btn btn-outline-danger btn-lg px-5" type="reset">Clear</button>
                    <button class="btn btn-outline-light btn-lg px-5" type="submit">Sign up</button>
                    
                    <div>
                      <p id="tienes" class="mb-0">Already have an account? <a href="login.php" class="text-white-50 fw-bold">Log in</a></p>
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