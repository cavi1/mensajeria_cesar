<?php
// index.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/clases/usuario.php';
require_once __DIR__ . '/clases/mensaje.php';
require_once __DIR__ . '/config/sesion.php';

// Verificar que el usuario esté logueado
if (!usuario_esta_autenticado()) {
    header("Location: pages/login.php");
    exit();
}

// Obtener datos del usuario actual desde sesión
$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre_usuario'];
$nombre_completo = $_SESSION['nombre_completo'] ?? 'Usuario';
$mensajes_nuevos = $_SESSION['mensajes_nuevos'] ?? 0;

// Instanciar las clases
$usuarioModel = new Usuario($conexion);
$mensajeModel = new Mensaje($conexion);

// Obtener datos usando las clases
$mensajes_recibidos = $mensajeModel->obtener_recibidos($usuario_id, 10);
$mensajes_enviados = $mensajeModel->obtener_enviados($usuario_id, 10);
$usuarios = $usuarioModel->obtener_todos_excepto($usuario_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mensajería César</title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<script src="assets/js/ver_mensaje_ajax.js"></script>
<body class="pagina-dashboard">
    <div>
        <header>
            <h1>Mensajería César - Panel Principal</h1>
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
                <h2>Mensajes Recibidos</h2>
                
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
                                    <td>
                                        <?php 
                                        // Mostrar asunto descifrado si está disponible
                                        if (isset($mensaje['asunto_descifrado'])) {
                                            echo htmlspecialchars($mensaje['asunto_descifrado']);
                                        } else {
                                            echo htmlspecialchars($mensaje['asunto_encriptado']);
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_envio'])); ?></td>
                                    <td><?php echo $mensaje['leido'] ? 'Leído' : 'Nuevo'; ?></td>
                                    <td>
                                        <button id="boton-ver-detalle-mensaje" class="button-ver-marcar" onclick="ver_mensaje(<?php echo $mensaje['id_mensaje'];?>);">
                                            Ver
                                        </button>
                                        <?php if (!$mensaje['leido']): ?>
                                            <button class="button-ver-marcar">
                                            Marcar como leído
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
            
            <!-- MENSAJES ENVIADOS -->
            <section>
                <h2>Mensajes Enviados</h2>
                
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
                                    <td>
                                        <?php 
                                        if (isset($mensaje['asunto_descifrado'])) {
                                            echo htmlspecialchars($mensaje['asunto_descifrado']);
                                        } else {
                                            echo htmlspecialchars($mensaje['asunto_encriptado']);
                                        }
                                        ?>
                                    </td>
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
                <?php endif; ?>
            </section>
            
            <!-- ENVIAR NUEVO MENSAJE -->
            <section>
                <h2>Enviar Nuevo Mensaje</h2>
                
                <form method="POST" action="enviar.php">
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
                            min="1" max="26" value="4" required>
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
                        <button type="submit">Enviar Mensaje</button>
                    </div>
                </form>
            </section>
        </main>
        
        <footer>
            <hr>
            <p>Sistema de Mensajería con Cifrado César</p>
        </footer>
    </div>
</body>
</html>