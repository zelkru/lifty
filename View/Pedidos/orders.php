<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/PedidosController.php';

ValidarSesion();

$esAdmin = EsAdministrador();
$estadoFiltro = $_GET['estado'] ?? '';
$pedidos = ConsultarPedidos();

if (!$esAdmin && $estadoFiltro != '') {
    $pedidos = array_filter($pedidos, function ($p) use ($estadoFiltro) { return $p['EstadoPedido'] == $estadoFiltro; });
}
$estados = ['Pendiente', 'Pagado', 'Enviado', 'Entregado', 'Cancelado'];
$iconoEstado = ['Pendiente' => 'ti-clock', 'Pagado' => 'ti-circle-check', 'Enviado' => 'ti-truck-delivery', 'Entregado' => 'ti-package', 'Cancelado' => 'ti-circle-x'];
$metodos = array_values(array_unique(array_column($pedidos, 'NombreMetodoPago')));
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS(($esAdmin ? 'Pedidos' : 'Mis pedidos') . ' - Lifty'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content py-10">
        <div class="container-fluid">

            <div class="page-head">
                <div>
                    <h1><?php echo $esAdmin ? 'Pedidos' : 'Mis pedidos'; ?></h1>
                    <p><?php echo $esAdmin ? 'Consulte los pedidos, ordénelos, fíltrelos y actualice su estado' : 'Historial y seguimiento de tus compras'; ?></p>
                </div>
                <?php if (!$esAdmin) { ?>
                    <form method="GET" class="d-flex gap-2">
                        <select name="estado" class="form-select" aria-label="Filtrar por estado" onchange="this.form.submit()">
                            <option value="">Todos los estados</option>
                            <?php foreach ($estados as $e) { ?>
                                <option value="<?php echo $e; ?>" <?php echo $estadoFiltro == $e ? 'selected' : ''; ?>><?php echo $e; ?></option>
                            <?php } ?>
                        </select>
                    </form>
                <?php } ?>
            </div>

            <?php MostrarMensaje(); ?>

            <?php if (!$esAdmin) { ?>

                <?php if (count($pedidos) == 0) { ?>
                    <div class="l-card empty-state">
                        <i class="ti ti-receipt-off"></i>
                        <p class="fw-bold">Todavía no tienes pedidos<?php echo $estadoFiltro != '' ? ' con ese estado' : ''; ?>.</p>
                        <a href="../Catalogo/catalog.php#productos" class="btn btn-primary px-4">Ir al catálogo</a>
                    </div>
                <?php } ?>

                <?php foreach ($pedidos as $p) { ?>
                    <a href="order.php?id=<?php echo $p['Consecutivo']; ?>" class="pedido-card">
                        <div class="num">#<?php echo $p['Consecutivo']; ?></div>
                        <div>
                            <div class="fecha"><?php echo date('d/m/Y', strtotime($p['FechaPedido'])); ?> <span class="text-secondary fw-normal">&middot; <?php echo date('H:i', strtotime($p['FechaPedido'])); ?></span></div>
                            <div class="meta"><i class="ti ti-credit-card"></i> <?php echo Texto($p['NombreMetodoPago']); ?></div>
                        </div>
                        <span class="estado-pill estado-<?php echo $p['EstadoPedido']; ?>"><i class="ti <?php echo $iconoEstado[$p['EstadoPedido']] ?? 'ti-circle'; ?>"></i> <?php echo $p['EstadoPedido']; ?></span>
                        <div class="total"><?php echo Dinero($p['Total']); ?></div>
                    </a>
                <?php } ?>

            <?php } else { ?>

                <div class="barra-filtros">
                    <input type="search" class="form-control ft ft-buscar" data-tabla="#tablaPedidos" placeholder="Buscar por número o cliente..." aria-label="Buscar">
                    <select class="form-select ft" data-tabla="#tablaPedidos" data-col="5" aria-label="Estado">
                        <option value="">Todos los estados</option>
                        <?php foreach ($estados as $e) { ?><option value="<?php echo $e; ?>"><?php echo $e; ?></option><?php } ?>
                    </select>
                    <select class="form-select ft" data-tabla="#tablaPedidos" data-col="3" aria-label="Método de pago">
                        <option value="">Todos los métodos</option>
                        <?php foreach ($metodos as $m) { ?><option value="<?php echo Texto($m); ?>"><?php echo Texto($m); ?></option><?php } ?>
                    </select>
                    <label class="fecha">Desde <input type="date" class="form-control ft" data-tabla="#tablaPedidos" data-col="2" data-rango="desde"></label>
                    <label class="fecha">hasta <input type="date" class="form-control ft" data-tabla="#tablaPedidos" data-col="2" data-rango="hasta"></label>
                    <button type="button" class="btn btn-outline-secondary limpiar-filtros" data-tabla="#tablaPedidos"><i class="ti ti-filter-off"></i> Limpiar</button>
                </div>

                <div class="l-card">
                    <div class="table-responsive">
                        <table class="table l-table table-hover tabla-apilada tabla-datos" id="tablaPedidos" data-pagina="12">
                            <thead>
                                <tr>
                                    <th data-orden="numero">#</th><th data-orden="texto">Cliente</th><th data-orden="fecha">Fecha</th>
                                    <th data-orden="texto">Pago</th><th data-orden="numero" class="text-end">Total</th>
                                    <th data-orden="texto">Estado</th><th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($pedidos as $p) { $siguientes = TransicionesPedido($p['EstadoPedido']); ?>
                                <tr>
                                    <td><a href="order.php?id=<?php echo $p['Consecutivo']; ?>" class="fw-semibold">#<?php echo $p['Consecutivo']; ?></a></td>
                                    <td><?php echo Texto($p['NombreCliente']); ?></td>
                                    <td class="text-nowrap" data-valor="<?php echo date('Y-m-d H:i', strtotime($p['FechaPedido'])); ?>"><?php echo date('d/m/Y H:i', strtotime($p['FechaPedido'])); ?></td>
                                    <td><?php echo Texto($p['NombreMetodoPago']); ?></td>
                                    <td class="text-end text-nowrap" data-valor="<?php echo (float) $p['Total']; ?>"><?php echo Dinero($p['Total']); ?></td>
                                    <td data-valor="<?php echo $p['EstadoPedido']; ?>"><span class="badge text-bg-<?php echo ClaseEstadoPedido($p['EstadoPedido']); ?>"><?php echo $p['EstadoPedido']; ?></span></td>
                                    <td class="text-end text-nowrap">
                                        <a href="order.php?id=<?php echo $p['Consecutivo']; ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-eye"></i> Detalle</a>
                                        <?php if (count($siguientes) > 0) { ?>
                                            <form action="" method="POST" class="d-inline-flex gap-1 m-0 align-middle">
                                                <?php CampoCsrf(); ?>
                                                <input type="hidden" name="txtConsecutivo" value="<?php echo $p['Consecutivo']; ?>">
                                                <select name="txtEstadoPedido" class="form-select form-select-sm" style="width: auto;" aria-label="Nuevo estado">
                                                    <?php foreach ($siguientes as $s) { ?><option value="<?php echo $s; ?>"><?php echo $s; ?></option><?php } ?>
                                                </select>
                                                <button type="submit" name="btnCambiarEstadoPedido" class="btn btn-primary btn-sm"><i class="ti ti-refresh"></i> Cambiar</button>
                                            </form>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php } ?>

            <?php MostrarFooter(); ?>

        </div>
    </main>

    <?php IncludeJS(); ?>

</body>

</html>
