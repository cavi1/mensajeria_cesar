<?php
session_start();

// Verificar si el usuario está logueado
function usuario_esta_autenticado() {
    return isset($_SESSION['usuario_id']);
}

// Redirigir a login si no está autenticado
function requiere_autenticacion() {
    if (!usuario_esta_autenticado()) {
        header("Location: ../pages/login.php");
        exit();
    }
}

// Obtener datos del usuario de la sesión
function obtener_usuario_sesion() {
    if (isset($_SESSION['usuario'])) {
        return $_SESSION['usuario'];
    }
    return null;
}

// Establecer datos de usuario en sesión
function establecer_usuario_sesion($usuario) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
    $_SESSION['nombre_completo'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
    $_SESSION['email'] = $usuario['email'];
    $_SESSION['usuario'] = $usuario;
}
?>