<?php
include_once __DIR__ . '/ConfigModel.php';

/* Devuelve el consecutivo del usuario nuevo, 0 si falla o -1 si el correo ya existe. */
function RegistroModel($nombre, $apellido1, $apellido2, $correoElectronico, $telefono, $contrasennaHash)
{
    try {
        $context = Conectar();

        $fila = ProcedimientoFila($context, 'spRegistrarUsuario',
            [$nombre, $apellido1, $apellido2, $correoElectronico, $telefono, $contrasennaHash]);

        Cerrar($context);
        return $fila ? (int) $fila['Consecutivo'] : 0;
    }
    catch (Exception $exc) {
        if ($exc -> getCode() == 1062) {
            return -1;
        }
        RegistrarError($exc, 'RegistroModel');
        return 0;
    }
}

function InicioSesionModel($correoElectronico)
{
    try {
        $context = Conectar();

        $data = ProcedimientoFila($context, 'spIniciarSesionUsuario', [$correoElectronico]);

        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'InicioSesionModel');
        return null;
    }
}

function ConsultarCorreoModel($correoElectronico)
{
    try {
        $context = Conectar();

        $data = ProcedimientoFila($context, 'spConsultarCorreo', [$correoElectronico]);

        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarCorreoModel');
        return null;
    }
}

function ConsultarContrasennaModel($consecutivo)
{
    try {
        $context = Conectar();

        $fila = ProcedimientoFila($context, 'spConsultarContrasenna', [(int) $consecutivo]);

        Cerrar($context);
        return $fila ? $fila['Contrasenna'] : null;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarContrasennaModel');
        return null;
    }
}

function ActualizarContrasennaModel($consecutivo, $contrasennaHash)
{
    try {
        $context = Conectar();

        $actualizado = ProcedimientoBooleano($context, 'spActualizarContrasenna', [(int) $consecutivo, $contrasennaHash]);

        Cerrar($context);
        return $actualizado;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ActualizarContrasennaModel');
        return false;
    }
}
