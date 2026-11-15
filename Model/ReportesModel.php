<?php
include_once __DIR__ . '/ConfigModel.php';

function ConsultarResumenModel()
{
    try {
        $context = Conectar();
        $data = ProcedimientoFila($context, 'spConsultarResumen');
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarResumenModel');
        return null;
    }
}

function ReportePedidosPorEstadoModel()
{
    try {
        $context = Conectar();
        $data = Procedimiento($context, 'spReportePedidosPorEstado');
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ReportePedidosPorEstadoModel');
        return [];
    }
}

function ReporteStockBajoModel($umbral)
{
    try {
        $context = Conectar();
        $data = Procedimiento($context, 'spReporteStockBajo', [(int) $umbral]);
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ReporteStockBajoModel');
        return [];
    }
}
