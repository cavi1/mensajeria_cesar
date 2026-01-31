function ver_mensaje(id_mensaje){
    var parametros= "id_mensaje="+id_mensaje;
    var peticion = new XMLHttpRequest();
    peticion.open("POST","index.php",true);
    peticion.onreadystatechange= muestro_modal_mensaje;
    peticion.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    peticion.send(parametros);
}