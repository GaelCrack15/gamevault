<?php
session_start();
require_once "config.php";

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $idPerfil = trim($_GET["id"]);

    // Consultar la información del perfil según su ID
    $sql = "CALL ConsultarPerfil(?)";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $idPerfil);

        if ($stmt->execute()) {
            $resultado = $stmt->get_result();

            if ($resultado->num_rows == 1) {
                $perfil = $resultado->fetch_assoc();
                $nombrePerfil = $perfil["nombrePerfil"];
                $fotoPerfill = $perfil["fotoPerfil"];
                $descripcion = $perfil["descripcion"];
                $fotoPerfil = "../".$fotoPerfill;
            } else {
                header("location: index.php");
                exit();
            }
        } else {
            echo "There was an error obtaining profile details";
        }

        $stmt->close();
    } else {
        echo "Query preparation error.";
    }

    // Consultar la información del perfil según su ID (parte 2)
    $sql = "SELECT nombreUsuario FROM Usuarios WHERE idUsuario = ?";
    if ($stmt = $mysqli->prepare($sql)) {
        $stmt->bind_param("i", $idPerfil);

        if ($stmt->execute()) {
            $resultado = $stmt->get_result();

            if ($resultado->num_rows == 1) {
                $usuario = $resultado->fetch_assoc();
                $nombreUsuario = $usuario["nombreUsuario"];
            } else {
                header("location: index.php");
                exit();
            }
        } else {
            echo "There was an error obtaining user details.";
        }

        $stmt->close();
    } else {
        echo "Query preparation error.";
    }

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
              $stmt->bind_result($rutaFotol);
  
              // Obtenemos el resultado de la consulta
              $stmt->fetch();
  
              // Cerramos el statement
              $stmt->close();
          }
      }
      $rutaFoto = "../".$rutaFotol;

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
  } else {
    $idUsuario=0;
  }

  $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $itemsPorPagina = 10;
    $offset = ($page - 1) * $itemsPorPagina;

    $sqlCount = "SELECT COUNT(*) as total FROM BibliotecaPersonal WHERE idUsuario = ?";
    if ($stmtCount = $mysqli->prepare($sqlCount)) {
        $stmtCount->bind_param("i", $idPerfil);
        $stmtCount->execute();
        $resultCount = $stmtCount->get_result();
        $totalJuegos = $resultCount->fetch_assoc()['total'];
    }

    $totalPaginas = ceil($totalJuegos / $itemsPorPagina);

    $sqlCountPorJugar = "SELECT COUNT(*) as total FROM BibliotecaPersonal WHERE idUsuario = ? AND estadoJuego = '1'";
    $sqlCountEnProgreso = "SELECT COUNT(*) as total FROM BibliotecaPersonal WHERE idUsuario = ? AND estadoJuego = '2'";
    $sqlCountCompletado = "SELECT COUNT(*) as total FROM BibliotecaPersonal WHERE idUsuario = ? AND estadoJuego = '3'";
    $sqlCountDeseado = "SELECT COUNT(*) as total FROM BibliotecaPersonal WHERE idUsuario = ? AND estadoJuego = '4'";

    if ($stmtCountPorJugar = $mysqli->prepare($sqlCountPorJugar)) {
        $stmtCountPorJugar->bind_param("i", $idPerfil);
        $stmtCountPorJugar->execute();
        $resultCountPorJugar = $stmtCountPorJugar->get_result();
        $totalPorJugar = $resultCountPorJugar->fetch_assoc()['total'];
    }

    if ($stmtCountEnProgreso = $mysqli->prepare($sqlCountEnProgreso)) {
        $stmtCountEnProgreso->bind_param("i", $idPerfil);
        $stmtCountEnProgreso->execute();
        $resultCountEnProgreso = $stmtCountEnProgreso->get_result();
        $totalEnProgreso = $resultCountEnProgreso->fetch_assoc()['total'];
    }
    if ($stmtCountCompletado = $mysqli->prepare($sqlCountCompletado)) {
      $stmtCountCompletado->bind_param("i", $idPerfil);
      $stmtCountCompletado->execute();
      $resultCountCompletado = $stmtCountCompletado->get_result();
      $totalCompletado = $resultCountCompletado->fetch_assoc()['total'];
    }
    
    if ($stmtCountDeseado = $mysqli->prepare($sqlCountDeseado)) {
        $stmtCountDeseado->bind_param("i", $idPerfil);
        $stmtCountDeseado->execute();
        $resultCountDeseado = $stmtCountDeseado->get_result();
        $totalDeseado = $resultCountDeseado->fetch_assoc()['total'];
    }

    $totalPaginasPorJugar = ceil($totalPorJugar / $itemsPorPagina);
    $totalPaginasEnProgreso = ceil($totalEnProgreso / $itemsPorPagina);
    $totalPaginasCompletado = ceil($totalCompletado / $itemsPorPagina);
    $totalPaginasDeseado = ceil($totalDeseado / $itemsPorPagina);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $nombreUsuario?></title>
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
  <link rel="stylesheet" href="css/styleprofile.css">
  
  <!-- =======================================================
  * Template Name: BizLand
  * Updated: Jul 27 2023 with Bootstrap v5.3.1
  * Template URL: https://bootstrapmade.com/bizland-bootstrap-business-template/
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>
<body style="background: linear-gradient(to right, #6607ff, #121212 5%, #121212 95%, #6607ff);">

<header id="header" class="d-flex align-items-center" style="padding-bottom: 10px;">
      <div class="container d-flex align-items-center justify-content-between">  
        <a href="index.php"><img src="css/startStyle/assets/img/favicon.png" alt="" srcset="" height="80%" width="80px"></a>
        <h1 class="logo" style="margin-right: 30px;"><a href="index.php">Game<span>Vault</span></a></h1>
        <nav id="navbar" class="navbar">
          <ul>
            <li><a class="nav-link scrollto" href="index.php">Start</a></li>
            <li><a class="nav-link scrollto" href="index.php?order=az">A to Z</a></li>
            <li><a class="nav-link scrollto" href="index.php?order=rated">Best Rated</a></li>
            <li><a class="nav-link scrollto" href="index.php?order=recent">Recently Added</a></li>
          </ul>
        </nav>
        <?php if ($idUsuario !== 0) {?>
                <img src="<?php echo $rutaFoto; ?>" alt="usuario" class="user-pic" onclick="toggleMenu()">
        <?php } else { ?>
                <a href="#" onclick="cambiarIdioma();"><img class="lan-pic" src="css/startStyle/assets/img/switch.png" height="30px" width="30px"></a>
        <?php }?>
        <div class="sub-menu-wrap bg-dark" id="subMenu">
          <div class="sub-menu">
            <div class="user-info">
              <img src="<?php echo $rutaFoto; ?>" height="50" width="50">
              <h3><?php echo $username ?></h3>
            </div>
          <hr>
          <a href="profile.php?id=<?php echo urlencode($_SESSION["idUsuario"]); ?>"class="sub-menu-link">
            <i class="bi bi-person-fill"></i>
            <p>View profile</p>
          <span></span>
          </a>
          <a href="settings.php?id= <?php echo urlencode($_SESSION["idUsuario"]); ?>"class="sub-menu-link">
            <i class="bi bi-gear-fill"></i>
            <p>Settings</p>
            <span></span>            
          </a>
          <a href="create.php"class="sub-menu-link">
            <i class="bi bi-plus-square-fill"></i>
            <p>Add Game</p>
            <span></span>
          </a>
          <a href="logout.php"class="sub-menu-link">
            <i class="bi bi-box-arrow-left"></i>
            <p>Log Out</p>
            <span></span>
          </a>
          </div>
        </div>
    </header>

    <section class="seccion-perfil-usuario">
        <div class="perfil-usuario-header">
          <div class="perfil-usuario-portada">
            <div class="perfil-usuario-avatar">
              <img src="<?php echo $fotoPerfil ?>" alt="perfil">
            </div>
          <?php
          if ($idUsuario !== 0) {
            if ($_SESSION['idUsuario'] == $idPerfil) {
              echo "<a class='boton-portada' href='settings.php?id=" . $idUsuario . "'>";
              echo "<i class='bi bi-pencil-fill'></i> Editar perfil";
              echo "</a>";
            }
          }
          
          ?>
          </div>

        </div>
        <div class="perfil-usuario-body">
            <div class="perfil-usuario-bio">
                <h3 class="titulo"><?php echo $nombrePerfil?></h3>
                <h2 class="subtitulo">@<?php echo $nombreUsuario?></h2>
                <p class="texto"><?php echo $descripcion?></p>
                <p class ="subtitulo">I have <?php echo $totalJuegos?> games in my library!</p>
            </div>
            <table class="perfil-usuario-footer">
            <div class="filtro-botones">
              <button class="btn btn-danger" onclick="mostrarTodos()">Show All</button>
              <button class="btn btn-primary" onclick="filtrarJuegos('To Play')">To Play</button>
              <button class="btn btn-info" onclick="filtrarJuegos('In Progress')">In Progress</button>
              <button class="btn btn-warning" onclick="filtrarJuegos('Completed')">Completed</button>
              <button class="btn btn-light" onclick="filtrarJuegos('Wished')">Wished</button>
          </div>
                <tr>
                    <th>Game Cover</th>
                    <th>Title</th>
                    <th>Game Status</th>
                    <th>Entry Date</th>
                    <th>Actions</th>
                </tr>
                <?php
                $sql = "CALL ConsultarBibliotecaPersonal(?, ?, ?)";
                if ($stmt = $mysqli->prepare($sql)) {
                    $stmt->bind_param("iii", $idPerfil, $offset, $itemsPorPagina);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $fotoJuegol = $row['fotoJuego'];
                            $fotoJuego = "../".$fotoJuegol;
                            echo '<tr>';
                            echo '<td><img src="' .$fotoJuego. '" alt="'. htmlspecialchars($row['titulo']) .'" class="card-img-top"></td>';
                            echo '<td>' . htmlspecialchars($row['titulo']) . '</td>';
                            if ($row['estadoJuego'] == 1) {
                              echo '<td>To Play</td>';
                            } elseif ($row['estadoJuego'] == 2) {
                              echo '<td>In Progress</td>';
                            } elseif ($row['estadoJuego'] == 3) {
                              echo '<td>Completed</td>';
                            } elseif ($row['estadoJuego'] == 4) {
                              echo '<td>Wished</td>';
                            }
                            echo '<td>' . htmlspecialchars($row['fechaAdicion']) . '</td>';
                            echo '<td>';

                            echo '<a href="game.php?id=' . $row['idJuego'] . '" class="btn btn-success" style="display: block; margin: 0 auto;" title="View Game" data-toggle="tooltip"><i class="bi bi-eye-fill"></i></a>';

                            if ($idUsuario == $idPerfil) {
                                echo '<div id="editForm_' . $row['idBibliotecaPersonal'] . '" style="display: none;">';
                                echo '<form action="biblioupdate.php" method="post">';
                                echo '<input type="hidden" name="idBibliotecaPersonal" value="' . $row['idBibliotecaPersonal'] . '">';
                                echo '<select name="estadoJuego" class="form-control">';
                                echo '<option value="1" ' . ($row['estadoJuego'] == 1 ? 'selected' : '') . '>To Play</option>';
                                echo '<option value="2" ' . ($row['estadoJuego'] == 2 ? 'selected' : '') . '>In Progress</option>';
                                echo '<option value="3" ' . ($row['estadoJuego'] == 3 ? 'selected' : '') . '>Completed</option>';
                                echo '<option value="4" ' . ($row['estadoJuego'] == 4 ? 'selected' : '') . '>Wished</option>';
                                echo '</select>';
                                echo '<button type="submit" class="btn btn-info" style="display: block; margin: 0 auto;" title="Change Status" data-toggle="tooltip"  ><i class="bi bi-check-circle-fill"></i></button>';
                                echo '</form>';
                                echo '</div>';

                                echo '<button id="editButton_' . $row['idBibliotecaPersonal'] . '" class="btn btn-warning" style="display: block; margin: 0 auto;" title="Change Status" data-toggle="tooltip" onclick="toggleEditForm(' . $row['idBibliotecaPersonal'] . ');"><i class="bi bi-pencil-fill"></i></button>';

                                echo '<form action="bibliodelete.php" method="post">';
                                echo '<input type="hidden" name="idBibliotecaPersonal" value="' . $row['idBibliotecaPersonal'] . '">';
                                echo '<button type="submit" class="btn btn-danger" style="display: block; margin: 0 auto;" title="Delete Game" data-toggle="tooltip"><i class="bi bi-trash-fill"></i></button>';
                                echo '</form>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5">Your library is empty, go get some games!</td></tr>';
                    }

                    $result->close();
                    $stmt->close();
                } else {
                    echo '<tr><td colspan="5">There was an error calling the procedure.</td></tr>';
                }
                ?>
            </table>
            <div class="pagination">
              <?php
              $estadoFiltrado = isset($_GET['estadoJuego']) ? $_GET['estadoJuego'] : '';

              if ($estadoFiltrado === '') {
                  $totalPaginas = $totalPaginas; // Mostrar todos los juegos
              } elseif ($estadoFiltrado === '1') {
                  $totalPaginas = $totalPaginasPorJugar;
              } elseif ($estadoFiltrado === '2') {
                  $totalPaginas = $totalPaginasEnProgreso;
              } elseif ($estadoFiltrado === '3') {
                  $totalPaginas = $totalPaginasCompletado;
              } elseif ($estadoFiltrado === '4') {
                  $totalPaginas = $totalPaginasDeseado;
              }

              // Bucle para generar enlaces de paginación según el estado filtrado
              for ($i = 1; $i <= $totalPaginas; $i++) {
                  echo '<a class="btn btn-secondary" href="profile.php?id=' . $idPerfil . '&page=' . $i . '&estado=' . urlencode($estadoFiltrado) . '">' . $i . '</a>';
              }
              ?>
          </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
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
<script>
function toggleEditForm(id) {
    var editForm = document.getElementById('editForm_' + id);
    var editButton = document.getElementById('editButton_' + id);

    if (editForm.style.display === 'none') {
        editForm.style.display = 'block';
        editButton.style.display = 'none'; // Ocultar el botón al mostrar el formulario
    } else {
        editForm.style.display = 'none';
        editButton.style.display = 'block'; // Mostrar el botón al ocultar el formulario
    }
}
</script>
<script>
    function filtrarJuegos(estado) {
        var rows = document.querySelectorAll(".perfil-usuario-footer tr");
        for (var i = 1; i < rows.length; i++) { // Comenzamos desde 1 para omitir la fila de encabezado
            var cell = rows[i].getElementsByTagName("td")[2]; // El índice 2 corresponde a la columna "Estado del Juego"
            if (cell) {
                var contenido = cell.textContent || cell.innerText;
                rows[i].style.display = contenido === estado ? "" : "none";
            }
        }
    }

    function mostrarTodos() {
        var rows = document.querySelectorAll(".perfil-usuario-footer tr");
        for (var i = 1; i < rows.length; i++) {
            rows[i].style.display = "";
        }
    }
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
    var idPerfil = <?php echo $idPerfil; ?>;
    function cambiarIdioma() {
        window.location.href = "../profile.php?id=" + idPerfil;
    }
  </script>
</body>
</html>
