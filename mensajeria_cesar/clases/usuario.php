<?php 

require_once __DIR__ . '/../config/database.php';

class Usuario{
    private $conexion;

function __construct($conexion){
    $this->conexion=$conexion;
}

function registrar($nombre, $apellido, $email, $nombre_usuario, $password) {
        // Validaciones básicas
        if (empty($nombre) || empty($apellido) || empty($email) || 
            empty($nombre_usuario) || empty($password)) {
            return ['success' => false, 'message' => 'Todos los campos son obligatorios'];//retorno arreglo asociativo 
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'El email no tiene un formato válido'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'];
        }
        
        if (strlen($nombre_usuario) < 4) {
            return ['success' => false, 'message' => 'El nombre de usuario debe tener al menos 4 caracteres'];
        }
        
        // Verificar si usuario o email ya existen
        if ($this->existe_usuario($nombre_usuario, $email)) { //false si no devuelve filas
            return ['success' => false, 'message' => 'El nombre de usuario o email ya están registrados'];
        }
        
        // Hash de la contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar nuevo usuario
        $sql = "INSERT INTO usuarios (nombre, apellido, nombre_usuario, correo, contraseña) 
                VALUES (?, ?, ?, ?, ?)";
        
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("sssss", $nombre, $apellido, $nombre_usuario, $email, $hashed_password);
            
            if ($stmt->execute()) {
                return [
                    'success' => true, 
                    'message' => 'Registro exitoso. Ahora podes <a href="login.php">iniciar sesión</a>',
                    'id' => $stmt->insert_id //propiedad de mysqli que devuelve el id autoincremental que produjo la bd
                ];
            } else {
                return ['success' => false, 'message' => 'Error al registrar el usuario: ' . $stmt->error];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error en la base de datos'];
        }
    }

    function login($nombre_usuario, $password) {
        if (empty($nombre_usuario) || empty($password)) {
            return ['success' => false, 'message' => 'Usuario y contraseña son obligatorios'];
        }
        
        $sql = "SELECT id_usuario, nombre, apellido, nombre_usuario, correo, contraseña, ultimo_acceso 
                FROM usuarios 
                WHERE nombre_usuario = ?";
        
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("s", $nombre_usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $usuario = $result->fetch_assoc();
                
                if (password_verify($password, $usuario['contraseña'])) {
                    // Actualizar último acceso
                    $this->actualizar_ultimo_acceso($usuario['id_usuario']);
                    
                    return [
                        'success' => true,
                        'usuario' => [
                            'id' => $usuario['id_usuario'],
                            'nombre' => $usuario['nombre'],
                            'apellido' => $usuario['apellido'],
                            'nombre_usuario' => $usuario['nombre_usuario'],
                            'email' => $usuario['correo'],
                            'ultimo_acceso' => $usuario['ultimo_acceso']
                        ]
                    ];
                } else {
                    return ['success' => false, 'message' => 'Contraseña incorrecta'];
                }
            } else {
                return ['success' => false, 'message' => 'Usuario no encontrado'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error en la base de datos'];
        }
    }
    
    private function existe_usuario($nombre_usuario, $email) {
        $sql = "SELECT id_usuario FROM usuarios WHERE nombre_usuario = ? OR correo = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ss", $nombre_usuario, $email);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    private function actualizar_ultimo_acceso($usuario_id) {
        $sql = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        return $stmt->execute();
    }

    function obtener_todos_excepto($usuario_id) { //para listar los usuarios a los cuales se les puede enviar mensajes
        $sql = "SELECT id_usuario, nombre_usuario, nombre, apellido 
                FROM usuarios 
                WHERE id_usuario != ? 
                ORDER BY nombre_usuario";
        
        $usuarios = [];
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($fila = $result->fetch_assoc()) {
            $usuarios[] = $fila;
        }
        
        return $usuarios;
    }

    function cerrar_conexion() {
        if ($this->conexion) {
            $this->conexion->close();
        }
    }


}

?>