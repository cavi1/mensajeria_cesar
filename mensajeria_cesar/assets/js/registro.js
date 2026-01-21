// Validación en cliente
function validar_mail_password() {
    var password = document.getElementById("password").value;
    var confirm_password = document.getElementById("confirm_password").value
    
    if (password != confirm_password) {
        alert("Las contraseñas no coinciden");
        return false;
    }
    return true; //si las contraseñas no coinciden, no se envía el formulario, por lo tanto evito la validacion en el servidor y disminuyo la carga en el mismo.
}



