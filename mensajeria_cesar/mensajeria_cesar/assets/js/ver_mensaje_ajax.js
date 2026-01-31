function ver_mensaje(id_mensaje){
    var parametros= "id_mensaje="+id_mensaje;
    var peticion = new XMLHttpRequest();
    peticion.open("POST","index.php",true);
    peticion.onreadystatechange= muestro_modal_mensaje;
    peticion.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    peticion.send(parametros);
}

function muestro_modal_mensaje(peticion) {
    console.log("📥 Respuesta recibida:", peticion.responseText);
    
    try {
        // Parsear JSON recibido
        var respuesta = JSON.parse(peticion.responseText);
        
        if (respuesta.success) {
            // Extraer datos del mensaje
            var mensaje = respuesta.data;
            
            // Mostrar modal con los datos
            mostrarModal(mensaje);
        } else {
            // Error del servidor
            alert("Error: " + respuesta.message);
        }
    } catch (error) {
        console.error("❌ Error parseando JSON:", error);
        alert("Error en el formato de respuesta");
    }
}

// Función para crear y mostrar el modal
function mostrarModal(mensaje) {
    console.log("🎨 Creando modal para mensaje:", mensaje);
    
    // Crear elemento modal
    var modal = document.createElement('div');
    modal.className = 'modal-mensaje';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    `;
    
    // Contenido del modal
    modal.innerHTML = `
        <div style="
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 5px 30px rgba(0,0,0,0.3);
        ">
            <div style="
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #3498db;
                padding-bottom: 10px;
            ">
                <h2 style="margin: 0; color: #2c3e50;">${escapeHTML(mensaje.asunto)}</h2>
                <button onclick="cerrarModal()" style="
                    background: none;
                    border: none;
                    font-size: 24px;
                    cursor: pointer;
                    color: #666;
                ">&times;</button>
            </div>
            
            <div style="margin-bottom: 20px;">
                <p><strong>De:</strong> ${escapeHTML(mensaje.remitente)}</p>
                <p><strong>Para:</strong> ${escapeHTML(mensaje.destinatario)}</p>
                <p><strong>Fecha:</strong> ${mensaje.fecha_formateada}</p>
                <p><strong>Desplazamiento:</strong> ${mensaje.desplazamiento}</p>
                <p><strong>Estado:</strong> ${mensaje.leido ? '✅ Leído' : '🆕 Nuevo'}</p>
            </div>
            
            <div style="
                background: #f8f9fa;
                padding: 20px;
                border-radius: 5px;
                border: 1px solid #dee2e6;
                margin-top: 20px;
            ">
                <h3 style="margin-top: 0; color: #495057;">Contenido del mensaje:</h3>
                <div style="
                    white-space: pre-wrap;
                    font-family: monospace;
                    line-height: 1.6;
                    padding: 10px;
                    background: white;
                    border-radius: 3px;
                ">${escapeHTML(mensaje.mensaje)}</div>
            </div>
            
            <div style="margin-top: 25px; text-align: right;">
                <button onclick="cerrarModal()" style="
                    padding: 10px 20px;
                    background: #95a5a6;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    margin-right: 10px;
                ">Cerrar</button>
                
                ${!mensaje.es_mio ? `
                <button onclick="responderMensaje(${mensaje.id_remitente}, '${escapeHTML(mensaje.asunto)}')" style="
                    padding: 10px 20px;
                    background: #3498db;
                    color: white;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                ">✉️ Responder</button>
                ` : ''}
            </div>
        </div>
    `;
    
    // Agregar al documento
    document.body.appendChild(modal);
    document.body.style.overflow = 'hidden'; // Evitar scroll
}

// Función para cerrar modal
function cerrarModal() {
    var modal = document.querySelector('.modal-mensaje');
    if (modal) {
        modal.remove();
        document.body.style.overflow = 'auto';
    }
}

// Función para responder mensaje
function responderMensaje(remitenteId, asunto) {
    cerrarModal();
    window.location.href = `pages/enviar.php?responder=${remitenteId}&asunto=Re: ${encodeURIComponent(asunto)}`;
}

// Función auxiliar para escapar HTML
function escapeHTML(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Cerrar modal con Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        cerrarModal();
    }
});

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-mensaje')) {
        cerrarModal();
    }
});