<?php

class Cifrado_cesar{
    private $alfabeto_mayus;
    private $alfabeto_minus;
    private $numeros;
    private $long_str_alfabeto_mayus;
    private $long_str_alfabeto_minus;
    private $long_str_numeros;
    

function __construct(){
    $this->alfabeto_mayus="ABCDEFGHIJKLMNÑOPQRSTUVWXYZ";
    $this->alfabeto_minus="abcdefghijklmnñopqrstuvwxyz";
    $this->long_str_alfabeto_minus=strlen($this->alfabeto_minus);
    $this->long_str_alfabeto_mayus=strlen($this->alfabeto_mayus);
    $this->numeros="0123456789";
    $this->long_str_numeros=strlen($this->numeros);
}

function encriptar($texto, $desplazamiento) {
    $resultado = "";
    $desplazamiento = (int)$desplazamiento;
    $longitud = mb_strlen($texto, 'UTF-8');
    
    for ($i = 0; $i < $longitud; $i++) {
        $caracter = mb_substr($texto, $i, 1, 'UTF-8');
        $encontrado = false;
        
        // 1. Números
        $pos = strpos($this->numeros, $caracter);
        if ($pos !== false) {
            $nueva_pos = ($pos + $desplazamiento) % $this->long_str_numeros;
            // AJUSTE PARA NEGATIVOS
            if ($nueva_pos < 0) {
                $nueva_pos += $this->long_str_numeros;
            }
            $resultado .= $this->numeros[$nueva_pos];
            $encontrado = true;
        }
        
        // 2. Mayúsculas
        if (!$encontrado) {
            $pos = mb_strpos($this->alfabeto_mayus, $caracter, 0, 'UTF-8');
            if ($pos !== false) {
                $nueva_pos = ($pos + $desplazamiento) % $this->long_str_alfabeto_mayus;
                // AJUSTE PARA NEGATIVOS
                if ($nueva_pos < 0) {
                    $nueva_pos += $this->long_str_alfabeto_mayus;
                }
                $resultado .= mb_substr($this->alfabeto_mayus, $nueva_pos, 1, 'UTF-8');
                $encontrado = true;
            }
        }
        
        // 3. Minúsculas
        if (!$encontrado) {
            $pos = mb_strpos($this->alfabeto_minus, $caracter, 0, 'UTF-8');
            if ($pos !== false) {
                $nueva_pos = ($pos + $desplazamiento) % $this->long_str_alfabeto_minus;
                // AJUSTE PARA NEGATIVOS
                if ($nueva_pos < 0) {
                    $nueva_pos += $this->long_str_alfabeto_minus;
                }
                $resultado .= mb_substr($this->alfabeto_minus, $nueva_pos, 1, 'UTF-8');
                $encontrado = true;
            }
        }
        
        // 4. Si no está en ningún alfabeto
        if (!$encontrado) {
            $resultado .= $caracter;
        }
    }
    
    return $resultado;
}

function desencriptar($texto_cifrado, $desplazamiento) {
    // Solo llama a encriptar con el desplazamiento negativo
    return $this->encriptar($texto_cifrado, -$desplazamiento);
}

}


?>




