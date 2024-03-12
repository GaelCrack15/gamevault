<?php
// Inicia la sesión (asegúrate de hacerlo antes de cualquier salida en la página)
session_start();

// Include config file
require_once "config.php";

// Obtener el ID del juego de la URL
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $idJuego = trim($_GET["id"]);
 
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
                $plataformas = $juego["plataformas"];
                $desarrollador = $juego["desarrollador"];
                $fechaLanzamiento = $juego["fechaLanzamiento"];
                $descripcion = $juego["descripcion"];
                $promedio = $juego["promedio"];
                $fotoJuego = $juego["fotoJuego"];
            } else {
                // Si no se encuentra el juego, redirigir al índice
                header("location: index.php");
                exit();
            }
        } else {
            // Si ocurre algún error en la consulta, mostrar mensaje de error y redirigir al índice
            echo "Error al obtener detalles del juego.";
            header("location: index.php");
            exit();
        }

        // Cerrar la sentencia preparada
        $stmt->close();
    } else {
        // Si ocurre algún error en la preparación de la consulta, mostrar mensaje de error y redirigir al índice
        echo "Error en la preparación de la consulta.";
        header("location: index.php");
        exit();
    }
} else {
    // Si no se proporciona un ID de juego válido en la URL, redirigir al índice
    header("location: index.php");
    exit();
}

// Calcular la calificación promedio y mostrarla
$sqlPromedio = "SELECT AVG(calificacion) AS promedio FROM Resenas WHERE idJuego = ?";
if ($stmtPromedio = $mysqli->prepare($sqlPromedio)) {
    $stmtPromedio->bind_param("i", $idJuego);
    if ($stmtPromedio->execute()) {
        $resultPromedio = $stmtPromedio->get_result();
        $rowPromedio = $resultPromedio->fetch_assoc();
        $promedio = $rowPromedio["promedio"];
    } else {
        echo "Error al obtener el promedio de calificaciones.";
    }
    $stmtPromedio->close();
} else {
    echo "Error en la preparación de la consulta para obtener el promedio de calificaciones.";
}

if (!empty($promedio)) {
    $sqlUpdatePromedio = "UPDATE Juegos SET promedio = ? WHERE idJuego = ?";
    if ($stmtUpdatePromedio = $mysqli->prepare($sqlUpdatePromedio)) {
        $stmtUpdatePromedio->bind_param("di", $promedio, $idJuego);
        if ($stmtUpdatePromedio->execute()) {
            // El promedio se ha actualizado exitosamente en la base de datos
        }
        $stmtUpdatePromedio->close();
    } else {
        echo "Error en la preparación de la consulta para actualizar el promedio de calificaciones.";
    }
}


// Inicializamos la variable $rutaFoto
$rutaFoto = "";

// Verificamos si el usuario está autenticado
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // Obtenemos el id del usuario autenticado (suponiendo que está almacenado en $_SESSION["id"])
    $idUsuario = $_SESSION["idUsuario"];

    // Preparamos la consulta para obtener la ruta de la foto de perfil
    $sql = "SELECT fotoPerfil FROM Perfiles WHERE idUsuario = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        // Vinculamos el id del usuario como parámetro
        $stmt->bind_param("i", $idUsuario);

        // Ejecutamos la consulta
        if ($stmt->execute()) {
            // Vinculamos el resultado de la consulta a una variable
            $stmt->bind_result($rutaFoto);

            // Obtenemos el resultado de la consulta
            $stmt->fetch();

            // Cerramos el statement
            $stmt->close();
        }
    }

            $sql = "SELECT adm FROM Usuarios WHERE idUsuario = ?";
            if ($stmt = $mysqli->prepare($sql)) {
                // Bind variables to the prepared statement as parameters
                $stmt->bind_param("i", $idUsuario);

                if ($stmt->execute()) {
                    // Vinculamos el resultado de la consulta a una variable
                    $stmt->bind_result($adm);

                    // Obtenemos el resultado de la consulta
                    $stmt->fetch();

                    // Cerramos el statement
                    $stmt->close();    
                }
            }
    // Preparamos la consulta para obtener la ruta de la foto de perfil
    $sql = "SELECT nombreUsuario FROM Usuarios WHERE idUsuario = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        // Vinculamos el id del usuario como parámetro
        $stmt->bind_param("i", $idUsuario);

        // Ejecutamos la consulta
        if ($stmt->execute()) {
            // Vinculamos el resultado de la consulta a una variable
            $stmt->bind_result($username);

            // Obtenemos el resultado de la consulta
            $stmt->fetch();

            // Cerramos el statement
            $stmt->close();
        }
    } 
} else{
    $idUsuario=0;
    $adm = 0;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>
    <link rel="icon" href="img/favicon.png" type="">
    <!-- Favicons -->
    <link href="css/startStyle/assets/img/favicon.png" rel="icon">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Roboto:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
    <!-- Vendor CSS Files -->
    <link href="css/startStyle/assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="css/startStyle/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/startStyle/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="css/startStyle/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="css/startStyle/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="css/startStyle/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    
    <!-- Template Main CSS File -->
    <link href="css/startStyle/assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="css/stylegame.css">
    
    <!-- =======================================================
    * Template Name: BizLand
    * Updated: Jul 27 2023 with Bootstrap v5.3.1
    * Template URL: https://bootstrapmade.com/bizland-bootstrap-business-template/
    * Author: BootstrapMade.com
    * License: https://bootstrapmade.com/license/
    ======================================================== -->
</head>
<body class="text-white" style="background: linear-gradient(to right, #6607ff, #121212 50%, #121212 50%, #6607ff);">
<header id="header" class="d-flex align-items-center" style="padding-bottom: 10px;">
      <div class="container d-flex align-items-center justify-content-between">  
        <a href="index.php"><img src="css/startStyle/assets/img/favicon.png" alt="" srcset="" height="100%" width="80px"></a>
        <h1 class="logo" style="margin-bottom: 15px"><a href="index.php">Game<span>Vault</span></a></h1>
        <nav id="navbar" class="navbar" style="width: fit-content">
          <ul>
            <li><a  class="nav-link scrollto" href="index.php">Inicio</a></li>
            <li><a  class="nav-link scrollto" href="index.php?order=az">De la A a la Z</a></li>
            <li><a  class="nav-link scrollto" href="index.php?order=rated">Mejor calificados</a></li>
            <li><a  class="nav-link scrollto" href="index.php?order=recent">Recién añadidos</a></li>
          </ul>
        </nav>
        <?php if ($idUsuario !== 0) {?>
                <img src="<?php echo $rutaFoto; ?>" alt="user" style="margin-left: -30px" class="user-pic" onclick="toggleMenu()">
            <?php } else { ?>
                <a href="#" onclick="cambiarIdioma();"><img class="lan-pic" src="css/startStyle/assets/img/switch.png" height="30px" width="30px"></a>
            <?php }?>
        <div class="sub-menu-wrap bg-dark" id="subMenu">
          <div class="sub-menu">
            <div class="user-info">
              <img src="<?php echo $rutaFoto; ?>" height="50" width="50">
              <h3><?php echo $username; ?></h3>
            </div>
          <hr>
          <a href="profile.php?id=<?php echo urlencode($_SESSION["idUsuario"]); ?>"class="sub-menu-link">
            <i class="bi bi-person-fill"></i>
            <p>Ver perfil</p>
          <span></span>
          </a>
          <a href="settings.php?id= <?php echo urlencode($_SESSION["idUsuario"]); ?>"class="sub-menu-link">
            <i class="bi bi-gear-fill"></i>
            <p>Configuración</p>
            <span></span>            
          </a>
          <a href="create.php"class="sub-menu-link">
            <i class="bi bi-plus-square-fill"></i>
            <p>Agregar juego</p>
            <span></span>
          </a>
          <a href="logout.php"class="sub-menu-link">
            <i class="bi bi-box-arrow-left"></i>
            <p>Cerrar sesión</p>
            <span></span>
          </a>
          </div>
        </div>
    </header>   

    <div class="container mt-4">
        <div class="mb-12"><h1 class="text-center mb-4" style="font-size: 40px; font-weight: bold; margin-top: 90px">Detalles del juego: <span style="color: #8810ea;"><?php echo $titulo; ?></span></h1></div>
        <div class="row">
            <!-- Columna izquierda con la imagen -->
            <div class="col-md-4">
                <div class="card mb-4" style="box-shadow: 0 0 50px rgba(0, 255, 255, 0.7); border-style: none">
                    <img src="<?php echo $fotoJuego; ?>" class="card-img-top" alt="<?php echo $titulo; ?>">
                    <?php
                   $sql = "SELECT idUsuario, idBibliotecaPersonal FROM BibliotecaPersonal WHERE idJuego = ?";
                   if ($stmt = $mysqli->prepare($sql)) {
                       // Bind variables to the prepared statement as parameters
                       $stmt->bind_param("i", $idJuego);
                   
                       if ($stmt->execute()) {
                           $stmt->bind_result($idUsuarioEnBiblioteca, $idBibliotecaPersonal);
                   
                           // Crear un arreglo para almacenar los IDs de usuarios en la biblioteca
                           $usuariosEnBiblioteca = array();
                   
                           while ($stmt->fetch()) {
                               $usuariosEnBiblioteca[$idUsuarioEnBiblioteca] = $idBibliotecaPersonal;
                           }
                   
                           // Verificar si el idUsuario está en el arreglo de usuarios en la biblioteca
                           if ($idUsuario !== 0) {
                               if (isset($usuariosEnBiblioteca[$idUsuario])) {
                                   $idBibliotecaEncontrada = $usuariosEnBiblioteca[$idUsuario];
                                   echo "<p style='text-align: center; margin-top: 10px; color: #4405a0' font-weight: bold>Ya tienes este juego en tu biblioteca</p>";
                                   echo '<form action="bibliodelete.php" method="post">';
                                    echo '<input type="hidden" name="idBibliotecaPersonal" value="' . $idBibliotecaEncontrada . '">';
                                    echo '<button style="width: 100%; background-color: #4405a0; border-color: #4405a0; border-radius: 0 0 5px 5px" type="submit" class="btn btn-danger" title="Eliminar Juego" data-toggle="tooltip"><i class="bi bi-trash-fill"></i> Eliminar de la biblioteca</button>';
                                    echo '</form>';
                               } else {
                                   echo "<form action='agregarbiblio.php' method='post' id='save-biblio-form'>";
                                   echo "<input type='hidden' name='idUsuario' value='" . $idUsuario . "'>";
                                   echo "<input type='hidden' name='idJuego' value='" . $idJuego . "'>";
                                   echo '<button style="width: 100%; background-color: #8810ae; border-color: #8810ae; border-radius: 0 0 5px 5px" class="btn btn-primary" type="submit" id="save-biblio-btn"><i class="bi bi-bookmarks-fill"></i> Guardar en la biblioteca</button>';
                                   echo "</form>";
                               }
                           } else {
                               echo "<p style='text-align: center;'>Inicia sesión para guardar este juego.</p>";
                           }
                       } else {
                           echo "<script>alert('Hubo un error, intente de nuevo más tarde.');</script>";
                       }
                   
                       // Close statement
                       $stmt->close();
                   }                   
                    ?>
                </div>
            </div>
            <!-- Columna derecha con la información -->
            <div class="col-md-8">
                <div class="card mb-4" style="box-shadow: 0 0 50px rgba(200, 0, 255, 0.7); border-style: none">
                <?php
                    if ($adm) {
                        echo "<a style='background-color: #8810ae; border-color: #8810ae; border-radius: 5px 5px 0 0; font-weight: bold' class='btn btn-success' href='editgame.php?id=" . $idJuego . "'>Editar juego</a>";
                        echo "<form action='deletegame.php' method='post'>";
                        echo "<input type='hidden' name='idJuego' value='" . $idJuego . "'>";
                        echo "<button type='submit' class='btn btn-danger' style='width: 100%; background-color: #4405a0; border-color: #4405a0; border-radius: 0; font-weight: bold'>Eliminar juego</button>";
                        echo "</form>";
                    }
                    
                ?>
                    <div class="card-body">
                        <p class="card-text"><strong style="color: #4405a0">Género:</strong> <?php echo $genero; ?></p>
                        <p class="card-text"><strong style="color: #4405a0">Plataformas:</strong> <?php echo $plataformas; ?></p>
                        <p class="card-text"><strong style="color: #4405a0">Desarrollador:</strong> <?php echo $desarrollador; ?></p>
                        <p class="card-text"><strong style="color: #4405a0">Fecha de lanzamiento:</strong> <?php echo $fechaLanzamiento; ?></p>
                        <p class="card-text"><strong style="color: #4405a0">Descripción:</strong> <?php echo $descripcion; ?></p>

                    </div>
                </div>
                <h2><strong>Promedio:</strong> <?php echo round($promedio, 2); ?><i style='color: yellow; margin-left: 5px;' class='bi bi-star-fill'></i></h2>
                <hr>

        <!-- Mostrar las reseñas existentes -->
        <?php
        // Consulta para obtener las reseñas del juego actual
        $sqlResenas = "SELECT r.*, u.nombreUsuario AS nombreUsuario FROM Resenas r
                       JOIN Usuarios u USING(idUsuario)
                       WHERE idJuego = ?";

        if ($stmtResenas = $mysqli->prepare($sqlResenas)) {
            // Bind variables a la sentencia preparada como parámetros
            $stmtResenas->bind_param("i", $idJuego);

            // Ejecutar la sentencia preparada
            if ($stmtResenas->execute()) {
                $resultResenas = $stmtResenas->get_result();

                $totalResenas = $resultResenas->num_rows;

                // Mostrar reseñas existentes
                if ($totalResenas > 0) {
                    echo "<h2 style='font-weight: 400'>Reseñas</h2>";
                    echo "<ul>";
                    while ($rowResena = $resultResenas->fetch_assoc()) {
                        echo "<li style='list-style: none'>";
                        echo "<i class='bi bi-chat-text-fill'></i>";
                        echo "<strong style='margin: 0 5px 0 5px'><a class='profile-link' href='profile.php?id=" . urlencode($rowResena["idUsuario"]) . "'>" . htmlspecialchars($rowResena["nombreUsuario"]) . "</a></strong>";
                        echo "<span class='rating'>"; // Puedes usar una clase CSS para mostrar estrellas según la calificación
                        for ($i = 1; $i <= $rowResena["calificacion"]; $i++) {
                           echo "<i style='color: yellow' class='bi bi-star-fill'></i>";
                        }
                        if ($rowResena["calificacion"] < 5){
                            while ($i <= 5) {
                                echo "<i class='bi bi-star-fill'></i>";
                                $i++;
                            }
                        }
                        echo "</span>";
                        echo "<p>" . htmlspecialchars($rowResena["comentario"]) . "</p>";
                        echo "<p>" . htmlspecialchars($rowResena["fechaPublicacion"]) . "</p>";
                        echo "</li>";
                    }
                    echo "</ul>";
                }
            } else {
                echo "Error al obtener las reseñas.";
            }

            // Cerrar la sentencia preparada
            $stmtResenas->close();
        }
        ?>

        <!-- Formulario para agregar nueva reseña -->
        <div class="add-review-section">
    <?php
    if (isset($_SESSION['nombreUsuario']) && !empty($_SESSION['nombreUsuario'])) {
        // Consultar si el usuario ya ha hecho una reseña para este juego
        $sqlUsuarioResena = "SELECT idResena, calificacion, comentario FROM Resenas WHERE idUsuario = ? AND idJuego = ?";
        if ($stmtUsuarioResena = $mysqli->prepare($sqlUsuarioResena)) {
            // Bind variables a la sentencia preparada como parámetros
            $stmtUsuarioResena->bind_param("ii", $_SESSION['idUsuario'], $idJuego);

            // Ejecutar la sentencia preparada
            if ($stmtUsuarioResena->execute()) {
                $resultUsuarioResena = $stmtUsuarioResena->get_result();
                if ($resultUsuarioResena->num_rows > 0) {
                    // Si el usuario ya ha hecho una reseña, mostramos el formulario para editar la reseña existente
                    $rowResena = $resultUsuarioResena->fetch_assoc();
                    echo "<h2>Editar Reseña</h2>";
                    echo "<form action='updateresena.php' method='post' id='edit-review-form' style='display: none;'>";
                    echo "<input type='hidden' name='idResena' value='" . $rowResena['idResena'] . "'>";
                    echo "<input type='hidden' name='idJuego' value='" . $idJuego . "'>";
                    echo "<span style='margin-right: 5px'>" . $username . "</span>";
                    echo "<input type='number' name='calificacion' value='".$rowResena['calificacion']."' min='1' max='5' placeholder='Calificación (1-5)' style='width: 140px; border-width: 0; box-shadow: 0 0 5px #fff; padding: 3px; border-radius: 5px; background-color: #fff;' required>";
                    echo "<br>";
                    echo "<textarea style='margin-top: 10px; width: 70%; border-width: 0; box-shadow: 0 0 5px #fff; border-radius: 5px; background-color: #fff; padding: 3px' name='comentario' placeholder='Escribe tu reseña... (opcional)'>". $rowResena['comentario'] . "</textarea>";
                    echo "<br>";
                    echo "<input style='height: 30px; border-width: 0; box-shadow: 0 0 5px #a2e; border-radius: 5px; background-color: #a3f; color: white; font-weight: bold; padding: 3px' type='submit' value='Enviar Reseña'>";
                    echo "</form>";
                    echo "<button style='height: 30px; margin-right: 20px; margin-bottom: 30px; border-width: 0; box-shadow: 0 0 5px #a2e; border-radius: 5px; background-color: #a3f; color: white; font-weight: bold; padding: 3px' id='edit-review-btn'>Editar reseña</button>";
                    // Formulario para eliminar reseña
                    echo "<form action='deleteresena.php' method='post' id='delete-review-form' style='display: none;'>";
                    echo "<input type='hidden' name='idResena' value='" . $rowResena['idResena'] . "'>";
                    echo "<input type='hidden' name='idJuego' value='" . $idJuego . "'>";
                    echo "</form>";
                    // Botón para borrar reseña
                    echo "<button style='height: 30px; border-width: 0; box-shadow: 0 0 5px #4405a0; border-radius: 5px; background-color: #4405a0; color: white; font-weight: bold; padding: 3px' id='delete-review-btn'>Borrar reseña</button>";
                } else {
                    // Si el usuario no ha hecho una reseña, mostramos el formulario para agregar una nueva reseña
                    echo "<h2>Agregar Reseña</h2>";
                    echo "<form action='guardarresena.php' method='post' style='margin-bottom: 30px'>";
                    echo "<input type='hidden' name='idJuego' value='" . $idJuego . "'>";
                    echo "<span style='margin-right: 10px; font-size: 20px'>" . $username . "</span>";
                    echo "<input type='number' name='calificacion' min='1' max='5' placeholder='Calificación (1-5)' style='width: 140px; border-width: 0; box-shadow: 0 0 5px #fff; padding: 3px; border-radius: 5px; background-color: #fff;' required>";
                    echo "<br>";
                    echo "<textarea style='margin-top: 10px; width: 70%; border-width: 0; box-shadow: 0 0 5px #fff; border-radius: 5px; background-color: #fff; padding: 3px' name='comentario' placeholder='Escribe tu reseña... (opcional)'></textarea>";
                    echo "<br>";
                    echo "<input style='height: 30px; border-width: 0; box-shadow: 0 0 5px #a2e; border-radius: 5px; background-color: #a3f; color: white; font-weight: bold; padding: 3px' type='submit' value='Enviar Reseña'>";
                    echo "</form>";
                }
            } else {
                echo "Error al verificar si el usuario ha realizado una reseña.";
            }

            // Cerrar la sentencia preparada
            $stmtUsuarioResena->close();
        } else {
            echo "Error en la preparación de la consulta.";
        }
    } else {
        echo "<p>Por favor, inicia sesión para agregar una reseña.</p>";
    }
    ?>
</div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8TkEdL3x8Ov2S/KBDeOCLV/Ftzru13TVCBskMQM1X7Y2Hp9U/E3" crossorigin="anonymous"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Obtener el botón y el formulario por su ID
        const editReviewBtn = document.getElementById("edit-review-btn");
        const editReviewForm = document.getElementById("edit-review-form");
        const deleteReviewBtn = document.getElementById("delete-review-btn");

        // Agregar evento de click al botón "Editar reseña"
        editReviewBtn.addEventListener("click", function() {
            // Mostrar el formulario de edición cuando se hace clic en el botón
            editReviewForm.style.display = "block";
            
            // Ocultar el botón de "Editar reseña"
            editReviewBtn.style.display = "none";

            // Ocultar el botón de "Eliminar reseña"
            deleteReviewBtn.style.display = "none";
        });

        // Agregar evento de click al botón "Borrar reseña"
        deleteReviewBtn.addEventListener("click", function() {
            // Mostrar mensaje de confirmación antes de enviar el formulario de eliminación
            const confirmDelete = confirm("¿Estás seguro que deseas eliminar esta reseña?");
            if (confirmDelete) {
                // Si el usuario confirma, enviar el formulario de eliminación
                document.getElementById("delete-review-form").submit();
            }
        });
    });
</script>
<script>
  // Función para cerrar el submenú
  function closeSubMenu() {
    let subMenu = document.getElementById("subMenu");
    subMenu.classList.remove("open-menu");
  }

  // Agrega un evento de clic en el documento
  document.addEventListener("click", function (event) {
    let subMenu = document.getElementById("subMenu");
    let userPic = document.querySelector(".user-pic");

    // Si el clic ocurrió fuera del submenú y fuera del botón de usuario, cierra el submenú
    if (!subMenu.contains(event.target) && !userPic.contains(event.target)) {
      closeSubMenu();
    }
  });

  // Agrega un evento de clic al botón de usuario para abrir/cerrar el submenú
  let userPic = document.querySelector(".user-pic");
  userPic.addEventListener("click", function () {
    let subMenu = document.getElementById("subMenu");
    subMenu.classList.toggle("open-menu");
  });
</script>
  <div id="preloader"></div>
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="css/startStyle/assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="css/startStyle/assets/vendor/aos/aos.js"></script>
  <script src="css/startStyle/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="css/startStyle/assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="css/startStyle/assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="css/startStyle/assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Template Main JS File -->
  <script src="css/startStyle/assets/js/main.js"></script>
  <script>
    var idJuego = <?php echo $idJuego; ?>;
    function cambiarIdioma() {
        window.location.href = "english/game.php?id=" + idJuego;
    }
  </script>
  
</body>
</html>
