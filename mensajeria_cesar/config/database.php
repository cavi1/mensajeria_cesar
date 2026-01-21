<?php

$server="localhost";
$user="root";
$pass="";
$db="mensajeria_cesar";
mysqli_report(MYSQLI_REPORT_OFF);
$conexion=new mysqli($server, $user, $pass, $db);

if ($conexion->connect_errno){
    die("No es posible conectar con la BD");
}

?>