<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/lifty/Model/ConfigModel.php';

function ConsultarIdentificacionModel($identificacion)
{
    try {
        $context = OpenConnection();

        $sentencia = "CALL ConsultarIdentificacion('$identificacion')";
        $resultado = $context->query($sentencia);

        $datos = null;
        while ($row = $resultado->fetch_assoc()) {
            $datos = $row;
        }

        $resultado->free_result();
        CloseConnection($context);

        return $datos;
    }
    catch (Exception $error) {
        return null;
    }
}

function ActualizarContrasennaModel($consecutivo, $contrasennaNueva, $contrasennaActual)
{
    try {
        $context = OpenConnection();

        $sentencia = "CALL ActualizarContrasenna('$consecutivo', '$contrasennaNueva', '$contrasennaActual')";
        $resultado = $context->query($sentencia);

        $actualizado = false;
        while ($row = $resultado->fetch_assoc()) {
            $actualizado = (bool) $row['Actualizado'];
        }

        $resultado->free_result();
        CloseConnection($context);

        return $actualizado;
    }
    catch (Exception $error) {
        return false;
    }
}
