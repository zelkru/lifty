<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/ReportesController.php';
include_once __DIR__ . '/../../Controller/PedidosController.php';

ValidarAdmin();

$porEstado = ReportePedidosPorEstado();
$stockBajo = ReporteStockBajo();
$totalPedidos = array_sum(array_column($porEstado, 'Cantidad'));
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Reportes - Lifty'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content py-10">
        <div class="container-fluid">

            <div class="page-head">
                <div>
                    <h1>Reportes</h1>
                    <p>Indicadores básicos de pedidos e inventario</p>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-xl-6">
                    <div class="l-card l-card-body">
                        <h2 class="l-card-title">Pedidos por estado</h2>
                        <?php if ($totalPedidos == 0) { ?>
                            <div class="empty-state py-4"><i class="ti ti-receipt-off"></i><p class="mb-0">Todavía no hay pedidos.</p></div>
                        <?php } ?>
                        <?php foreach ($porEstado as $estadoPedido) { $porcentaje = $totalPedidos > 0 ? round($estadoPedido['Cantidad'] * 100 / $totalPedidos) : 0; ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span><span class="badge text-bg-<?php echo ClaseEstadoPedido($estadoPedido['EstadoPedido']); ?>"><?php echo $estadoPedido['EstadoPedido']; ?></span> <?php echo $estadoPedido['Cantidad']; ?> pedido<?php echo $estadoPedido['Cantidad'] == 1 ? '' : 's'; ?></span>
                                    <span class="text-secondary"><?php echo Dinero($estadoPedido['Monto']); ?></span>
                                </div>
                                <div class="bar-track"><div class="bar-fill" style="width: <?php echo $porcentaje; ?>%"></div></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="col-12 col-xl-6">
                    <div class="l-card">
                        <div class="l-card-body pb-2">
                            <h2 class="l-card-title mb-0">Productos con stock bajo</h2>
                            <p class="small text-secondary mb-0">Presentaciones activas con <?php echo UMBRAL_STOCK_BAJO; ?> unidades o menos</p>
                        </div>
                        <div class="table-responsive">
                            <table class="table l-table tabla-apilada tabla-datos" id="tablaStockBajo" data-pagina="8">
                                <thead><tr><th data-orden="texto">Producto</th><th data-orden="texto">SKU</th><th data-orden="numero" class="text-end">Stock</th></tr></thead>
                                <tbody>
                                <?php foreach ($stockBajo as $producto) { ?>
                                    <tr>
                                        <td><?php echo Texto($producto['NombreProducto']); ?> <span class="text-secondary small">&middot; <?php echo Texto($producto['Presentacion']); ?></span></td>
                                        <td><code><?php echo Texto($producto['Sku']); ?></code></td>
                                        <td class="text-end" data-valor="<?php echo (int) $producto['Stock']; ?>"><span class="badge text-bg-<?php echo $producto['Stock'] == 0 ? 'danger' : 'warning'; ?>"><?php echo $producto['Stock']; ?></span></td>
                                    </tr>
                                <?php } ?>
                                <?php if (count($stockBajo) == 0) { ?>
                                    <tr><td colspan="3" class="text-center text-secondary py-4">No hay productos con stock bajo.</td></tr>
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
