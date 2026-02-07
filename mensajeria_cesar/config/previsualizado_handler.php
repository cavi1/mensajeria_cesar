<?php

session_start();
require_once '../clases/mensaje.php';

if (isset($_POST['asunto_mensaje'])&& isset($_POST['texto_mensaje'])&& isset($_POST['desplazamiento'])) {
    
    $cifrado_cesar_instancia = new Cifrado_cesar();
    $asunto_encriptado = $cifrado_cesar_instancia->encriptar($_POST['asunto_mensaje'], $_POST['desplazamiento']);
    $mensaje_encriptado = $cifrado_cesar_instancia->encriptar($_POST['texto_mensaje'], $_POST['desplazamiento']);

    $respuesta = [
        'asunto_encriptado_prev'=>$asunto_encriptado,
        'mensaje_encriptado_prev'=>$mensaje_encriptado
    ];
    echo json_encode($respuesta);
}