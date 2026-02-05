// respuesta.js

// Variables para almacenar datos del mensaje
var datos_mensaje_actual = {
    remitente: "",
    asunto: "",
    id_remitente: 0,
    desplazamiento: 3
};


function mostrar_formulario_respuesta() {
    var remitente = document.getElementById("seccion-receptor-mensaje").textContent;
    var asunto = document.getElementById("seccion-asunto").textContent;
    var id_remitente = document.getElementById("seccion-id-receptor-mensaje-oculta").textContent;
    
    
    document.getElementById("respuesta-remitente").textContent = remitente;
    document.getElementById("respuesta-asunto-original-vista").textContent = asunto;
    document.getElementById("respuesta-asunto-original").value = asunto;
    document.getElementById("id-original-remitente").value = id_remitente;

    document.getElementById("vista-mensaje").style.display = "none";
    document.getElementById("formulario-respuesta").style.display = "block";
    

}

// Función para volver a la vista del mensaje
function volver_a_vista_mensaje() {
    // Ocultar formulario, mostrar vista del mensaje
    document.getElementById("formulario-respuesta").style.display = "none";
    document.getElementById("vista-mensaje").style.display = "block";
    
    // Limpiar textarea
    document.getElementById("texto-respuesta").value = "";
}

// Función para cerrar el modal completamente
function cerrar_modal() {
    document.getElementById("modal-mensaje").style.display = "none";
    document.getElementById("modal-mensaje-previsualizado").style.display = "none";
    // Volver a vista inicial por si acaso
    volver_a_vista_mensaje();

     if (window.necesita_recargar) {
        setTimeout(function() {
            location.reload();
        }, 300);
        window.necesita_recargar = false; // Resetear
    }
}

// Función para enviar respuesta (simulada por ahora)
function enviar_respuesta() {
    var respuestaTexto = document.getElementById("texto-respuesta").value.trim();
    
    if (!respuestaTexto) {
        alert("Por favor, escribe un mensaje de respuesta");
        return;
    }
    
    // Aquí irá la lógica AJAX para enviar al servidor
    console.log("Enviando respuesta:");
    console.log("Para:", datos_mensaje_actual.remitente);
    console.log("Asunto: Re:", datos_mensaje_actual.asunto);
    console.log("Mensaje:", respuestaTexto);
    console.log("Desplazamiento:", datos_mensaje_actual.desplazamiento);
    
    // Simular envío exitoso
    alert("Respuesta enviada correctamente (simulación)");
    
    // Cerrar modal después de enviar
    cerrar_modal();
}

// Función para configurar datos cuando se carga un mensaje (para usar con AJAX después)
function configurar_datos_mensaje(remitente, asunto, idRemitente, desplazamiento) {
    datos_mensaje_actual.remitente = remitente;
    datos_mensaje_actual.asunto = asunto;
    datos_mensaje_actual.id_remitente = idRemitente;
    datos_mensaje_actual.desplazamiento = desplazamiento || 3;
}