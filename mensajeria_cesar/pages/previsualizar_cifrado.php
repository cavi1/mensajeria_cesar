<?php
// pages/previsualizar_cifrado.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../clases/cifrado_cesar.php';

header('Content-Type: application/json');

// Solo procesar si es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Obtener datos del POST
$asunto = $_POST['asunto'] ?? '';
$mensaje = $_POST['mensaje'] ?? '';
$desplazamiento = $_POST['desplazamiento'] ?? 4;

if (empty($asunto) || empty($mensaje)) {
    echo json_encode(['success' => false, 'message' => 'Asunto y mensaje son requeridos']);
    exit();
}

// Instanciar cifrador
$cifrador = new Cifrado_cesar();

// Cifrar asunto y mensaje
$asunto_cifrado = $cifrador->encriptar($asunto, $desplazamiento);
$mensaje_cifrado = $cifrador->encriptar($mensaje, $desplazamiento);

// Devolver resultado
echo json_encode([
    'success' => true,
    'asunto_cifrado' => $asunto_cifrado,
    'mensaje_cifrado' => $mensaje_cifrado,
    'desplazamiento' => $desplazamiento
]);
exit();