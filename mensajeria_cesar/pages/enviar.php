<?php
// pages/enviar.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../clases/usuario.php';
require_once __DIR__ . '/../clases/mensaje.php';
require_once __DIR__ . '/../config/sesion.php';

// Verificar autenticación
if (!usuario_esta_autenticado()) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $de_usuario_id = $_SESSION['usuario_id'];
    $para_usuario_id = $_POST['destinatario'] ?? '';
    $asunto = trim($_POST['asunto'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $desplazamiento = $_POST['desplazamiento'] ?? 69;
    
    if (empty($para_usuario_id) || empty($asunto) || empty($mensaje)) {
       $error = 'Todos los campos son obligatorios. id_destinatario: ' . $para_usuario_id . ' asunto: ' . $asunto . ' mensaje: ' . $mensaje . ' desplazamiento: ' . $desplazamiento;
    } else {
        $mensaje_instancia = new Mensaje($conexion);
        $resultado = $mensaje_instancia->enviar($de_usuario_id, $para_usuario_id, $asunto, $mensaje, $desplazamiento);
        
        if ($resultado['success']) {
            $success = $resultado['message'];
            // Redirigir después de 2 segundos
            header("refresh:2;url=../index.php");
        } else {
            $error = $resultado['message'];
        }
    }
} else {
    // Si no es POST, redirigir al index
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Enviar Mensaje</title>
    <link rel="stylesheet" href="../assets/css/estilos.css">
</head>
<body>
    <h1>Enviar Mensaje</h1>
    
    <?php if (!empty($error)): ?>
        <div style="color: red;">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
        <p><a href="../index.php">Volver al dashboard</a></p>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div style="color: green;">
            <strong>Éxito:</strong> <?php echo $success; ?>
        </div>
        <p>Redirigiendo al dashboard en 2 segundos...</p>
    <?php endif; ?>
</body>
</html>