<?php
$host="mysql8002.site4now.net";
$user="a9d8b4_gamev";
$pwd="equipo5de";
$bd="db_a9d8b4_gamev";

$mysqli=new mysqli($host,$user,$pwd,$bd);

if($mysqli->connect_error){
    die("Fallo la conexión".$mysqli->connect_error);

}

?>