<?php
include_once __DIR__ . '/../Model/InicioModel.php';
include_once __DIR__ . '/../Model/PersonalModel.php';
include_once __DIR__ . '/ConfigController.php';
include_once __DIR__ . '/InicioController.php';

ValidarSesion();

function ConsultarUsuario()
{
    return ConsultarUsuarioModel($_SESSION['ConsecutivoUsuario']);
}

function ConsultarProvincias()
{
    return ConsultarProvinciasModel();
}

function ConsultarDirecciones()
{
    return ConsultarDireccionesModel($_SESSION['ConsecutivoUsuario']);
}

if (isset($_POST['btnCambiarContrasenna'])) {
    $contrasennaActual = $_POST['txtContrasennaActual'] ?? '';
    $contrasennaNueva = $_POST['txtContrasennaNueva'] ?? '';
    $confirmacion = $_POST['txtConfirmarContrasennaNueva'] ?? '';
    $consecutivoUsuario = $_SESSION['ConsecutivoUsuario'];

    $hashActual = ConsultarContrasennaModel($consecutivoUsuario);

    if (!$hashActual || !password_verify($contrasennaActual, $hashActual)) {
        AsignarMensaje('La contraseña actual no es correcta.', 'danger');
    }
    else if (!ValidarContrasenna($contrasennaNueva)) {
        AsignarMensaje('La contraseña nueva debe tener al menos 8 caracteres, con letras y números.', 'danger');
    }
    else if ($contrasennaNueva !== $confirmacion) {
        AsignarMensaje('La confirmación no coincide con la contraseña nueva.', 'danger');
    }
    else if (ActualizarContrasennaModel($consecutivoUsuario, password_hash($contrasennaNueva, PASSWORD_DEFAULT))) {
        AsignarMensaje('Su contraseña se ha actualizado correctamente.');
    }
    else {
        AsignarMensaje('Su contraseña no se ha podido actualizar.', 'danger');
    }

    Redirigir('account.php');
}

if (isset($_POST['btnCambiarDatosCuenta'])) {
    $nombre = LeerTexto('txtNombre');
    $apellido1 = LeerTexto('txtApellido1');
    $apellido2 = LeerTexto('txtApellido2');
    $correoElectronico = strtolower(LeerTexto('txtCorreoElectronico'));
    $telefono = LeerTexto('txtTelefono');

    if ($nombre == '' || $apellido1 == '' || !filter_var($correoElectronico, FILTER_VALIDATE_EMAIL) || !preg_match('/^[0-9]{8}$/', $telefono)) {
        AsignarMensaje('Verifique los datos: nombre, primer apellido, correo válido y teléfono de 8 dígitos.', 'danger');
    }
    else {
        $response = ActualizarDatosCuentaModel($_SESSION['ConsecutivoUsuario'], $nombre, $apellido1, $apellido2, $correoElectronico, $telefono);

        if ($response === -1) {
            AsignarMensaje('Ese correo electrónico ya pertenece a otra cuenta.', 'danger');
        }
        else if ($response) {
            $_SESSION['NombreUsuario'] = $nombre . ' ' . $apellido1;
            $_SESSION['CorreoElectronico'] = $correoElectronico;
            AsignarMensaje('Su información se ha actualizado correctamente.');
        }
        else {
            AsignarMensaje('Su información no se ha podido actualizar.', 'danger');
        }
    }

    Redirigir('account.php');
}

function LeerDireccion()
{
    return [LeerEntero('txtProvincia'), LeerTexto('txtCanton'), LeerTexto('txtDistrito'), LeerTexto('txtDetalle'), LeerTexto('txtCodigoPostal')];
}

function DireccionValida($direccion)
{
    return in_array($direccion[0], array_column(ConsultarProvinciasModel(), 'Consecutivo')) && $direccion[1] != '' && $direccion[2] != '' && $direccion[3] != '' && ($direccion[4] == '' || preg_match('/^[0-9]{4,10}$/', $direccion[4]));
}

if (isset($_POST['btnRegistrarDireccion'])) {
    $direccion = LeerDireccion();

    if (!DireccionValida($direccion)) {
        AsignarMensaje('Complete provincia, cantón, distrito y señas. El código postal solo lleva números.', 'danger');
    }
    else if (RegistrarDireccionModel($_SESSION['ConsecutivoUsuario'], $direccion[0], $direccion[1], $direccion[2], $direccion[3], $direccion[4])) {
        AsignarMensaje('La dirección se ha registrado correctamente.');
    }
    else {
        AsignarMensaje('La dirección no se ha podido registrar.', 'danger');
    }

    Redirigir('account.php');
}

if (isset($_POST['btnActualizarDireccion'])) {
    $direccion = LeerDireccion();

    if (!DireccionValida($direccion)) {
        AsignarMensaje('Complete provincia, cantón, distrito y señas. El código postal solo lleva números.', 'danger');
    }
    else if (ActualizarDireccionModel(LeerEntero('txtConsecutivo'), $_SESSION['ConsecutivoUsuario'], $direccion[0], $direccion[1], $direccion[2], $direccion[3], $direccion[4])) {
        AsignarMensaje('La dirección se ha actualizado correctamente.');
    }
    else {
        AsignarMensaje('La dirección no se ha podido actualizar.', 'danger');
    }

    Redirigir('account.php');
}

if (isset($_POST['btnInactivarDireccion'])) {
    if (InactivarDireccionModel(LeerEntero('txtConsecutivo'), $_SESSION['ConsecutivoUsuario'])) {
        AsignarMensaje('La dirección se ha eliminado de su cuenta.');
    }
    else {
        AsignarMensaje('La dirección no se ha podido eliminar.', 'danger');
    }

    Redirigir('account.php');
}

function ConsultarUsuarios()
{
    return ConsultarUsuariosModel();
}

if (isset($_POST['btnCambiarEstadoUsuario'])) {
    $consecutivo = LeerEntero('txtConsecutivo');
    $estado = LeerEntero('txtEstado') == 1 ? 1 : 0;

    if (!EsAdministrador()) {
        AsignarMensaje('No tiene permiso para esta acción.', 'danger');
    }
    else if ($consecutivo == $_SESSION['ConsecutivoUsuario']) {
        AsignarMensaje('No puede inactivar su propia cuenta.', 'warning');
    }
    else if (ActualizarEstadoUsuarioModel($consecutivo, $estado)) {
        AsignarMensaje($estado == 1 ? 'El usuario se activó correctamente.' : 'El usuario se inactivó correctamente.');
    }
    else {
        AsignarMensaje('No se pudo cambiar el estado del usuario.', 'danger');
    }

    Redirigir('users.php');
}

function ConsultarRoles()
{
    return ConsultarRolesModel();
}

if (isset($_POST['btnGuardarUsuario'])) {
    $consecutivo = LeerEntero('txtConsecutivo');
    $nombre = LeerTexto('txtNombre');
    $apellido1 = LeerTexto('txtApellido1');
    $apellido2 = LeerTexto('txtApellido2');
    $correoElectronico = strtolower(LeerTexto('txtCorreoElectronico'));
    $telefono = LeerTexto('txtTelefono');
    $rol = LeerEntero('txtRol');
    $estado = LeerEntero('txtEstado') == 1 ? 1 : 0;
    $contrasenna = $_POST['txtContrasenna'] ?? '';
    $esUnoMismo = $consecutivo > 0 && $consecutivo == $_SESSION['ConsecutivoUsuario'];

    if ($esUnoMismo) {
        $rol = $_SESSION['ConsecutivoRol'];
        $estado = 1;
    }

    $rolesValidos = array_column(ConsultarRolesModel(), 'Consecutivo');

    if (!EsAdministrador()) {
        AsignarMensaje('No tiene permiso para esta acción.', 'danger');
    }
    else if ($nombre == '' || $apellido1 == '' || !filter_var($correoElectronico, FILTER_VALIDATE_EMAIL) || !preg_match('/^[0-9]{8}$/', $telefono)) {
        AsignarMensaje('Verifique los datos: nombre, primer apellido, correo válido y teléfono de 8 dígitos.', 'danger');
    }
    else if (!in_array($rol, $rolesValidos)) {
        AsignarMensaje('Seleccione un rol válido.', 'danger');
    }
    else if ($contrasenna != '' && !ValidarContrasenna($contrasenna)) {
        AsignarMensaje('La contraseña debe tener al menos 8 caracteres, con letras y números.', 'danger');
    }
    else if ($consecutivo == 0 && $contrasenna == '') {
        AsignarMensaje('Indique una contraseña inicial para el usuario nuevo.', 'danger');
    }
    else {
        if ($consecutivo == 0) {
            $response = RegistrarUsuarioAdminModel($nombre, $apellido1, $apellido2, $correoElectronico, $telefono, password_hash($contrasenna, PASSWORD_DEFAULT), $rol);
            $correcto = $response > 0;
        }
        else {
            $response = ActualizarUsuarioAdminModel($consecutivo, $nombre, $apellido1, $apellido2, $correoElectronico, $telefono, $rol, $estado);
            $correcto = $response === true;

            if ($correcto && $contrasenna != '') {
                ActualizarContrasennaModel($consecutivo, password_hash($contrasenna, PASSWORD_DEFAULT));
            }
            if ($correcto && $esUnoMismo) {
                $_SESSION['NombreUsuario'] = $nombre . ' ' . $apellido1;
                $_SESSION['CorreoElectronico'] = $correoElectronico;
            }
        }

        if ($response === -1) {
            AsignarMensaje('Ese correo electrónico ya pertenece a otro usuario.', 'danger');
        }
        else if ($correcto) {
            AsignarMensaje($consecutivo == 0 ? 'El usuario se creó correctamente.' : 'El usuario se actualizó correctamente.');
        }
        else {
            AsignarMensaje('No se pudo guardar el usuario.', 'danger');
        }
    }

    Redirigir('users.php');
}
