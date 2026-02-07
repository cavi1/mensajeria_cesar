<?php
// index.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/clases/usuario.php';
require_once __DIR__ . '/clases/mensaje.php';
require_once __DIR__ . '/config/sesion.php';

// Funcion del archivo sesion
requiere_autenticacion();

// Obtener datos del usuario actual desde sesión
$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre_usuario'];
$nombre_completo = $_SESSION['nombre_completo'] ?? 'Usuario';
$mensajes_nuevos = $_SESSION['mensajes_nuevos'] ?? 0;

// Instanciar las clases
$usuario_instancia = new Usuario($conexion);
$mensaje_instancia = new Mensaje($conexion);

// Obtener datos usando las clases
$mensajes_recibidos = $mensaje_instancia->obtener_recibidos($usuario_id, 10);
$mensajes_enviados = $mensaje_instancia->obtener_enviados($usuario_id, 10);
$usuarios = $usuario_instancia->obtener_todos_excepto($usuario_id);
$_SESSION['ids_mensajes_no_leidos'] = $mensaje_instancia->obtener_ids_no_leidos($usuario_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mensajería César</title>
    <link rel="stylesheet" href="assets/css/estilos_nuevos.css">
</head>
<script src="globals.js"></script>
<script src="ver_mensaje_ajax.js"></script>
<script src="assets/js/respuesta.js"></script>
<body class="pagina-dashboard">
    <div>
        <header>
            <h1>Mensajería César - Panel Principal</h1>
            <div>
                <p>Bienvenido, <strong><?php echo htmlspecialchars($nombre_completo); ?></strong></p>
                <p>Usuario: <?php echo htmlspecialchars($nombre_usuario); ?></p>
                <p>Mensajes nuevos desde ultima sesión: <strong><?php echo $mensajes_nuevos; ?></strong></p>
                <p>Mensajes no leídos: <strong><?php echo count($_SESSION['ids_mensajes_no_leidos']); ?></strong></p>
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
                                <tr style="<?php echo $mensaje['leido'] ? '' : 'font-weight: bold;';?>" data-mensaje-id="<?php echo $mensaje['id_mensaje']; ?>">
                                    <td><?php echo htmlspecialchars($mensaje['remitente']); ?></td>
                                    <td>
                                        <?php
                                            echo htmlspecialchars($mensaje['asunto_descifrado']);
                                        ?>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($mensaje['fecha_envio'])); ?></td>
                                    <td><?php echo $mensaje['leido'] ? 'Leído' : 'Sin leer'; ?></td>
                                    <td>
                                        <button id="boton-ver-detalle-mensaje" class="button-ver-marcar" onclick="ver_mensaje(<?php echo $mensaje['id_mensaje'];?>);">
                                            Ver mensaje
                                        </button>
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
                                        <button id="boton-ver-detalle-mensaje" class="button-ver-marcar" onclick="ver_mensaje(<?php echo $mensaje['id_mensaje'];?>);">
                                            Ver mensaje
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>

                    <!-- Modal para la vista del mensaje -->
            <div id="modal-mensaje" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
                <div style="background:white; margin:50px auto; padding:20px; width:80%; max-width:600px;">
                    
                    <!--Vista del mensaje producto de la llamada ajax-->
                    <div id="vista-mensaje">
                        <h2>Detalle del Mensaje</h2>
                        
                        <p><strong>Asunto:</strong> <span id="seccion-asunto"></span></p>
                        <p><strong>Remitente:</strong> <span id="seccion-receptor-mensaje"></span></p>
                        <p><strong>Fecha:</strong> <span id="seccion-fecha-recepcion"></span></p>
                        <p><strong>Mensaje:</strong></p>
                        <div id="seccion-cuerpo-mensaje" style="padding:10px; background:#f5f5f5; border:1px solid #ddd; min-height:100px;"></div>
                        
                        <!--Botones para responder o cerrar detalle mensaje-->
                        <div style="margin-top:20px; text-align:right;">
                            <button id="btn-responder-modal" onclick="mostrar_formulario_respuesta()" style="display:none;">
                                Responder
                            </button>
                            <button onclick="cerrar_modal()">
                                Cerrar
                            </button>
                        </div>
                    </div>
                    
                     <!--Seccion para responder mensajes-->
                    <div id="formulario-respuesta" style="display:none;">
                        <form method="POST" action="pages/enviar.php">
                            <h2>Responder Mensaje</h2>                           
                            <div>
                                <p><strong>Respondiendo a:</strong> <span id="respuesta-remitente"></span></p>
                                <span id="seccion-id-receptor-mensaje-oculta" style="display:none;"></span></p>
                                <p><strong>Asunto original:</strong> <span id="respuesta-asunto-original-vista"></span></p>
                            </div>
                            
                            <input type="hidden" name="destinatario" id="id-original-remitente">
                            <input type="hidden" name="asunto" id="respuesta-asunto-original">     

                            <div style="margin-bottom:15px;">
                                <label style="display:block; margin-bottom:5px; font-weight:bold;">Tu respuesta:</label>
                                <textarea id="texto-respuesta" name="mensaje" rows="6" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;"></textarea>
                            </div>
                            <div>
                                <label for="desplazamiento">Desplazamiento (1-26):</label>
                                <input type="number" id="desplazamiento-mensaje-respuesta" name="desplazamiento" 
                                    min="1" max="26" required>
                            </div>
                            <div style="margin-top:20px; text-align:right;">
                                <button type="submit" style="background:#4CAF50; color:white; padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">
                                    Enviar Respuesta
                                </button>
                                <button type="button" onclick="previsualizar_msj_respuesta()" style="background:#4CAF50; color:white; padding:8px 16px; border:none; border-radius:4px; cursor:pointer;" >
                                        Previsualizar
                                </button>
                                <button type="button" onclick="volver_a_vista_mensaje()" style="background:#757575; color:white; padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">
                                    Cancelar
                                </button>
                            </div>
                                <div id="modal-mensaje-previsualizado-rta" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
                                    <div style="background:white; margin:50px auto; padding:20px; width:80%; max-width:600px;">
                                    
                                    <div id="vista-mensaje-previsualizado-rta">
                                        <p><strong>Asunto encriptado:</strong> <span id="seccion-asunto-a-enviar-encriptado-rta"></span></p>
                                        <p><strong>Mensaje encriptado:</strong></p>
                                        <div id="seccion-cuerpo-mensaje-a-enviar-encriptado-rta" style="padding:10px; background:#f5f5f5; border:1px solid #ddd; min-height:100px;"></div>
                                        
                                        <div style="margin-top:20px; text-align:right;">
                                            <button type="button" onclick="cerrar_modal_visualizado_rta()">
                                                volver
                                            </button>
                                        </div>
                                    </div>   
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!--Seccion para enviar un nuevo mensaje desde cero-->
            <section>    
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
                        <input type="number" id="desplazamiento-mensaje-index" name="desplazamiento" 
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
                        <button type="button" onclick="vista_previa_mensaje_y_encriptado()">Enviar Mensaje</button>
                    </div>

                    <!--Seccion de previsualizado de mensajes-->

                    <div id="modal-mensaje-previsualizado" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
                        <div style="background:white; margin:50px auto; padding:20px; width:80%; max-width:600px;">
                            
                            <div id="vista-mensaje-previsualizado">
                                <h2>Confirmar envio del mensaje</h2>
                                <p><strong>Asunto:</strong> <span id="seccion-asunto-a-enviar"></span></p>
                                <p><strong>Mensaje:</strong></p>
                                <div id="seccion-cuerpo-mensaje-a-enviar" style="padding:10px; background:#f5f5f5; border:1px solid #ddd; min-height:100px;"></div>
                                <p><strong>Asunto encriptado:</strong> <span id="seccion-asunto-a-enviar-encriptado"></span></p>
                                <p><strong>Mensaje encriptado:</strong></p>
                                <div id="seccion-cuerpo-mensaje-a-enviar-encriptado" style="padding:10px; background:#f5f5f5; border:1px solid #ddd; min-height:100px;"></div>
                                
                                <div style="margin-top:20px; text-align:right;">
                                    <button type="submit" id="btn-confirmar-envio-mensaje">
                                        Confirmar
                                    </button>
                                    <button type="button" onclick="cerrar_modal()">
                                        Cerrar
                                    </button>
                                </div>
                            </div>   
                        </div>
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