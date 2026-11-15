<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/ReportesController.php';
include_once __DIR__ . '/../../Controller/PedidosController.php';

ValidarAdmin();

$resumen = ConsultarResumen();
$recientes = array_slice(ConsultarPedidos(), 0, 6);
$stockBajo = array_slice(ReporteStockBajo(), 0, 6);

$tarjetas = [
    ['ti-coin', 'Ventas', Dinero($resumen['Ventas'] ?? 0)],
    ['ti-receipt', 'Pedidos', $resumen['Pedidos'] ?? 0],
    ['ti-clock', 'Pendientes', $resumen['PedidosPendientes'] ?? 0],
    ['ti-package', 'Productos activos', $resumen['Productos'] ?? 0],
    ['ti-stack-2', 'Unidades en stock', $resumen['UnidadesStock'] ?? 0],
    ['ti-users', 'Clientes', $resumen['Clientes'] ?? 0],
];
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Panel - Lifty'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content py-10">
        <div class="container-fluid">

            <?php MostrarMensaje(); ?>

            <div class="page-head">
                <div>
                    <h1>Hola, <?php echo Texto(explode(' ', $_SESSION['NombreUsuario'])[0]); ?></h1>
                    <p>Resumen de la tienda</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="../Inventario/inventory.php" class="btn btn-primary"><i class="ti ti-plus"></i> Nuevo producto</a>
                    <a href="../Pedidos/orders.php" class="btn btn-outline-secondary">Ver pedidos</a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <?php foreach ($tarjetas as $t) { ?>
                    <div class="col-6 col-xl-2">
                        <div class="l-card stat-card">
                            <span class="stat-icon"><i class="ti <?php echo $t[0]; ?>"></i></span>
                            <div>
                                <div class="stat-value"><?php echo $t[2]; ?></div>
                                <div class="stat-label"><?php echo $t[1]; ?></div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="row g-4">
                <div class="col-12 col-xl-7">
                    <div class="l-card">
                        <div class="l-card-body pb-2 d-flex justify-content-between">
                            <h2 class="l-card-title mb-0">Pedidos recientes</h2>
                            <a href="../Pedidos/orders.php" class="small">Ver todos</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table l-table">
                                <thead><tr><th>#</th><th>Cliente</th><th>Fecha</th><th class="text-end">Total</th><th>Estado</th></tr></thead>
                                <tbody>
                                <?php foreach ($recientes as $p) { ?>
                                    <tr>
                                        <td><a href="../Pedidos/order.php?id=<?php echo $p['Consecutivo']; ?>">#<?php echo $p['Consecutivo']; ?></a></td>
                                        <td><?php echo Texto($p['NombreCliente']); ?></td>
                                        <td class="text-nowrap"><?php echo date('d/m/Y', strtotime($p['FechaPedido'])); ?></td>
                                        <td class="text-end"><?php echo Dinero($p['Total']); ?></td>
                                        <td><span class="badge text-bg-<?php echo ClaseEstadoPedido($p['EstadoPedido']); ?>"><?php echo $p['EstadoPedido']; ?></span></td>
                                    </tr>
                                <?php } ?>
                                <?php if (count($recientes) == 0) { ?>
                                    <tr><td colspan="5" class="text-center text-secondary py-4">Todavía no hay pedidos.</td></tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="l-card">
                        <div class="l-card-body pb-2 d-flex justify-content-between">
                            <h2 class="l-card-title mb-0">Stock bajo</h2>
                            <a href="../Reportes/reports.php" class="small">Ver reporte</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table l-table">
                                <thead><tr><th>Producto</th><th class="text-end">Stock</th></tr></thead>
                                <tbody>
                                <?php foreach ($stockBajo as $s) { ?>
                                    <tr>
                                        <td>
                                            <?php echo Texto($s['NombreProducto']); ?>
                                            <div class="small text-secondary"><?php echo Texto($s['Presentacion']); ?></div>
                                        </td>
                                        <td class="text-end"><span class="badge text-bg-<?php echo $s['Stock'] == 0 ? 'danger' : 'warning'; ?>"><?php echo $s['Stock']; ?></span></td>
                                    </tr>
                                <?php } ?>
                                <?php if (count($stockBajo) == 0) { ?>
                                    <tr><td colspan="2" class="text-center text-secondary py-4">Todo el inventario tiene existencias suficientes.</td></tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <?php MostrarFooter(); ?>

        </div>
    </main>

    <?php IncludeJS(); ?>

</body>

</html>
