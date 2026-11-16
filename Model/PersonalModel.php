<?php
include_once __DIR__ . '/ConfigModel.php';

function ConsultarUsuarioModel($consecutivo)
{
    try {
        $context = Conectar();

        $data = ProcedimientoFila($context, 'spConsultarUsuario', [(int) $consecutivo]);

        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarUsuarioModel');
        return null;
    }
}

function ActualizarDatosCuentaModel($consecutivo, $nombre, $apellido1, $apellido2, $correoElectronico, $telefono)
{
    try {
        $context = Conectar();

        $actualizado = ProcedimientoBooleano($context, 'spActualizarDatosCuenta',
            [(int) $consecutivo, $nombre, $apellido1, $apellido2, $correoElectronico, $telefono]);

        Cerrar($context);
        return $actualizado;
    }
    catch (Exception $exc) {
        if ($exc -> getCode() == 1062) {
            return -1;
        }
        RegistrarError($exc, 'ActualizarDatosCuentaModel');
        return false;
    }
}

function ConsultarUsuariosModel()
{
    try {
        $context = Conectar();

        $data = Procedimiento($context, 'spConsultarUsuarios');

        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarUsuariosModel');
        return [];
    }
}

function ActualizarEstadoUsuarioModel($consecutivo, $estado)
{
    try {
        $context = Conectar();

        $actualizado = ProcedimientoBooleano($context, 'spActualizarEstadoUsuario', [(int) $consecutivo, (int) $estado]);

        Cerrar($context);
        return $actualizado;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ActualizarEstadoUsuarioModel');
        return false;
    }
}

function ConsultarDireccionesModel($consecutivoUsuario)
{
    try {
        $context = Conectar();

        $data = Procedimiento($context, 'spConsultarDirecciones', [(int) $consecutivoUsuario]);

        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarDireccionesModel');
        return [];
    }
}

function RegistrarDireccionModel($consecutivoUsuario, $provincia, $canton, $distrito, $detalle, $codigoPostal)
{
    try {
        $context = Conectar();

        $fila = ProcedimientoFila($context, 'spRegistrarDireccion',
            [(int) $consecutivoUsuario, $provincia, $canton, $distrito, $detalle, $codigoPostal]);

        Cerrar($context);
        return $fila !== null;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'RegistrarDireccionModel');
        return false;
    }
}

function ActualizarDireccionModel($consecutivo, $consecutivoUsuario, $provincia, $canton, $distrito, $detalle, $codigoPostal)
{
    try {
        $context = Conectar();

        $actualizado = ProcedimientoBooleano($context, 'spActualizarDireccion',
            [(int) $consecutivo, (int) $consecutivoUsuario, $provincia, $canton, $distrito, $detalle, $codigoPostal]);

        Cerrar($context);
        return $actualizado;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ActualizarDireccionModel');
        return false;
    }
}

function InactivarDireccionModel($consecutivo, $consecutivoUsuario)
{
    try {
        $context = Conectar();

        $actualizado = ProcedimientoBooleano($context, 'spInactivarDireccion', [(int) $consecutivo, (int) $consecutivoUsuario]);

        Cerrar($context);
        return $actualizado;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'InactivarDireccionModel');
        return false;
    }
}

function ConsultarRolesModel()
{
    try {
        $context = Conectar();
        $data = Procedimiento($context, 'spConsultarRoles');
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarRolesModel');
        return [];
    }
}

function RegistrarUsuarioAdminModel($nombre, $apellido1, $apellido2, $correoElectronico, $telefono, $contrasennaHash, $consecutivoRol)
{
    try {
        $context = Conectar();
        $fila = ProcedimientoFila($context, 'spRegistrarUsuarioAdmin',
            [$nombre, $apellido1, $apellido2, $correoElectronico, $telefono, $contrasennaHash, (int) $consecutivoRol]);
        Cerrar($context);
        return $fila ? (int) $fila['Consecutivo'] : 0;
    }
    catch (Exception $exc) {
        if ($exc -> getCode() == 1062) {
            return -1;
        }
        RegistrarError($exc, 'RegistrarUsuarioAdminModel');
        return 0;
    }
}

function ActualizarUsuarioAdminModel($consecutivo, $nombre, $apellido1, $apellido2, $correoElectronico, $telefono, $consecutivoRol, $estado)
{
    try {
        $context = Conectar();
        $actualizado = ProcedimientoBooleano($context, 'spActualizarUsuarioAdmin',
            [(int) $consecutivo, $nombre, $apellido1, $apellido2, $correoElectronico, $telefono, (int) $consecutivoRol, (int) $estado]);
        Cerrar($context);
        return $actualizado;
    }
    catch (Exception $exc) {
        if ($exc -> getCode() == 1062) {
            return -1;
        }
        RegistrarError($exc, 'ActualizarUsuarioAdminModel');
        return false;
    }
}
