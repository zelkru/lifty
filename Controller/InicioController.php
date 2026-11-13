<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/lifty/Model/InicioModel.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/lifty/Controller/ConfigController.php';

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
