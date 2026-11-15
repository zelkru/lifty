<?php
include_once __DIR__ . '/ConfigModel.php';

/*
 * Crea el pedido (estado Pendiente) con su detalle dentro de una transaccion.
 * $lineas = [ ['Variante' => id, 'Cantidad' => n, 'Precio' => decimal], ... ]
 * Los precios los lee el Controller de la base de datos, nunca del navegador.
 * Devuelve el consecutivo del pedido o 0 si falla.
 */
function RegistrarPedidoModel($consecutivoUsuario, $consecutivoDireccion, $consecutivoMetodoPago, $subtotal, $impuesto, $costoEnvio, $total, $lineas)
{
    $context = null;

    try {
        $context = Conectar();
        $context -> begin_transaction();

        $fila = ProcedimientoFila($context, 'spRegistrarPedido',
            [(int) $consecutivoUsuario, (int) $consecutivoDireccion, (int) $consecutivoMetodoPago,
             (float) $subtotal, (float) $impuesto, (float) $costoEnvio, (float) $total]);

        $consecutivoPedido = $fila ? (int) $fila['Consecutivo'] : 0;

        foreach ($lineas as $linea) {
            Procedimiento($context, 'spRegistrarDetallePedido',
                [$consecutivoPedido, (int) $linea['Variante'], (int) $linea['Cantidad'], (float) $linea['Precio']]);
        }

        $context -> commit();
        Cerrar($context);
        return $consecutivoPedido;
    }
    catch (Exception $exc) {
        if ($context) {
            try { $context -> rollback(); Cerrar($context); } catch (Exception $e) { }
        }
        RegistrarError($exc, 'RegistrarPedidoModel');
        return 0;
    }
}

function PagarPedidoModel($consecutivoPedido, $consecutivoUsuario)
{
    try {
        $context = Conectar();
        $pagado = ProcedimientoBooleano($context, 'spPagarPedido', [(int) $consecutivoPedido, (int) $consecutivoUsuario]);
        Cerrar($context);
        return $pagado;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'PagarPedidoModel');
        return false;
    }
}

/* $consecutivoUsuario = 0 devuelve todos los pedidos (administrador). */
function ConsultarPedidosModel($consecutivoUsuario)
{
    try {
        $context = Conectar();
        $data = Procedimiento($context, 'spConsultarPedidos', [(int) $consecutivoUsuario]);
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarPedidosModel');
        return [];
    }
}

function ConsultarPedidoModel($consecutivo)
{
    try {
        $context = Conectar();
        $data = ProcedimientoFila($context, 'spConsultarPedido', [(int) $consecutivo]);
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarPedidoModel');
        return null;
    }
}

function ConsultarDetallePedidoModel($consecutivoPedido)
{
    try {
        $context = Conectar();
        $data = Procedimiento($context, 'spConsultarDetallePedido', [(int) $consecutivoPedido]);
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarDetallePedidoModel');
        return [];
    }
}

function ActualizarEstadoPedidoModel($consecutivo, $estadoPedido)
{
    try {
        $context = Conectar();
        $actualizado = ProcedimientoBooleano($context, 'spActualizarEstadoPedido', [(int) $consecutivo, $estadoPedido]);
        Cerrar($context);
        return $actualizado;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ActualizarEstadoPedidoModel');
        return false;
    }
}
