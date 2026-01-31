<?php
// includes/Mensaje.php
require_once __DIR__ . '/cifrado_cesar.php';

class Mensaje {
    private $conn;
    private $cifrador;
    
    function __construct($conexion) {
        $this->conn = $conexion;
        $this->cifrador = new Cifrado_cesar();
    }
    
    function enviar($de_usuario_id, $para_usuario_id, $asunto, $mensaje, $desplazamiento) {
        // Cifrar asunto y mensaje
        $asunto_cifrado = $this->cifrador->encriptar($asunto, $desplazamiento);
        $mensaje_cifrado = $this->cifrador->encriptar($mensaje, $desplazamiento);
        
        $sql = "INSERT INTO mensajes (id_remitente, id_destinatario, asunto_encriptado, mensaje_encriptado, desplazamiento) 
                VALUES (?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("iissi", $de_usuario_id, $para_usuario_id, $asunto_cifrado, $mensaje_cifrado, $desplazamiento);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Mensaje enviado correctamente'];
            } else {
                return ['success' => false, 'message' => 'Error al enviar el mensaje'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error en la base de datos'];
        }
    }
    
    function obtener_recibidos($usuario_id, $limite = 10) { //limite de mensajes para probar
        $sql = "SELECT m.id_mensaje, m.asunto_encriptado, m.fecha_envio, 
                       u.nombre_usuario as remitente, m.leido, m.desplazamiento,
                       m.id_remitente
                FROM mensajes m
                JOIN usuarios u ON m.id_remitente = u.id_usuario
                WHERE m.id_destinatario = ?
                ORDER BY m.fecha_envio DESC
                LIMIT ?";
        
        $mensajes = [];
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $usuario_id, $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($fila = $result->fetch_assoc()) {
            // Descifrar asunto para mostrar en lista
            $fila['asunto_descifrado'] = $this->cifrador->desencriptar(
                $fila['asunto_encriptado'],
                $fila['desplazamiento']
            );
            $mensajes[] = $fila;
        }
        
        return $mensajes;
    }
    
    function obtener_enviados($usuario_id, $limite = 10) {
        $sql = "SELECT m.id_mensaje, m.asunto_encriptado, m.fecha_envio,
                       u.nombre_usuario as destinatario, m.desplazamiento,
                       m.id_destinatario
                FROM mensajes m
                JOIN usuarios u ON m.id_destinatario = u.id_usuario
                WHERE m.id_remitente = ?
                ORDER BY m.fecha_envio DESC
                LIMIT ?";
        
        $mensajes = [];
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $usuario_id, $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($fila = $result->fetch_assoc()) {
            // Descifrar asunto para mostrar en lista
            $fila['asunto_descifrado'] = $this->cifrador->desencriptar(
                $fila['asunto_encriptado'],
                $fila['desplazamiento']
            );
            $mensajes[] = $fila;
        }
        
        return $mensajes;
    }
    
    
    function obtener_por_id($mensaje_id, $usuario_id) {
        $sql = "SELECT m.*, 
                       u1.nombre_usuario as remitente_nombre,
                       u2.nombre_usuario as destinatario_nombre
                FROM mensajes m
                LEFT JOIN usuarios u1 ON m.id_remitente = u1.id_usuario
                LEFT JOIN usuarios u2 ON m.id_destinatario = u2.id_usuario
                WHERE m.id_mensaje = ? 
                AND (m.id_remitente = ? OR m.id_destinatario = ?)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $mensaje_id, $usuario_id, $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $mensaje = $result->fetch_assoc();
            
            // Descifrar asunto y mensaje
            $mensaje['asunto_descifrado'] = $this->cifrador->desencriptar(
                $mensaje['asunto_encriptado'],
                $mensaje['desplazamiento']
            );
            
            $mensaje['mensaje_descifrado'] = $this->cifrador->desencriptar(
                $mensaje['mensaje_encriptado'],
                $mensaje['desplazamiento']
            );
            
            return $mensaje;
        }
        
        return null;
    }
    
    
    function marcar_como_leido($mensaje_id, $usuario_id) {
        $sql = "UPDATE mensajes SET leido = 1, fecha_leido = NOW() 
                WHERE id_mensaje = ? AND id_destinatario = ? AND leido = 0";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $mensaje_id, $usuario_id);
        return $stmt->execute();
    }
    
    
    function contar_nuevos_desde($usuario_id, $fecha_desde) {
        $sql = "SELECT COUNT(*) as total 
                FROM mensajes 
                WHERE id_destinatario = ? 
                AND fecha_envio > ? 
                AND leido = 0";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("is", $usuario_id, $fecha_desde);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        return $row['total'] ?? 0;
    }
    
   
    function obtener_ids_no_leidos($usuario_id) {
        $sql = "SELECT id_mensaje 
                FROM mensajes 
                WHERE id_destinatario = ? 
                AND leido = 0";
        
        $ids = [];
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($fila = $result->fetch_assoc()) {
            $ids[] = $fila['id_mensaje'];
        }
        
        return $ids;
    }
}
?>