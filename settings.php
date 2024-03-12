<?php
// Include config file
require_once "config.php";

// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to start page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: start.html");
    exit;
}

// Verificar si el usuario autenticado es el propietario del perfil
if ((int)$_SESSION["idUsuario"] !== (int)$_GET["id"]) {
    // Si el usuario autenticado no es el propietario del perfil, redirige o muestra un mensaje de error.
    header("location: index.php");
    exit();
}

// Obtener el ID de perfil de la URL
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $idPerfil = trim($_GET["id"]);
	$idUsuario = $_SESSION["idUsuario"];

    // Consultar la información del perfil según su ID
    $sql = "CALL ConsultarPerfil(?)";
    if ($stmt = $mysqli->prepare($sql)) {
        // Vincular el ID de perfil como parámetro
        $stmt->bind_param("i", $idPerfil);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();

            if ($resultado->num_rows == 1) {
                // Obtener los datos del perfil
                $perfil = $resultado->fetch_assoc();
                $nombrePerfil = $perfil["nombrePerfil"];
                $fotoPerfil = $perfil["fotoPerfil"];
                $descripcion = $perfil["descripcion"];
            } else {
                // Si no se encuentra el perfil, redirigir al índice
                header("location: index.php");
                exit();
            }
        } else {
            echo "Error al obtener detalles del perfil.";
        }

        // Cerrar la consulta
        $stmt->close();
    } else {
        echo "Error en la preparación de la consulta.";
    }

    // Consultar la información del perfil según su ID
    $sql = "SELECT * FROM Usuarios WHERE idUsuario = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        // Vincular el ID de perfil como parámetro
        $stmt->bind_param("i", $idPerfil);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            $resultado = $stmt->get_result();

            if ($resultado->num_rows == 1) {
                // Obtener los datos del perfil
                $usuario = $resultado->fetch_assoc();
                $nombreUsuario = $usuario["nombreUsuario"];
                $correo = $usuario["correo"];
				$adm = $usuario["adm"];

            } else {
                // Si no se encuentra el perfil, redirigir al índice u otra página de manejo de errores
                header("location: index.php");
                exit();
            }
        } else {
            echo "Error al obtener detalles del usuario.";
        }

        // Cerrar la consulta
        $stmt->close();
    } else {
        echo "Error en la preparación de la consulta.";
    }

    $nombreUsuario_err = $nombrePerfil_err = $correo_err = $fotoPerfil_err = $descripcion_err = $updatesuccess = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["form1"])) {
        $nombrePerfiln = $_POST["nombrePerfil"];
        $nombreUsuarion = $_POST["nombreUsuario"];
        $correon = $_POST["correo"];
        $descripcionn = $_POST["descripcion"];

        // Validate username
        if ($nombreUsuarion !== $nombreUsuario) {
            if (empty(trim($_POST["nombreUsuario"]))) {
                $nombreUsuario_err = "Por favor, ingrese un nombre de usuario.";
            } elseif (!preg_match('/^[a-zA-Z0-9._]+$/', trim($_POST["nombreUsuario"]))) {
                $nombreUsuario_err = "El nombre de usuario solo puede tener letras, números, puntos y guion bajo.";
            } elseif (strlen(trim($_POST["nombreUsuario"])) > 18) {
                $nombreUsuario_err = "El nombre de usuario no puede tener más de 18 carácteres.";
            } else {
                // Prepare a select statement
                $sql = "SELECT idUsuario FROM usuarios WHERE nombreUsuario = ? AND idUsuario != ?";
                
                if ($stmt = $mysqli->prepare($sql)) {
                    // Bind variables to the prepared statement as parameters
                    $stmt->bind_param("si", $param_nombreUsuario, $idUsuario);
                    
                    // Set parameters
                    $param_nombreUsuario = trim($_POST["nombreUsuario"]);
                    
                    // Attempt to execute the prepared statement
                    if ($stmt->execute()) {
                        // store result
                        $stmt->store_result();
                        
                        if ($stmt->num_rows == 1) {
                            $nombreUsuario_err = "Este nombre de usuario ya está tomado.";
                        } else {
                            $nombreUsuario = trim($_POST["nombreUsuario"]);
                        }
                    } else {
                        echo "Ocurrió un error, intente de nuevo más tarde.";
                    }

                    // Close statement
                    $stmt->close();
                }
            }
        }
        
        // Validate perfil
        if ($nombrePerfiln !== $nombrePerfil) {
            if (empty(trim($_POST["nombrePerfil"]))) {
                $nombrePerfil_err = "Por favor, ingrese un nombre de perfil.";     
            } else {
                $nombrePerfil = trim($_POST["nombrePerfil"]);
            }
        }
        
        // Validate email
        if ($correon !== $correo) {
            if (empty(trim($_POST["correo"]))) {
                $correo_err = "Por favor, ingrese un correo electrónico.";
            } else {
                // Prepare a select statement
                $sql = "SELECT nombreUsuario FROM usuarios WHERE correo = ? AND idUsuario != ?";
                
                if ($stmt = $mysqli->prepare($sql)) {
                    // Bind variables to the prepared statement as parameters
                    $stmt->bind_param("si", $param_correo, $idUsuario);
                    
                    // Set parameters
                    $param_correo = trim($_POST["correo"]);
                    
                    // Attempt to execute the prepared statement
                    if ($stmt->execute()) {
                        // store result
                        $stmt->store_result();
                        
                        if ($stmt->num_rows == 1) {
                            $correo_err = "Este correo ya está en uso.";
                        } else {
                            $correo = trim($_POST["correo"]);
                        }
                    } else {
                        echo "Ocurrió un error, intente de nuevo más tarde.";
                    }
          
                    // Close statement
                    $stmt->close();
                }
            }
        }
        
        // Validate bio
        if ($descripcionn !== $descripcion) {
            if (empty(trim($_POST["descripcion"]))) {
                $descripcion_err = "Por favor, ingrese una biografía.";     
            } else {
                $descripcion = trim($_POST["descripcion"]);
            }
        }

        // Llamar al procedimiento almacenado para actualizar el perfil en la base de datos
        if (empty($nombreUsuario_err) && empty($nombrePerfil_err) && empty($correo_err) && empty($descripcion_err)) {
            // Actualizar Perfil
            $sqlCallActualizarPerfil = "CALL ActualizarPerfil(?, ?, ?)";
            if ($stmtActualizarPerfil = $mysqli->prepare($sqlCallActualizarPerfil)) {
                $stmtActualizarPerfil->bind_param("iss", $idUsuario, $nombrePerfil, $descripcion);
                if ($stmtActualizarPerfil->execute()) {
                    $stmtActualizarPerfil->close();
                } else {
                    echo "Error al actualizar el perfil.";
                }
            } else {
                echo "Error en la preparación de la consulta.";
            }

            // Actualizar Usuario
            $sqlCallActualizarUsuario = "CALL ActualizarUsuario(?, ?, ?)";
            if ($stmtActualizarUsuario = $mysqli->prepare($sqlCallActualizarUsuario)) {
                $stmtActualizarUsuario->bind_param("iss", $idUsuario, $correo, $nombreUsuario);
                if ($stmtActualizarUsuario->execute()) {
                    $stmtActualizarUsuario->close();
                    // Redirigir al detalle del perfil después de actualizar
					$updatesuccess="Se ha actualizado la información correctamente";
                    header("location: settings.php?id=" . $_SESSION["idUsuario"]);
                    exit();
                } else {
                    echo "Error al actualizar la cuenta.";
                }
            } else {
                echo "Error en la preparación de la consulta.";
            }
        }
    }

	$passerror = $passerroractual = $passsuccess = '';

	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["form2"])) {
		if (isset($_POST['op']) && isset($_POST['np']) && isset($_POST['c_np'])) {
			function validate($data) {
				$data = trim($data);
				$data = stripslashes($data);
				$data = htmlspecialchars($data);
				return $data;
			}
	
			$op = validate($_POST['op']);
			$np = validate($_POST['np']);
			$c_np = validate($_POST['c_np']);
	
			if (empty($op) || empty($np) || empty($c_np) || $np !== $c_np || strlen($np) < 8) {
				$passerror = "Error en los datos proporcionados.";
			} else {
				$sql = "SELECT contrasenaHash FROM Usuarios WHERE idUsuario = ?";
				if ($stmt = $mysqli->prepare($sql)) {
					$stmt->bind_param("i", $idUsuario);
					$stmt->execute();
					$result = $stmt->get_result();
					$row = $result->fetch_assoc();
	
					if (password_verify($op, $row['contrasenaHash'])) {
						$stmt->close();
	
						$npHash = password_hash($np, PASSWORD_DEFAULT);
						$sql_2 = "UPDATE Usuarios SET contrasenaHash = ? WHERE idUsuario = ?";
						if ($stmt2 = $mysqli->prepare($sql_2)) {
							$stmt2->bind_param("si", $npHash, $idUsuario);
	
							if ($stmt2->execute()) {
								$passsuccess = "Contraseña cambiada con éxito.";
							} else {
								$passerror = "Hubo un error al cambiar la contraseña. Intente de nuevo más tarde.";
							}
							$stmt2->close();
						} else {
							$passerror = "Hubo un error al cambiar la contraseña. Intente de nuevo más tarde.";
						}
					} else {
						$passerroractual = "Contraseña actual incorrecta.";
					}
				} else {
					$passerror = "Hubo un error al cambiar la contraseña. Intente de nuevo más tarde.";
				}
			}
		} else {
			$passerror = "Hubo un error. Vuelva a la página de configuración.";
		}
	}
	


	$adminerror = "";

	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["form3"])) {
		$contraseñadmin = "i@#&ok@S6XJS9ME5u&Gm";
		$adminmode = $_POST['adminmode'];
		
		if ($contraseñadmin !== $adminmode){
			$adminerror = 'La contraseña no es correcta, modo solo para administradores.';
		} else{
			$sql = "UPDATE Usuarios SET adm = TRUE WHERE idUsuario = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("i", $idUsuario);

                if ($stmt->execute()) {
                    // Records created successfully. Redirect to landing page
                    header("location: settings.php?id=".$idUsuario);
                    exit();
                } else {
					echo "<script>alert('Hubo un error, intente de nuevo más tarde.');</script>";
                }

                // Close statement
                $stmt->close();
            }
		}
	}

	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["form35"])) {

			$sql = "UPDATE Usuarios SET adm = FALSE WHERE idUsuario = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("i", $idUsuario);

                if ($stmt->execute()) {
                    // Records created successfully. Redirect to landing page
                    header("location: settings.php?id=".$idUsuario);
                    exit();
                } else {
					echo "<script>alert('Hubo un error, intente de nuevo más tarde.');</script>";
                }

                // Close statement
                $stmt->close();
            }
	}

	$error = $error2 = "";

	if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["form4"])) {
        // Verificar si se proporcionó la cadena correcta para eliminar la cuenta
        $eliminarTexto = "Eliminar cuenta";
        $inputEliminar = $_POST["eliminar"];

        if ($inputEliminar !== $eliminarTexto) {
            $error = 'La cadena para eliminar la cuenta no coincide.';
        } else {
            // Verificar si ambas casillas de verificación están marcadas
            if (isset($_POST["seguro"]) && isset($_POST["seguro2"])) {
                // Procesar la eliminación de la cuenta aquí
                $sql3 = "DELETE FROM Resenas WHERE idUsuario = ?";
                if ($stmt3 = $mysqli->prepare($sql3)) {
                    // Bind variables to the prepared statement as parameters
                    $stmt3->bind_param("i", $idUsuario);

                    if ($stmt3->execute()) {
                        // Close statement
                        $stmt3->close();

                        $sql4 = "DELETE FROM BibliotecaPersonal WHERE idUsuario = ?";
                        if ($stmt4 = $mysqli->prepare($sql4)) {
                            // Bind variables to the prepared statement as parameters
                            $stmt4->bind_param("i", $idUsuario);
            
                            if ($stmt4->execute()) {
                                // Close statement
                                $stmt4->close();
                        
                                $sql = "CALL EliminarPerfil(?)";
                                if ($stmt = $mysqli->prepare($sql)) {
                                    // Bind variables to the prepared statement as parameters
                                    $stmt->bind_param("i", $idUsuario);
                    
                                    if ($stmt->execute()) {
                                        // Close statement
                                        $stmt->close();

                                        if ($fotoPerfil !== "img/default.png") {
                                            unlink($fotoPerfil);
                                        }
                                        $sql2 = "CALL EliminarUsuario(?)";
                                        if ($stmt2 = $mysqli->prepare($sql2)) {
                                            // Bind variables to the prepared statement as parameters
                                            $stmt2->bind_param("i", $idUsuario);

                                            if ($stmt2->execute()) {
                                                // Records created successfully. Redirect to landing page
                                                header("location: logout.php");
                                                exit();
                                            } else {
                                                echo "<script>alert('Hubo un error, intente de nuevo más tarde.');</script>";
                                            }

                                            // Close statement
                                            $stmt2->close();
                                        }
                                    } else {
                                        echo "<script>alert('Hubo un error, intente de nuevo más tarde.');</script>";
                                    }
                                }
                            } else {
                                echo "<script>alert('Hubo un error, intente de nuevo más tarde.');</script>";
                            }
                        }
                        
                    } else {
                        echo "<script>alert('Hubo un error, intente de nuevo más tarde.');</script>";
                    }
                    
                }
            } else {
                $error2= 'Debes marcar ambas casillas de verificación para eliminar la cuenta.';
            }
        }
    }

} else {
    // Si no se proporciona un ID de perfil válido en la URL, redirigir al índice u otra página de manejo de errores
    header("location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Configuración</title>
	<link rel="icon" href="img/favicon.png" type="">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="css/stylesettings.css">
</head>
<body>
	<section>
		<div class="container">
			<div class="bg-white shadow rounded-lg d-block d-sm-flex">
				<div class="profile-tab-nav border-right">
					<div class="p-4">
						<div class="img-circle text-center mb-3 upload">
                        <img src="<?php echo $fotoPerfil ?>" alt="Image" class="shadow">
                        <div class="round">
                            <form id="form" method="post" enctype="multipart/form-data"> <!-- Agregamos el formulario y el atributo 'enctype' -->
                                <input type="hidden" name="id" value="<?php echo $idPerfil; ?>">
                                <input type="hidden" name="name" value="<?php echo $nombreUsuario; ?>">
                                <input type="file" name="image" id="image" accept=".jpg, .jpeg, .png">
                                <i class="fa fa-camera" style="color: #fff;"></i>
                            </form>
                            <script type="text/javascript">
                                document.getElementById("image").onchange = function() {
                                    document.getElementById("form").submit();
                                };
                            </script>
                            <?php
                            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"]["name"])) { // Verificamos el método de solicitud
                                $id = $_POST["id"];
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
                                            alert('Formato de la foto incorrecto');
                                        </script>";
                                    header("location: settings.php?id=" . $idUsuario);
                                    exit();
                                } else {
                                    if ($imageSize > 5242880) {
                                        echo "<script>
                                                alert('La foto es demasiado grande');
                                            </script>";
                                        header("location: settings.php?id=" . $idUsuario);
                                        exit();
                                    } else {
                                        $fileNameNew = uniqid('', true).".".$fileActualExt;
                                        $fileDestination = 'uploads/'.$fileNameNew;
                                        // Update database
                                        $query = "UPDATE Perfiles SET fotoPerfil = '$fileDestination' WHERE idPerfil = $id";
                                        $result = mysqli_query($mysqli, $query);

                                        if ($result) { // Check if the query was successful
                                            if ($fotoPerfil !== "img/default.png") {
                                                unlink($fotoPerfil);
                                            }
                                            move_uploaded_file($tmpName, $fileDestination);
                                            header("location: settings.php?id=" . $idUsuario);
                                        exit();
                                        } else {
                                            echo "<script>
                                                    alert('Error updating database');
                                                </script>";
                                                header("location: settings.php?id=" . $idUsuario);
                                                exit();
                                        }
                                    }
                                }
                            }
                            ?>
                        </div>
						</div>
						<h4 class="text-center"><?php echo $nombreUsuario?></h4>
					</div>
					<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
						<a class="nav-link active" id="account-tab" data-toggle="pill" href="#account" role="tab" aria-controls="account" aria-selected="true">
							<i class="fa fa-home text-center mr-1"></i> 
							Cuenta
						</a>
						<a class="nav-link" id="password-tab" data-toggle="pill" href="#password" role="tab" aria-controls="password" aria-selected="false">
							<i class="fa fa-key text-center mr-1"></i> 
							Contraseña
						</a>
						<a class="nav-link" id="security-tab" data-toggle="pill" href="#security" role="tab" aria-controls="security" aria-selected="false">
							<i class="fa fa-user text-center mr-1"></i> 
							Admin
						</a>
						<a class="nav-link" id="notification-tab" data-toggle="pill" href="#notification" role="tab" aria-controls="notification" aria-selected="false">
							<i class="fa fa-bell text-center mr-1"></i> 
							Eliminar cuenta
						</a>
						<a id="volverPerfil" class="nav-link" href="profile.php?id=<?php echo $idUsuario?>">
							<i class="fa fa-arrow-left text-center mr-1"></i> 
							Volver al perfil
						</a>
					</div>
				</div>
				<div class="tab-content p-4 p-md-5" id="v-pills-tabContent">
					<div class="tab-pane fade show active" id="account" role="tabpanel" aria-labelledby="account-tab">
						<h3 class="mb-4">Configuración de la cuenta</h3>
                        <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method='post'>
						<input type="hidden" name="form1" value="1">
                        <div class="row">
							<div class="col-md-6">
								<div class="form-group">
								  	<label>Nombre del perfil</label>
								  	<input type="text" name="nombrePerfil" class="form-control <?php echo(!empty($nombrePerfil_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $nombrePerfil?>">
									<span class="invalid-feedback"><?php echo $nombrePerfil_err;?></span>
								</div>
							</div>
                            <div class="col-md-6">
								<div class="form-group">
								  	<label>Nombre de cuenta</label>
								  	<input type="text" name="nombreUsuario" class="form-control <?php echo(!empty($nombreUsuario_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $nombreUsuario?>">
									  <span class="invalid-feedback"><?php echo $nombreUsuario_err;?></span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
								  	<label>Correo</label>
								  	<input type="email" name="correo" class="form-control <?php echo(!empty($correo_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $correo?>">
									  <span class="invalid-feedback"><?php echo $correo_err;?></span>
								</div>
							</div>
							<div class="col-md-6">
								<div class="form-group">
								  	<label>Idioma</label>
								  	<select class="form-control" id="idioma" onchange="cambiarIdioma()">
										<option value="español">Español</option>
										<option value="english">English</option>
									</select>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
								  	<label>Biografía</label>
                                    <input type="text" class="form-control <?php echo(!empty($descripcion_err)) ? 'is-invalid' : ''; ?>" name="descripcion" value="<?php echo $descripcion?>">
									<span class="invalid-feedback"><?php echo $descripcion_err;?></span>
									<span class="valid-feedback"><?php echo $updatesuccess;?></span>
								</div>
							</div>
						</div>
						<div>
							<button class="btn btn-primary">Aceptar</button>
							<button class="btn btn-dark" type="reset">Limpiar</button>
						</div>
                        </form>
					</div>
					<div class="tab-pane fade" id="password" role="tabpanel" aria-labelledby="password-tab">
						<h3 class="mb-4">Configuración de contraseña</h3>
						<form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
						<input type="hidden" name="form2" value="1">
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label>Contraseña actual</label>
										<input name="op" type="password" class="form-control">
										<span class="invalid-feedback"><?php echo $passerroractual?></span>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6">
									<div class="form-group">
										<label>Nueva contraseña</label>
										<input name="np" type="password" class="form-control">
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label>Confirmar nueva contraseña</label>
										<input name="c_np" type="password" class="form-control">
										<span class="invalid-feedback"><?php echo $passerror?></span>
										<span class="valid-feedback"><?php echo $passsuccess?></span>
									</div>
								</div>
							</div>
							<div>
								<button class="btn btn-primary">Aceptar</button>
								<button class="btn btn-dark" type="reset">Limpiar</button>
							</div>
						</form>
					</div>
					<div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
						<h3 class="mb-4">Entrar en modo administrador</h3>
						<?php
						if (!$adm) {
							// Mostrar el formulario
							echo '
							<form action="' . htmlspecialchars(basename($_SERVER['REQUEST_URI'])) . '" method="post">
								<input type="hidden" name="form3" value="1">
								<div class="row">
									<div class="col-md-6">
										<div class="form-group">
											<label>Contraseña</label>
											<input type="password" name="adminmode" class="form-control">
											<span class="invalid-feedback"><?php echo $adminerror ?></span>
										</div>
									</div>
								</div>
								<div>
									<button class="btn btn-primary">Aceptar</button>
									<button class="btn btn-dark" type="reset">Limpiar</button>
								</div>
							</form>';
						} else {
							// Mostrar el botón para cambiar adm a false
							echo '
							<form action="' . htmlspecialchars(basename($_SERVER['REQUEST_URI'])) . '" method="post">
								<input type="hidden" name="form35" value="1">
								<button class="btn btn-danger">Salir del modo administrador</button>
							</form>';
						}
						?>
					</div>
					<div class="tab-pane fade" id="notification" role="tabpanel" aria-labelledby="notification-tab">
						<h3 class="mb-4">Eliminar cuenta</h3>
						<form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
						<input type="hidden" name="form4" value="1">
							<label>Escribe: "Eliminar cuenta" para borrar por completo tu cuenta.</label>
							<div class="form-group">
								<input name="eliminar" class="form-control" type="text" placeholder="Eliminar cuenta">
								<span class="invalid-feedback"><?php echo $error;?></span>
							</div>
							<div class="form-group">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" name="seguro" >
									<label class="form-check-label">
										Acepto que se eliminará está cuenta.
									</label>
									<span class="invalid-feedback"><?php echo $error2;?></span>
								</div>
							</div>
							<div class="form-group">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" name="seguro2" >
									<label class="form-check-label">
										Sé que al borrarse perderé todos mis datos y no se podrán recuperar.
									</label>
									<span class="invalid-feedback"><?php echo $error2;?></span>
								</div>
							</div>
							<div>
								<button class="btn btn-primary">Aceptar</button>
								<button class="btn btn-dark" type="reset">Limpiar</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>


	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
	<script>
        function cambiarIdioma() {
            var seleccionado = document.getElementById("idioma").value;

            <?php
            echo "var idUsuario = " . json_encode($idUsuario) . ";";
            ?>

            if (seleccionado === "english") {
                window.location.href = "english/settings.php?id=" + idUsuario;
            }
        }
    </script>
</body>
</html>