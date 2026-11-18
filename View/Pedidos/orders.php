<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/PedidosController.php';

ValidarSesion();

$esAdmin = EsAdministrador();
$estadoFiltro = $_GET['estado'] ?? '';
$pedidos = ConsultarPedidosPorEstado($estadoFiltro);
$estados = ESTADOS_PEDIDO;
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
                        <select name="estado" class="form-select" aria-label="Filtrar por estado" data-autoenvio>
                            <option value="">Todos los estados</option>
                            <?php foreach ($estados as $estadoPedido) { ?>
                                <option value="<?php echo $estadoPedido; ?>" <?php echo $estadoFiltro == $estadoPedido ? 'selected' : ''; ?>><?php echo $estadoPedido; ?></option>
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

                <?php foreach ($pedidos as $pedido) { ?>
                    <a href="order.php?id=<?php echo $pedido['Consecutivo']; ?>" class="pedido-card">
                        <div class="num">#<?php echo $pedido['Consecutivo']; ?></div>
                        <div>
                            <div class="fecha"><?php echo date('d/m/Y', strtotime($pedido['FechaPedido'])); ?> <span class="text-secondary fw-normal">&middot; <?php echo date('H:i', strtotime($pedido['FechaPedido'])); ?></span></div>
                            <div class="meta"><i class="ti ti-credit-card"></i> <?php echo Texto($pedido['NombreMetodoPago']); ?></div>
                        </div>
                        <span class="estado-pill estado-<?php echo $pedido['EstadoPedido']; ?>"><i class="ti <?php echo $iconoEstado[$pedido['EstadoPedido']] ?? 'ti-circle'; ?>"></i> <?php echo $pedido['EstadoPedido']; ?></span>
                        <div class="total"><?php echo Dinero($pedido['Total']); ?></div>
                    </a>
                <?php } ?>

            <?php } else { ?>

                <div class="barra-filtros">
                    <input type="search" class="form-control ft ft-buscar" data-tabla="#tablaPedidos" placeholder="Buscar por número o cliente..." aria-label="Buscar">
                    <select class="form-select ft" data-tabla="#tablaPedidos" data-col="5" aria-label="Estado">
                        <option value="">Todos los estados</option>
                        <?php foreach ($estados as $estadoPedido) { ?><option value="<?php echo $estadoPedido; ?>"><?php echo $estadoPedido; ?></option><?php } ?>
                    </select>
                    <select class="form-select ft" data-tabla="#tablaPedidos" data-col="3" aria-label="Método de pago">
                        <option value="">Todos los métodos</option>
                        <?php foreach ($metodos as $metodoPago) { ?><option value="<?php echo Texto($metodoPago); ?>"><?php echo Texto($metodoPago); ?></option><?php } ?>
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
                            <?php foreach ($pedidos as $pedido) { $siguientes = TransicionesPedido($pedido['EstadoPedido']); ?>
                                <tr>
                                    <td><a href="order.php?id=<?php echo $pedido['Consecutivo']; ?>" class="fw-semibold">#<?php echo $pedido['Consecutivo']; ?></a></td>
                                    <td><?php echo Texto($pedido['NombreCliente']); ?></td>
                                    <td class="text-nowrap" data-valor="<?php echo date('Y-m-d H:i', strtotime($pedido['FechaPedido'])); ?>"><?php echo date('d/m/Y H:i', strtotime($pedido['FechaPedido'])); ?></td>
                                    <td><?php echo Texto($pedido['NombreMetodoPago']); ?></td>
                                    <td class="text-end text-nowrap" data-valor="<?php echo (float) $pedido['Total']; ?>"><?php echo Dinero($pedido['Total']); ?></td>
                                    <td data-valor="<?php echo $pedido['EstadoPedido']; ?>"><span class="badge text-bg-<?php echo ClaseEstadoPedido($pedido['EstadoPedido']); ?>"><?php echo $pedido['EstadoPedido']; ?></span></td>
                                    <td class="text-end text-nowrap">
                                        <a href="order.php?id=<?php echo $pedido['Consecutivo']; ?>" class="btn btn-outline-secondary btn-sm"><i class="ti ti-eye"></i> Detalle</a>
                                        <?php if (count($siguientes) > 0) { ?>
                                            <form action="" method="POST" class="d-inline-flex gap-1 m-0 align-middle">
                                                <?php CampoCsrf(); ?>
                                                <input type="hidden" name="txtConsecutivo" value="<?php echo $pedido['Consecutivo']; ?>">
                                                <select name="txtEstadoPedido" class="form-select form-select-sm select-auto" aria-label="Nuevo estado">
                                                    <?php foreach ($siguientes as $estadoSiguiente) { ?><option value="<?php echo $estadoSiguiente; ?>"><?php echo $estadoSiguiente; ?></option><?php } ?>
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
