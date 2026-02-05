function ver_mensaje(id_mensaje){
    var parametros= "id_mensaje="+id_mensaje;
    var peticion = new XMLHttpRequest();
    peticion.open("POST","config/mensaje_handler.php",true);
    peticion.onreadystatechange= muestro_modal_mensaje;
    peticion.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    peticion.send(parametros);


    //modal del detalle del mensaje producto de llamada ajax
    function muestro_modal_mensaje() {
        if ((peticion.readyState == 4) && (peticion.status==200)){
            var myObj = JSON.parse(peticion.responseText);
            var asunto_mensaje = document.getElementById("seccion-asunto");
            asunto_mensaje.innerHTML=myObj.asunto;
            var cuerpo_mensaje = document.getElementById("seccion-cuerpo-mensaje");
            cuerpo_mensaje.innerHTML=myObj.descripcion_mensaje;
            var fecha_recepcion = document.getElementById("seccion-fecha-recepcion");
            fecha_recepcion.innerHTML=myObj.fecha_recepcion_mensaje;
            var remitente = document.getElementById("seccion-receptor-mensaje");
            remitente.innerHTML=myObj.nombre_remitente;
            var id_remitente_oculto = document.getElementById("seccion-id-receptor-mensaje-oculta");
            id_remitente_oculto.innerHTML=myObj.id_remitente;
            document.getElementById("modal-mensaje").style.display = "block";

            //boton de responder que aparece solo si se esta viendo el detalle de un mensaje recibido
            if (myObj.puede_responder) {
                document.getElementById("btn-responder-modal").style.display = "inline-block";
            } else {
                document.getElementById("btn-responder-modal").style.display = "none";
            }

            //recarga de la página forzada solo si se ve el detalle de un mensaje que no ha sido leído
            //esto es para que se quite el resaltado y cambie el estado a leído
            if (myObj.es_destinatario && myObj.fue_marcado_leido) {
                window.necesita_recargar = true; 
                console.log("Se marcó para recargar: mensaje leído");
            }
            
        }
    }
}

function probar_modal(){
    var asunto = document.getElementById("asunto").value;
    var mensaje = document.getElementById("mensaje").value;
    var desplazamiento = document.getElementById("desplazamiento-mensaje-index").value;
    var destinatario = document.getElementById("destinatario").value;
    var asunto_ver = document.getElementById("seccion-asunto-a-enviar");
    var desp_ver = document.getElementById("desplazamiento-a-enviar");

    if(!asunto || !mensaje || (!desplazamiento || desplazamiento < 1 || desplazamiento > 26) || !destinatario){
         alert("Por favor, completa todos los campos correctamente:\n" +
              "- Destinatario: Selecciona un usuario\n" +
              "- Desplazamiento: Entre 1 y 26\n" +
              "- Asunto: No puede estar vacío\n" +
              "- Mensaje: No puede estar vacío");
    }
    else{
        asunto_ver.innerHTML=asunto;
        desp_ver.innerHTML=desplazamiento;
        document.getElementById("modal-mensaje-previsualizado").style.display = "block";
    }
}


//modal de la vista previa del mensaje que se va a enviar cuya previsualización encriptada se obtiene de una llamada ajax
function vista_previa_mensaje_y_encriptado(){
    var asunto_mensaje = document.getElementById("asunto").value;
    var texto_mensaje = document.getElementById("mensaje").value;
    var desplazamiento = document.getElementById("desplazamiento-mensaje-index").value;
    var destinatario = document.getElementById("destinatario").value;
    if(!asunto_mensaje || !texto_mensaje || (!desplazamiento || desplazamiento < 1 || desplazamiento > 26) || !destinatario){
            alert("Por favor, completa todos los campos correctamente:\n" +
              "- Destinatario: Selecciona un usuario\n" +
              "- Desplazamiento: Entre 1 y 26\n" +
              "- Asunto: No puede estar vacío\n" +
              "- Mensaje: No puede estar vacío");
    }
    else{
    var parametros= "asunto_mensaje="+asunto_mensaje+"&texto_mensaje="+texto_mensaje+"&desplazamiento="+desplazamiento;
    var peticion = new XMLHttpRequest();
    peticion.open("POST","config/previsualizado_handler.php",true);
    peticion.onreadystatechange= muestro_modal_previsualizado_mensaje;
    peticion.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    peticion.send(parametros);
    }

    function muestro_modal_previsualizado_mensaje(){
        var asunto_ver = document.getElementById("seccion-asunto-a-enviar");
        asunto_ver.innerHTML=asunto_mensaje;
        var mensaje_ver = document.getElementById("seccion-cuerpo-mensaje-a-enviar");
        mensaje_ver.innerHTML=texto_mensaje;
            if ((peticion.readyState == 4) && (peticion.status==200)){
                var myObj = JSON.parse(peticion.responseText);
                var asunto_encriptado = document.getElementById("seccion-asunto-a-enviar-encriptado")
                asunto_encriptado.innerHTML=myObj.asunto_encriptado_prev;
                var mensaje_encriptado = document.getElementById("seccion-cuerpo-mensaje-a-enviar-encriptado");
                mensaje_encriptado.innerHTML=myObj.mensaje_encriptado_prev;
            }
        document.getElementById("modal-mensaje-previsualizado").style.display = "block";
    }
        
}




