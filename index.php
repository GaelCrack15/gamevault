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

// Obtener la página actual y la cantidad de juegos por página
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$per_page = 12;

// Calcular el desplazamiento para la consulta SQL
$offset = ($page - 1) * $per_page;

// Verificar la opción de orden seleccionada (por defecto, "Más nuevos")
$orderBy = "fechaLanzamiento DESC";
if (isset($_GET['order'])) {
    if ($_GET['order'] === 'az') {
        $orderBy = "titulo ASC"; // Cambiar al orden alfabético
    } elseif ($_GET['order'] === 'rated') {
        $orderBy = "promedio DESC"; // Cambiar al orden por mayor promedio
    } elseif ($_GET['order'] === 'recent') {
        $orderBy = "idJuego DESC"; // Cambiar al orden descendente por fecha de lanzamiento
    }
}

// Consulta SQL para obtener juegos limitados por página
$sqlOrderBy = "SELECT idJuego, titulo, fotoJuego, promedio FROM Juegos ORDER BY $orderBy LIMIT ?, ?";
$stmtOrderBy = $mysqli->prepare($sqlOrderBy);
$stmtOrderBy->bind_param("ii", $offset, $per_page);
$stmtOrderBy->execute();
$resultadoOrderBy = $stmtOrderBy->get_result();

// Calcular el número total de juegos
$sql_count = "SELECT COUNT(*) FROM Juegos";
$count_result = $mysqli->query($sql_count);
$total_games = $count_result->fetch_row()[0];

// Calcular el número total de páginas
$total_pages = ceil($total_games / $per_page);

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
}

// Conexión a la base de datos y otras configuraciones

// Obtener el término de búsqueda si se envió
$terminoBusqueda = $_GET["busqueda"] ?? "";

// Consulta SQL con la búsqueda
$sqlBusqueda = "SELECT * FROM Juegos";

// Agregar la condición de búsqueda si se proporcionó un término
if (!empty($terminoBusqueda)) {
  $sqlBusqueda .= " WHERE titulo LIKE '%$terminoBusqueda%'";
} else {
  $terminoBusqueda = "";
}

// Ejecutar la consulta y obtener el resultado
$resultadoBusqueda = $mysqli->query($sqlBusqueda);


// Cerramos la conexión a la base de datos
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GameVault</title>
  
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
  <link rel="stylesheet" href="css/styleindex.css">
  
  <!-- =======================================================
  * Template Name: BizLand
  * Updated: Jul 27 2023 with Bootstrap v5.3.1
  * Template URL: https://bootstrapmade.com/bizland-bootstrap-business-template/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>
<body class="bg-dark">
    <header id="header" class="d-flex align-items-center" style="padding-bottom: 10px;">
      <div class="container d-flex align-items-center justify-content-between">  
        <a href="index.php"><img src="css/startStyle/assets/img/favicon.png" alt="" srcset="" height="80%" width="80px"></a>
        <h1 class="logo" style="margin-right: 30px;"><a href="index.php">Game<span>Vault</span></a></h1>
        <nav id="navbar" class="navbar">
          <ul>
            <li><a class="nav-link scrollto" href="index.php">Inicio</a></li>
            <li><a class="nav-link scrollto" href="index.php?order=az">De la A a la Z</a></li>
            <li><a class="nav-link scrollto" href="index.php?order=rated">Mejor calificados</a></li>
            <li><a class="nav-link scrollto" href="index.php?order=recent">Recién añadidos</a></li>
          </ul>
          <i class="bi bi-list mobile-nav-toggle"></i>
        </nav>
        <img src="<?php echo $rutaFoto; ?>" alt="usuario" class="user-pic" onclick="toggleMenu()">
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

  <div class="container mt-2 text-white text-center">
        <?php if (isset($_GET['order']) && $_GET['order'] == "az") {
          echo "<h1 class='tituloPrincipal'>Juegos de la A a la Z en GameVault</h1>";
        } elseif (isset($_GET['order']) && $_GET['order'] == "rated") {
          echo "<h1 class='tituloPrincipal'>Juegos mejor calificados en GameVault</h1>";
        } elseif (isset($_GET['order']) && $_GET['order'] == "recent") {
          echo "<h1 class='tituloPrincipal'>Juegos recien añadidos en GameVault</h1>";
        } elseif (isset($_GET['busqueda'])) {
          echo "<h1 class='tituloPrincipal'>Buscando en GameVault</h1>";
        } else {
          echo "<h1 class='tituloPrincipal'>Juegos en GameVault</h1>";
        }
        ?>
        <form method="get" action="" class="mb-4" class="search-bar">
          <input type="text" name="busqueda" placeholder="Buscar juego..." class="search-input">
          <button type="submit" class="search-button">Buscar</button>
          <a class="reset-button" href="index.php" style="color: white;">Restablecer</a>
          <!-- <a href="?busqueda=" class="reset-button">Restablecer</a> -->
        </form>
        <div class="row">
            <?php
            if (isset($_GET['order']) || (!isset($_GET['order']) && !isset($_GET['busqueda']))) {
              // Mostrar resultados de ordenar juegos
              $resultado = $resultadoOrderBy;
            } elseif (isset($resultadoBusqueda)) {
              // Mostrar resultados de consulta de juegos
              $resultado = $resultadoBusqueda;
            }
            if (isset($_GET['busqueda']) && empty($terminoBusqueda)) {
              echo "<p class='mensajeError'>Esperando su búsqueda...</p>";
            }elseif ($resultado->num_rows === 0) {
              // Aquí puedes mostrar un mensaje indicando que no se encontraron resultados
              echo "<p class='mensajeError'>No se encontró el juego que buscaba.</p>";
              echo "<p class='mensajeError'>Si desea añadirlo de click <a href='create.php'>aquí</a>.</p>";
            } else {
              // Mostrar la lista de juegos
              while ($row = $resultado->fetch_assoc()) {
                $idJuego = $row["idJuego"];
                $titulo = $row["titulo"];
                $fotoJuego = $row["fotoJuego"];
                ?>
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <a href="game.php?id=<?php echo $idJuego; ?>" class="sin-subrayado">
                        <img src="<?php echo $row["fotoJuego"]; ?>" alt="<?php echo $row["titulo"]?>" class="card-img-top">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $titulo; ?></h5>
                                <h4 class="fluid-h4"><?php echo round($row["promedio"], 2)?><i style='color: yellow; margin-left: 5px;' class='bi bi-star-fill'></i></h4>
                            </div>
                        </a>
                    </div>
                </div>
                <?php
              }
            }
            ?>
            <div class="pagination mt-4">
            <?php
            // Mostrar botones de paginación
            if (!isset($_GET['busqueda'])) {
              for ($i = 1; $i <= $total_pages; $i++) {
                $active = ($i == $page) ? 'active' : '';
                echo '<a class="btn btn-secondary ' . $active . '" href="index.php?page=' . $i . '&order=' . ($_GET['order'] ?? 'newest') . '">' . $i . '</a>';
            }
            }
            ?>
        </div>
        </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
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
</body>
</html>
