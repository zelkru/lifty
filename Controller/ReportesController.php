<?php
include_once __DIR__ . '/../Model/ReportesModel.php';
include_once __DIR__ . '/ConfigController.php';

function ConsultarResumen()
{
    return ConsultarResumenModel();
}

function ReportePedidosPorEstado()
{
    return ReportePedidosPorEstadoModel();
}

function ReporteStockBajo()
{
    return ReporteStockBajoModel(UMBRAL_STOCK_BAJO);
}
