


function validar_contraseñas_formulario() {  //si las contraseñas no coinciden, no se envía el formulario, por lo tanto evito la validacion en el servidor y disminuyo la carga en el mismo.
    // 1. Validar contraseñas
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        alert('❌ Las contraseñas no coinciden');
        document.getElementById('confirm_password').focus();
        document.getElementById('confirm_password').style.borderColor = 'red';
        return false; // ← DETIENE el envío del formulario
    }
    return true;
}