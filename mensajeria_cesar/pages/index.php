<?php


session_start();

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: pages/login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

// Obtener datos del usuario actual
$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre_usuario'];
$nombre_completo = $_SESSION['nombre_completo'] ?? 'Usuario';
$mensajes_nuevos = $_SESSION['mensajes_nuevos'] ?? 0;

// Obtener mensajes recibidos
$mensajes_recibidos = [];
$sql_recibidos = "SELECT m.id_mensaje, m.asunto_encriptado, m.fecha_envio, 
                         u.nombre_usuario as remitente, m.leido, m.desplazamiento
                  FROM mensajes m
                  JOIN usuarios u ON m.id_remitente = u.id_usuario
                  WHERE m.id_destinatario = ?
                  ORDER BY m.fecha_envio DESC
                  LIMIT 10"; //joineo la tabla usuarios con la de mensajes para obtener el nombre de usuario del remitente 
                  //el enunciado no especifica cuantos mensajes nuevos mostrar, pongo 10 como ejemplo

if ($stmt_recibidos = $conexion->prepare($sql_recibidos)) {
    $stmt_recibidos->bind_param("i", $usuario_id);
    $stmt_recibidos->execute();
    $result_recibidos = $stmt_recibidos->get_result();
    
    while ($fila = $result_recibidos->fetch_assoc()) {
        $mensajes_recibidos[] = $fila;//almaceno cada fila en el arreglo de mensajes recibidos, por eso uso [] para agregar al final, queda como un arreglo enumerado de arreglos asociativos
    }
    $stmt_recibidos->close();
}

// Obtener mensajes enviados
$mensajes_enviados = [];
$sql_enviados = "SELECT m.id_mensaje, m.asunto_encriptado, m.fecha_envio,
                        u.nombre_usuario as destinatario, m.desplazamiento
                 FROM mensajes m
                 JOIN usuarios u ON m.id_destinatario = u.id_usuario
                 WHERE m.id_remitente = ?
                 ORDER BY m.fecha_envio DESC
                 LIMIT 10";

if ($stmt_enviados = $conexion->prepare($sql_enviados)) {
    $stmt_enviados->bind_param("i", $usuario_id);
    $stmt_enviados->execute();
    $result_enviados = $stmt_enviados->get_result();
    
    while ($fila = $result_enviados->fetch_assoc()) {
        $mensajes_enviados[] = $fila;
    }
    $stmt_enviados->close();
}

// Obtener lista de usuarios para enviar mensajes
$usuarios = [];
$sql_usuarios = "SELECT id_usuario, nombre_usuario, nombre, apellido 
                 FROM usuarios 
                 WHERE id_usuario != ? 
                 ORDER BY nombre_usuario";

if ($stmt_usuarios = $conexion->prepare($sql_usuarios)) {
    $stmt_usuarios->bind_param("i", $usuario_id);
    $stmt_usuarios->execute();
    $result_usuarios = $stmt_usuarios->get_result();
    
    while ($fila = $result_usuarios->fetch_assoc()) {
        $usuarios[] = $fila;
    }
    $stmt_usuarios->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mensajería César</title>
</head>
<body>
    <header>
        <h1>🔐 Mensajería César - Panel Principal</h1>
        <div>
            <p>Bienvenido, <strong><?php echo htmlspecialchars($nombre_completo); ?></strong></p>
            <p>Usuario: <?php echo htmlspecialchars($nombre_usuario); ?></p>
            <p>Mensajes nuevos: <strong><?php echo $mensajes_nuevos; ?></strong></p>
            <a href="pages/logout.php">Cerrar Sesión</a>
        </div>
    </header>
    
    <main>
        <!-- MENSAJES RECIBIDOS -->
        <section>
            <h2>📥 Mensajes Recibidos</h2>
            
            <?php if (empty($mensajes_recibidos)): ?>
                <p>No tienes mensajes recibidos.</p>
            <?php else: ?>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Remitente</th>
                            <th>Asunto</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mensajes_recibidos as $mensaje): ?>
                            <tr style="<?php echo $mensaje['leido'] ? '' : 'font-weight: bold;'; ?>">
                                <td><?php echo htmlspecialchars($mensaje['remitente']); ?></td>
                                <td><?php echo htmlspecialchars($mensaje['asunto_encriptado']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_envio'])); ?></td>
                                <td><?php echo $mensaje['leido'] ? 'Leído' : 'Nuevo'; ?></td>
                                <td>
                                    <a href="pages/ver_mensaje.php?id=<?php echo $mensaje['id_mensaje']; ?>&tipo=recibido">
                                        Ver
                                    </a>
                                    <?php if (!$mensaje['leido']): ?>
                                        | <a href="pages/marcar_leido.php?id=<?php echo $mensaje['id_mensaje']; ?>">
                                            Marcar como leído
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><a href="pages/mensajes_recibidos.php">Ver todos los mensajes recibidos</a></p>
            <?php endif; ?>
        </section>
        
        <!-- MENSAJES ENVIADOS -->
        <section>
            <h2>📤 Mensajes Enviados</h2>
            
            <?php if (empty($mensajes_enviados)): ?>
                <p>No has enviado mensajes.</p>
            <?php else: ?>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Destinatario</th>
                            <th>Asunto</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mensajes_enviados as $mensaje): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($mensaje['destinatario']); ?></td>
                                <td><?php echo htmlspecialchars($mensaje['asunto_encriptado']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_envio'])); ?></td>
                                <td>
                                    <a href="pages/ver_mensaje.php?id=<?php echo $mensaje['id_mensaje']; ?>&tipo=enviado">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p><a href="pages/mensajes_enviados.php">Ver todos los mensajes enviados</a></p>
            <?php endif; ?>
        </section>
        
        <!-- ENVIAR NUEVO MENSAJE -->
        <section>
            <h2>📨 Enviar Nuevo Mensaje</h2>
            
            <form method="POST" action="pages/enviar.php">
                <div>
                    <label for="destinatario">Destinatario:</label>
                    <select id="destinatario" name="destinatario" required>
                        <option value="">Selecciona un usuario</option>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?php echo $usuario['id_usuario']; ?>">
                                <?php echo htmlspecialchars($usuario['nombre_usuario'] . ' (' . $usuario['nombre'] . ' ' . $usuario['apellido'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="desplazamiento">Desplazamiento (1-26):</label>
                    <input type="number" id="desplazamiento" name="desplazamiento" 
                           min="1" max="26" value="3" required>
                    <small>Número de posiciones para cifrar</small>
                </div>
                
                <div>
                    <label for="asunto">Asunto:</label>
                    <input type="text" id="asunto" name="asunto" 
                           maxlength="100" required>
                </div>
                
                <div>
                    <label for="mensaje">Mensaje:</label>
                    <textarea id="mensaje" name="mensaje" 
                              rows="5" cols="50" required></textarea>
                </div>
                
                <div>
                    <button type="submit">Redactar y Encriptar</button>
                </div>
            </form>
        </section>
    </main>
    
    <footer>
        <hr>
        <p>Sistema de Mensajería con Cifrado César</p>
        <p>Universidad Nacional de la Patagonia San Juan Bosco</p>
    </footer>
</body>
</html>