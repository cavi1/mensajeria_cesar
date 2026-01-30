<?php
// pages/registro.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../clases/usuario.php';
require_once __DIR__ . '/../config/sesion.php';

// Redirigir si ya está logueado
if (usuario_esta_autenticado()) {
    header("Location: ../index.php");
    exit();
}

// Variables para el formulario
$error = '';
$success = '';
$nombre = '';
$apellido = '';
$email = '';
$usuario = '';

// si se mando una vez el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $usuario_instancia = new Usuario($conexion);
    
    if ($password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $resultado = $usuario_instancia->registrar($nombre, $apellido, $email, $usuario, $password);
        
        if ($resultado['success']) {
            $success = $resultado['message'];
            // Limpiar campos
            $nombre = $apellido = $email = $usuario = '';
        } else {
            $error = $resultado['message'];
        }
    }
}

// Cerrar conexión (opcional, php la cierra automáticamente al final del script)
//primero mira si se instancio la clase usuario (si es q hubo post)
if (isset($usuario_instancia)) {
    $usuario_instancia->cerrar_conexion();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Registro - Mensajería César</title>
</head>
<script src="../assets/js/registro.js"></script>
<body>
    <div>
        <h1>Mensajería César - Registro</h1>
        
        <?php if (!empty($error)): ?>
            <div>
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div>
                <strong>Éxito:</strong> <?php echo $success; ?>
            </div>
        <?php else: ?>
        
        <form method="POST" action="" onsubmit="return validar_contraseñas_formulario()">
            <div>
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" 
                       value="<?php echo htmlspecialchars($nombre); ?>" required>
            </div>
            
            <div>
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" 
                       value="<?php echo htmlspecialchars($apellido); ?>" required>
            </div>
            
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            
            <div>
                <label for="usuario">Nombre de usuario:</label>
                <input type="text" id="usuario" name="usuario" 
                       value="<?php echo htmlspecialchars($usuario); ?>" 
                       minlength="4" required>
                <small>(Mínimo 4 caracteres)</small>
            </div>
            
            <div>
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" 
                       minlength="6" required>
                <small>(Mínimo 6 caracteres)</small>
            </div>
            
            <div>
                <label for="confirm_password">Confirmar contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       minlength="6" required>
            </div>
            
            <div>
                <button type="submit">Registrarse</button>
            </div>
        </form>
        
        <div>
            <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>