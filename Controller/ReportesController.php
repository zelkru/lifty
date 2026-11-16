<?php
include_once __DIR__ . '/../Model/ReportesModel.php';
include_once __DIR__ . '/ConfigController.php';

function ConsultarResumen()
{
    return ConsultarResumenModel();
}

function ConsultarTarjetasResumen()
{
    $resumen = ConsultarResumen();
    return [
        ['ti-coin', 'Ventas', Dinero($resumen['Ventas'] ?? 0)],
        ['ti-receipt', 'Pedidos', $resumen['Pedidos'] ?? 0],
        ['ti-clock', 'Pendientes', $resumen['PedidosPendientes'] ?? 0],
        ['ti-package', 'Productos activos', $resumen['Productos'] ?? 0],
        ['ti-stack-2', 'Unidades en stock', $resumen['UnidadesStock'] ?? 0],
        ['ti-users', 'Clientes', $resumen['Clientes'] ?? 0]
    ];
}

function ReportePedidosPorEstado()
{
    return ReportePedidosPorEstadoModel();
}

function ReporteStockBajo()
{
    return ReporteStockBajoModel(UMBRAL_STOCK_BAJO);
}
