<?php

session_start();

// Redirigir si ya está logueado
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

// Variables para el formulario
$error = '';
$success = '';
$nombre = '';
$apellido = '';
$email = '';
$usuario = '';

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener y limpiar datos
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $email = trim($_POST['email']);
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones básicas, estas se hacen del lado del servidor, por el contrario, pueden ser manipuladas usando herramientas de desarrollo del navegador
    $campos_requeridos = [$nombre, $apellido, $email, $usuario, $password, $confirm_password];
    
    foreach ($campos_requeridos as $campo) {
        if (empty($campo)) {
            $error = 'Todos los campos son obligatorios';
            break;
        }
    }
    
    if (empty($error) && !filter_var($email, FILTER_VALIDATE_EMAIL)) { //constante predefinida de php para validar email
        $error = 'El email no tiene un formato válido';
    }
    
    if (empty($error) && $password !== $confirm_password) {
        $error = 'Las contraseñas no coinciden';
    }
    
    if (empty($error) && strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres';
    }
    
    if (empty($error) && strlen($usuario) < 4) {
        $error = 'El nombre de usuario debe tener al menos 4 caracteres';
    }
    
    // Si no hay errores de validación, proceder con BD
    if (empty($error)) {
        // Verificar si usuario o email ya existen
        $consulta = "SELECT id_usuario FROM usuarios 
                         WHERE nombre_usuario = ? OR correo = ?";
        
        $stmt_verificar = $conexion->prepare($consulta); //envia la estrcutura sql a la bd para su preparacion (sin datos) y devuelve un objeto
        
        if ($stmt_verificar) { //php puede comparar objetos con true o false, en este caso es true si la preparacion fue exitosa ya que devolvio un objeto
            $stmt_verificar->bind_param("ss", $usuario, $email);//vincula los datos a la estructura preparada, "ss" indica que son dos cadenas de texto en ese orden
            $stmt_verificar->execute();//ejecuta la consulta ya con los datos vinculados
            $stmt_verificar->store_result();//almacena el resultado para poder contar filas
            
            if ($stmt_verificar->num_rows > 0) {
                $error = 'El nombre de usuario o email ya están registrados';
            } else {
                // Hash de la contraseña
                $hashed_password = password_hash($password, PASSWORD_DEFAULT); //algoritmo predefinido de php para hashear contraseñas
                
                // Insertar nuevo usuario
                $sql_insertar = "INSERT INTO usuarios 
                                (nombre, apellido, nombre_usuario, correo, contraseña) 
                                VALUES (?, ?, ?, ?, ?)";
                
                $stmt_insertar = $conexion->prepare($sql_insertar);
                
                if ($stmt_insertar) {
                    $stmt_insertar->bind_param("sssss", $nombre, $apellido, $usuario, $email, $hashed_password);
                    
                    if ($stmt_insertar->execute()) {
                        $success = 'Registro exitoso. Ahora puedes <a href="login.php">iniciar sesión</a>';
                        // Limpiar campos después de registro exitoso
                        $nombre = $apellido = $email = $usuario = '';
                    } else {
                        $error = 'Error al registrar el usuario: ' . $stmt_insertar->error;
                    }
                    
                    $stmt_insertar->close();
                } else {
                    $error = 'Error al preparar la consulta de inserción: ' . $conexion->error;
                }
            }
            
            $stmt_verificar->close();
        } else {
            $error = 'Error al preparar la consulta de verificación: ' . $conexion->error;
        }
    }
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Registro - Mensajería César</title>
<script src="../assets/js/registro.js"></script>
</head>
<body>
    <div>
        <h1>Mensajería César - Registro</h1>
        
        <?php if (!empty($error)): ?>
            <div>
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?> <!-- Me guarda lo que ya habia ingresado cuando se envia el form para no perderlo-->
            </div> <!--htmlspecialchars previene inyeccion de codigo malicioso-->
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div>
                <strong>Éxito:</strong> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div>
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" 
                       value="<?php echo htmlspecialchars($nombre); ?>" 
                       required>
            </div>
            
            <div>
                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" 
                       value="<?php echo htmlspecialchars($apellido); ?>" 
                       required>
            </div>
            
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($email); ?>" 
                       required>
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
                <button type="submit" onclick="return validar_mail_password()">Registrarse</button>
            </div>
        </form>
        
        <div>
            <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        </div>
        
        <div>
            <small>
                Sistema de Mensajería con Cifrado César<br>
                Universidad Nacional de la Patagonia San Juan Bosco
            </small>
        </div>
    </div>
</body>
</html>