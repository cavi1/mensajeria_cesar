<?php

session_start();

// Si ya está logueado, redirigir al index
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

// Variables para mensajes
$error = '';
$success = '';
$usuario_valor = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener y limpiar datos
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $usuario_valor = htmlspecialchars($usuario);
    
    // Validaciones básicas
    if (empty($usuario) || empty($password)) {
        $error = 'Usuario y contraseña son obligatorios';
    } else {
        // Buscar usuario en la base de datos
        $consulta = "SELECT id_usuario, nombre, apellido, nombre_usuario, correo, contraseña, ultimo_acceso 
                FROM usuarios 
                WHERE nombre_usuario = ?";
        
        $stmt = $conexion->prepare($consulta);
        
        if ($stmt) {
            $stmt->bind_param("s", $usuario);
            
            if ($stmt->execute()) {
                $result = $stmt->get_result();//se que el resultado es unico porque el nombre de usuario es unico, y necesito todos los datos del usuario
                
                if ($result->num_rows === 1) {//si esta el usuario en la bd
                    $user = $result->fetch_assoc(); //me traigo los datos a un arreglo asociativo
                    
                    // Verificar contraseña
                    if (password_verify($password, $user['contraseña'])) {//password verify compara la contraseña ingresada con la hasheada en la bd (con password_hash)
                        // LOGIN EXITOSO
                        
                        // 1. Guardar último acceso anterior para contar mensajes nuevos
                        $ultimo_acceso_anterior = $user['ultimo_acceso'];

                        
                        // 2. Actualizar último acceso a AHORA
                        $actualizar_ultimo_acceso = "UPDATE usuarios SET ultimo_acceso = NOW() 
                                      WHERE id_usuario = ?";
                        $stmt_update = $conexion->prepare($actualizar_ultimo_acceso);
                        $stmt_update->bind_param("i", $user['id_usuario']);
                        $stmt_update->execute();
                        $stmt_update->close();
                        
                        // 3. Contar mensajes nuevos desde el último acceso
                        $mensajes_nuevos = 0;
                        if ($ultimo_acceso_anterior) {//si es null no entra
                            $sql_mensajes_nuevos = "SELECT COUNT(*) as total 
                                            FROM mensajes 
                                            WHERE id_destinatario = ? 
                                            AND fecha_envio > ? 
                                            AND leido = 0";
                            $stmt_mensajes = $conexion->prepare($sql_mensajes_nuevos);
                            $stmt_mensajes->bind_param("is", $user['id_usuario'], $ultimo_acceso_anterior);
                            $stmt_mensajes->execute();
                            $result_mensajes_nuevos = $stmt_mensajes->get_result(); 
                            $row_mensajes = $result_mensajes_nuevos->fetch_assoc();//me da la row con los datos del mensaje nuevo
                            $mensajes_nuevos = $row_mensajes['total'] ?? 0; //es el count de mensajes nuevos
                            $stmt_mensajes->close();
                        }
                        
                        // 4. Guardar datos en sesión
                        $_SESSION['usuario_id'] = $user['id_usuario'];
                        $_SESSION['nombre_usuario'] = $user['nombre_usuario'];
                        $_SESSION['nombre_completo'] = $user['nombre'] . ' ' . $user['apellido'];
                        $_SESSION['email'] = $user['correo'];
                        $_SESSION['ultimo_acceso'] = $ultimo_acceso_anterior;
                        $_SESSION['mensajes_nuevos'] = $mensajes_nuevos;
                        
                        // 5. Preparar mensaje de bienvenida
                        $fecha_formateada = $ultimo_acceso_anterior 
                            ? date('d/m/Y H:i:s', strtotime($ultimo_acceso_anterior))
                            : 'Primer acceso';
                        
                        $success = "¡Bienvenido, {$user['nombre']}!<br>";
                        $success .= "Último acceso: {$fecha_formateada}<br>";
                        $success .= "Mensajes nuevos: {$mensajes_nuevos}<br>";
                        $success .= "Redirigiendo al panel principal...";
                        
                        // 6. Redirigir después de 3 segundos
                        header("refresh:3;url=index.php");
                        
                    } else {
                        $error = 'Contraseña incorrecta';
                    }
                } else {
                    $error = 'Usuario no encontrado';
                }
            } else {
                $error = 'Error en la consulta: ' . $stmt->error;
            }
            
            $stmt->close();
        } else {
            $error = 'Error al preparar la consulta: ' . $conexion->error;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mensajería César</title>
    <script src="../assets/js/login.js"></script>
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
                           required 
                           autofocus>
                </div>
                
                <div>
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" 
                           required>
                </div>
                
                <div>
                    <button type="submit">Iniciar Sesión</button>
                </div>
            </form>
            
            <div>
                <p>¿No tenes una cuenta? <a href="registro.php">Registrate acá</a></p>
            </div>
        <?php endif; ?>
        
        <div>
            <small>
                Sistema de Mensajería con Cifrado César<br>
                Universidad Nacional de la Patagonia San Juan Bosco
            </small>
        </div>
    </div>
</body>
</html>