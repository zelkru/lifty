<?php
include_once __DIR__ . '/../Model/InicioModel.php';
include_once __DIR__ . '/ConfigController.php';

const LARGO_MINIMO_CONTRASENNA = 8;

function ValidarContrasenna($contrasenna)
{
    return strlen($contrasenna) >= LARGO_MINIMO_CONTRASENNA
        && preg_match('/[A-Za-z]/', $contrasenna)
        && preg_match('/\d/', $contrasenna);
}

if (isset($_POST['btnRegistro'])) {
    $nombre = LeerTexto('txtNombre');
    $apellido1 = LeerTexto('txtApellido1');
    $apellido2 = LeerTexto('txtApellido2');
    $correoElectronico = strtolower(LeerTexto('txtCorreoElectronico'));
    $telefono = LeerTexto('txtTelefono');
    $contrasenna = $_POST['txtContrasenna'] ?? '';
    $confirmacion = $_POST['txtConfirmarContrasenna'] ?? '';

    if ($nombre == '' || $apellido1 == '' || $correoElectronico == '' || $telefono == '' || $contrasenna == '') {
        $_POST['mensaje'] = 'Complete todos los campos obligatorios.';
    }
    else if (!filter_var($correoElectronico, FILTER_VALIDATE_EMAIL)) {
        $_POST['mensaje'] = 'Ingrese un correo electrónico válido.';
    }
    else if (!preg_match('/^[0-9]{8}$/', $telefono)) {
        $_POST['mensaje'] = 'El teléfono debe tener 8 dígitos.';
    }
    else if (!ValidarContrasenna($contrasenna)) {
        $_POST['mensaje'] = 'La contraseña debe tener al menos 8 caracteres, con letras y números.';
    }
    else if ($contrasenna !== $confirmacion) {
        $_POST['mensaje'] = 'Las contraseñas no coinciden.';
    }
    else {
        $response = RegistroModel($nombre, $apellido1, $apellido2, $correoElectronico, $telefono,
            password_hash($contrasenna, PASSWORD_DEFAULT));

        if ($response > 0) {
            $plantilla = ArmarPlantilla('Bienvenida.html', ['Nombre' => $nombre]);
            EnviarCorreoElectronico('Bienvenido a Lifty', $plantilla, $correoElectronico);

            AsignarMensaje('Su cuenta se creó correctamente. Ya puede iniciar sesión.');
            Redirigir('login.php');
        }

        $_POST['mensaje'] = ($response == -1)
            ? 'Ese correo electrónico ya está registrado.'
            : 'Su información no se ha podido registrar correctamente.';
    }
}

if (isset($_POST['btnInicioSesion'])) {
    $correoElectronico = strtolower(LeerTexto('txtCorreoElectronico'));
    $contrasenna = $_POST['txtContrasenna'] ?? '';

    $response = InicioSesionModel($correoElectronico);

    if ($response && password_verify($contrasenna, $response['Contrasenna'])) {
        session_regenerate_id(true);

        $_SESSION['ConsecutivoUsuario'] = (int) $response['Consecutivo'];
        $_SESSION['NombreUsuario'] = $response['Nombre'] . ' ' . $response['Apellido1'];
        $_SESSION['CorreoElectronico'] = $response['CorreoElectronico'];
        $_SESSION['ConsecutivoRol'] = (int) $response['ConsecutivoRol'];
        $_SESSION['NombreRol'] = $response['NombreRol'];

        Redirigir(PaginaInicioRol());
    }

    $_POST['mensaje'] = 'Correo o contraseña incorrectos, o la cuenta está inactiva.';
}

if (isset($_POST['btnRecuperarContrasenna'])) {
    $correoElectronico = strtolower(LeerTexto('txtCorreoElectronico'));

    $response = filter_var($correoElectronico, FILTER_VALIDATE_EMAIL) ? ConsultarCorreoModel($correoElectronico) : null;

    if ($response) {
        $contrasennaTemporal = GenerarContrasennaTemporal();

        if (ActualizarContrasennaModel($response['Consecutivo'], password_hash($contrasennaTemporal, PASSWORD_DEFAULT))) {
            $plantilla = ArmarPlantilla('RecuperarAcceso.html', ['Nombre' => $response['Nombre'], 'Contrasenna' => $contrasennaTemporal]);
            EnviarCorreoElectronico('Recuperación de acceso a Lifty', $plantilla, $response['CorreoElectronico']);
        }
    }

    AsignarMensaje('Si el correo está registrado, le enviamos una contraseña temporal. Revise su bandeja de entrada.', 'info');
    Redirigir('login.php');
}

if (isset($_POST['btnCerrarSesion'])) {
    session_destroy();
    session_start();
    AsignarMensaje('Cerró sesión correctamente.', 'info');
    Redirigir('../Inicio/login.php');
}
