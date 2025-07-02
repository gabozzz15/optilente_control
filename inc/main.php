<?php

function connection(){

    $host = "localhost";
    $user = "root";
    $pass = "";

    $db = "sistemaoptilente";

    $connect=mysqli_connect($host, $user, $pass);

    mysqli_select_db($connect, $db);

    // Verificar conexión
    if ($connect->connect_error) {
        die("Conexión fallida: " . $connect->connect_error);
    }   

    return $connect;

}

# Funcion para verificar datos #
function verificar_datos($filtro,$cadena){
    if(preg_match("/^".$filtro."$/",$cadena)){
        return false;
    }else{
        return true;
    }
}

# Limpiar cadenas de texto #
function limpiar_cadena($cadena){
    $cadena=trim($cadena);
    $cadena=stripslashes($cadena);
    $cadena=str_ireplace("<script>", "", $cadena);
    $cadena=str_ireplace("</script>", "", $cadena);
    $cadena=str_ireplace("<script src", "", $cadena);
    $cadena=str_ireplace("<script type=", "", $cadena);
    $cadena=str_ireplace("SELECT * FROM", "", $cadena);
    $cadena=str_ireplace("DELETE FROM", "", $cadena);
    $cadena=str_ireplace("INSERT INTO", "", $cadena);
    $cadena=str_ireplace("DROP TABLE", "", $cadena);
    $cadena=str_ireplace("DROP DATABASE", "", $cadena);
    $cadena=str_ireplace("TRUNCATE TABLE", "", $cadena);
    $cadena=str_ireplace("SHOW TABLES;", "", $cadena);
    $cadena=str_ireplace("SHOW DATABASES;", "", $cadena);
    $cadena=str_ireplace("<?php", "", $cadena);
    $cadena=str_ireplace("?>", "", $cadena);
    $cadena=str_ireplace("--", "", $cadena);
    $cadena=str_ireplace("^", "", $cadena);
    $cadena=str_ireplace("<", "", $cadena);
    $cadena=str_ireplace("[", "", $cadena);
    $cadena=str_ireplace("]", "", $cadena);
    $cadena=str_ireplace("==", "", $cadena);
    $cadena=str_ireplace(";", "", $cadena);
    $cadena=str_ireplace("::", "", $cadena);
    $cadena=trim($cadena);
    $cadena=stripslashes($cadena);
    return $cadena;
}

    // Funcion para paginador de tabla //
    function paginador_tablas($pagina, $Npaginas, $url, $botones){
        $tabla='<nav class="pagination is-centered is-rounded" role="navigation" aria-label="pagination">';

        if($pagina<=1){ // sentencia if..else para deshabilitar y habilitar boton anterior //
            $tabla.='
                <a class="pagination-previous is-disabled" disabled >Anterior</a>
                <ul class="pagination-list">
            ';
            
        }else{
            $tabla.='
            <a class="pagination-previous" href="'.$url.($pagina-1).'">Anterior</a>
            <ul class="pagination-list">
                <li><a class="pagination-link" href="'.$url.'1">1</a></li>
                <li><span class="pagination-ellipsis">&hellip;</span></li>
            ';
        }

        $ci=0;
        for($i=$pagina; $i<=$Npaginas; $i++){

            if($ci>=$botones){
                break;
            }

            if($pagina==$i){
                $tabla.='<li><a class="pagination-link is-current" href="'.$url.$i.'">'.$i.'</a></li>';
            }else{
                $tabla.='<li><a class="pagination-link" href="'.$url.$i.'">'.$i.'</a></li>';
            }

            $ci++;
        }

        if($pagina==$Npaginas){ // sentencia if..else para deshabilitar y habilitar boton anterior //
            $tabla.='
            </ul>
	        <a class="pagination-next is-disabled" disabled >Siguiente</a>
            ';
            
        }else{
            $tabla.='
                <li><span class="pagination-ellipsis">&hellip;</span></li>
                <li><a class="pagination-link" href="'.$url.$Npaginas.'">'.$Npaginas.'</a></li>
            </ul>
            <a class="pagination-next" href="'.$url.($pagina+1).'">Siguiente</a>
            ';
        }

        $tabla.='</nav>';
        return $tabla;
    }

?>
