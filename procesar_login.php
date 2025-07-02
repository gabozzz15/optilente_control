<?php
    
    session_start();
    include "./inc/conexionbd.php";
    $con = connection();

    function validar($data){
        $data=trim($data);
        $data=stripcslashes($data);
        $data=htmlspecialchars($data);
            
        return $data;
    }

    //Validacion de los datos
    $usuario=validar($_POST['usuario']);
    $clave=validar($_POST['clave']);

    //Si los campos estan vacios
    if(empty($usuario)){

        header("location:login.php?error=El usuario es requerido");
        exit();
    }
    elseif(empty($clave)){
        header("location:login.php?error=La clave es requerida");
        exit();
    }
    else{
        $sql= "SELECT * FROM empleados WHERE usuario = '$usuario'";
        $query = mysqli_query($con,$sql);

        if($query->num_rows == 1){
            
            $usuarioQ= $query->fetch_assoc();

            $id= $usuarioQ['id_empleado'];
            $loginUsuario= $usuarioQ['usuario'];
            $loginclave= $usuarioQ['clave'];

            if($usuario===$loginUsuario){
                if($clave==$loginclave){

                    $_SESSION['id_empleado']=$id;
                    $_SESSION['usuario']=$loginUsuario;
                    $_SESSION['rol']=$usuarioQ['cargo']; // Agregar el rol del empleado a la sesión

                    echo "<script>
                        location.href = 'home.php'
                    </script>";
                }
                else{
                    header('location:login.php?error=Usuario o Clave incorrecta');
                }
            }
            else{
                header('location:login.php?error=Usuario o Clave incorrecta');
            }
        }
        else{
            header('location:login.php?error=Usuario o Clave incorrecta');
        }
    }
?>