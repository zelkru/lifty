<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/lifty/Model/InicioModel.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/lifty/Controller/ConfigController.php';

// Botón de Inicio de Sesión
if(isset($_POST['btnInicioSesion'])) {
    $identificacion = $_POST['txtIdentificacion'];
    $contrasenna = $_POST['txtContrasenna'];

    $response = InicioSesionModel($identificacion,$contrasenna);

    if($response) {
        header("Location: ../../View/Inicio/home.php");
        exit();
    }

    $_POST["mensaje"] = "Su información no se ha podido validar correctamente";
}


if(isset($_POST['btnRegistro'])) {
    $identificacion = $_POST['txtIdentificacion'];
    $nombre = $_POST['txtNombre'];
    $correoElectronico = $_POST['txtCorreoElectronico'];
    $contrasenna = $_POST['txtContrasenna'];
    $confirmarContrasenna = $_POST['txtconfirmarContrasenna'];

    $response = RegistroModel($identificacion,$nombre,$correoElectronico,$contrasenna,$confirmarContrasenna);

    if($response) {
        header("Location: ../../View/Inicio/login.php");
        exit();
    }

    $_POST["mensaje"] = "Su información no se ha podido registrar correctamente";
}

// boton de Recuperar Acceso
if(isset($_POST['btnRecuperarAcceso'])) {
    $identificacion = $_POST['txtIdentificacion'];

    $response = ConsultarIdentificacionModel($identificacion);

    if($response) {
        $contrasennaTemporal = GenerarContrasenna();

        $actualizado = ActualizarContrasennaModel($response['Consecutivo'], $contrasennaTemporal, null);

        if($actualizado) {
            $plantilla = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/lifty/View/Template/RecuperarAcceso.html');
            $plantilla = str_replace('{{Nombre}}', $response['Nombre'], $plantilla);
            $plantilla = str_replace('{{Contrasenna}}', $contrasennaTemporal, $plantilla);

            EnviarCorreo('Recuperar Acceso', $plantilla, $response['CorreoElectronico']);

            header("Location: ../../View/Inicio/login.php");
            exit();
        }
    }

    $_POST["mensaje"] = "Su información no se ha podido validar correctamente";
}
