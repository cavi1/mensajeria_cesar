function ver_mensaje(id_mensaje){
    var parametros= "id_mensaje="+id_mensaje;
    var peticion = new XMLHttpRequest();
    peticion.open("POST","config/mensaje_handler.php",true);
    peticion.onreadystatechange= muestro_modal_mensaje;
    peticion.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    peticion.send(parametros);


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
            var id_remitente_oculto = document.getElementById("seccion-id-receptor-mensaje-oculta");//debería ocultarla 
            id_remitente_oculto.innerHTML=myObj.id_remitente;
            document.getElementById("modal-mensaje").style.display = "block";
            if (myObj.puede_responder) {
                // Mostrar botón de responder
                document.getElementById("btn-responder-modal").style.display = "inline-block";
                
                // Configurar datos para el formulario
                configurarDatosParaRespuesta(myObj);
            } else {
                // Ocultar botón de responder
                document.getElementById("btn-responder-modal").style.display = "none";
            }
        }
    }
}
