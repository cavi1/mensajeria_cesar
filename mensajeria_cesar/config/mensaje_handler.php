<?php
// mensaje_handler.php
session_start();
require_once 'database.php';
require_once '../clases/mensaje.php';

if (isset($_POST['id_mensaje'])) {
    $mensaje_id = intval($_POST['id_mensaje']);
    $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 0;
    
    // DEBUG: Agrega esto para ver qué está pasando
    error_log("DEBUG Handler: ID mensaje: $mensaje_id, Usuario: $usuario_id");
    
    $mensaje_instancia = new Mensaje($conexion);
    $mensaje = $mensaje_instancia->obtener_por_id($mensaje_id, $usuario_id);
    
    if ($mensaje) {
        // DEBUG
        error_log("DEBUG Handler: Mensaje encontrado: " . print_r($mensaje, true));
        
        $es_remitente = ($mensaje['id_remitente'] == $usuario_id);
        $es_destinatario = ($mensaje['id_destinatario'] == $usuario_id);
        
        // Preparar respuesta SIN campos duplicados
        $respuesta = [
            'error' => false,
            'asunto' => $mensaje['asunto_descifrado'] ?? 'Sin asunto',
            'descripcion_mensaje' => $mensaje['mensaje_descifrado'] ?? 'Sin contenido',
            'fecha_recepcion_mensaje' => isset($mensaje['fecha_envio']) ? 
                date('d/m/Y H:i', strtotime($mensaje['fecha_envio'])) : 'Sin fecha',
            'nombre_remitente' => $mensaje['remitente_nombre'] ?? 'Sin remitente',
            'id_remitente' => $mensaje['id_remitente'] ?? 'Sin remitente',
            'es_remitente' => $es_remitente,  // NUEVO: indica si el usuario ES el remitente
            'es_destinatario' => $es_destinatario,// NUEVO: indica si el usuario ES el destinatario
            'puede_responder' => $es_destinatario
        ];
        
        echo json_encode($respuesta);
        
    } else {
        // SOLO UN campo error
        echo json_encode([
            'error' => true,
            'mensaje' => 'Mensaje no encontrado o no tienes permisos'
        ]);
    }
} else {
    // SOLO UN campo error  
    echo json_encode([
        'error' => true,
        'mensaje' => 'No se recibió ID de mensaje'
    ]);
}
?>