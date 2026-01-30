<?php
// pages/login.php
//require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../clases/usuario.php';
require_once __DIR__ . '/../clases/mensaje.php';
require_once __DIR__ . '/../config/sesion.php';

// Si ya está logueado, redirigir al index
if (usuario_esta_autenticado()) {
    header("Location: ../index.php");
    exit();
}

// Variables para mensajes
$error = '';
$success = '';
$usuario_valor = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario_input = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $usuario_valor = htmlspecialchars($usuario_input);
    
 
    // Usar la clase Usuario
    $usuario_instancia = new Usuario($conexion);
    $resultado = $usuario_instancia->login($usuario_input, $password);
    
    if ($resultado['success']) {
        // Usar la clase Mensaje para contar mensajes nuevos
        $mensaje_instancia = new Mensaje($conexion);
        
        $ultimo_acceso_anterior = $resultado['usuario']['ultimo_acceso'];
        $mensajes_nuevos = 0;
        
        if ($ultimo_acceso_anterior) {
            $mensajes_nuevos = $mensaje_instancia->contar_nuevos_desde(
                $resultado['usuario']['id'],
                $ultimo_acceso_anterior
            );
        }
        
        // Obtener IDs de mensajes no leídos
        $ids_no_leidos = $mensaje_instancia->obtener_ids_no_leidos($resultado['usuario']['id']);
        
        // Establecer datos en sesión
        establecer_usuario_sesion($resultado['usuario']);
        $_SESSION['ultimo_acceso'] = $ultimo_acceso_anterior;
        $_SESSION['mensajes_nuevos'] = $mensajes_nuevos;
        $_SESSION['ids_mensajes_no_leidos'] = $ids_no_leidos;
        
        // Preparar mensaje de bienvenida
        $fecha_formateada = $ultimo_acceso_anterior 
            ? date('d/m/Y H:i:s', strtotime($ultimo_acceso_anterior))
            : 'Primer acceso';
        
        $success = "¡Bienvenido, {$resultado['usuario']['nombre']}!<br>";
        $success .= "Último acceso: {$fecha_formateada}<br>";
        $success .= "Mensajes nuevos: {$mensajes_nuevos}<br>";
        $success .= "Redirigiendo al panel principal...";
        
        header("refresh:3;url=../pages/index.php");
    } else {
        $error = $resultado['message'];
    }
   
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mensajería César</title>
</head>
<body>
    <div>
        <h1>Mensajería César - Inicio de Sesión</h1>
        
        <?php if (!empty($error)): ?>
            <div>
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div>
                <strong>Éxito:</strong> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($success)): ?>
            <form method="POST" action="">
                <div>
                    <label for="usuario">Nombre de usuario:</label>
                    <input type="text" id="usuario" name="usuario" 
                           value="<?php echo $usuario_valor; ?>" 
                           required autofocus>
                </div>
                
                <div>
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div>
                    <button type="submit">Iniciar Sesión</button>
                </div>
            </form>
            
            <div>
                <p>¿No tenes una cuenta? <a href="registro.php">Registrate acá</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>