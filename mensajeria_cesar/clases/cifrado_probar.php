<?php
// test_cifrador.php en tu proyecto

require_once 'cifrado_cesar.php';

$cifrador = new Cifrado_cesar();

echo "<h2>Prueba del Cifrador César</h2>";

// Prueba básica
$texto = "Año 2025";
$cifrado = $cifrador->encriptar($texto, 3);

echo "Texto: $texto<br>";
echo "Cifrado: $cifrado<br>";

$texto = "ñ";
$cifrado = $cifrador->encriptar($texto, 3);

echo "Texto: $texto<br>";
echo "Cifrado: $cifrado<br>";

$texto = "Dqr 5358";
$cifrado = $cifrador->desencriptar($texto, 3);

echo "Texto: $texto<br>";
echo "Descifrado: $cifrado<br>";

?>