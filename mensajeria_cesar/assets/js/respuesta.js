
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

function volver_a_vista_mensaje_rta() {
    document.getElementById("vista-mensaje").style.display = "block";
}

// Función para cerrar cualquiera de las 2 modales y efectuar la recarga de la página cuando se debe
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

function cerrar_modal_visualizado_rta(){
    document.getElementById("modal-mensaje-previsualizado-rta").style.display = "none";
}


