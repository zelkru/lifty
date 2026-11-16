<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/PedidosController.php';

ValidarSesion();

$pedido = ConsultarPedido((int) ($_GET['id'] ?? 0));

if (!$pedido) {
    AsignarMensaje('El pedido no existe.', 'warning');
    Redirigir('orders.php');
}

$detalle = ConsultarDetallePedido($pedido['Consecutivo']);
$esAdmin = EsAdministrador();
$siguientes = TransicionesPedido($pedido['EstadoPedido']);
$esSinpe = EsMetodoSinpe($pedido['NombreMetodoPago']);

$pasos = ['Pendiente', 'Pagado', 'Enviado', 'Entregado'];
$indice = array_search($pedido['EstadoPedido'], $pasos);
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Pedido #' . $pedido['Consecutivo'] . ' - Lifty'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content py-10">
        <div class="container-fluid">

            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="orders.php"><?php echo $esAdmin ? 'Pedidos' : 'Mis pedidos'; ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">#<?php echo $pedido['Consecutivo']; ?></li>
                </ol>
            </nav>

            <div class="page-head">
                <div>
                    <h1>Pedido #<?php echo $pedido['Consecutivo']; ?>
                        <span class="badge fs-6 align-middle text-bg-<?php echo ClaseEstadoPedido($pedido['EstadoPedido']); ?>"><?php echo $pedido['EstadoPedido']; ?></span>
                    </h1>
                    <p>Realizado el <?php echo date('d/m/Y \a \l\a\s H:i', strtotime($pedido['FechaPedido'])); ?></p>
                </div>
                <?php if ($esAdmin && count($siguientes) > 0) { ?>
                    <form action="" method="POST" class="d-flex gap-2 m-0">
                        <?php CampoCsrf(); ?>
                        <input type="hidden" name="txtConsecutivo" value="<?php echo $pedido['Consecutivo']; ?>">
                        <input type="hidden" name="txtRetorno" value="detalle">
                        <select name="txtEstadoPedido" class="form-select" aria-label="Nuevo estado">
                            <?php foreach ($siguientes as $s) { ?><option value="<?php echo $s; ?>"><?php echo $s; ?></option><?php } ?>
                        </select>
                        <button type="submit" name="btnCambiarEstadoPedido" class="btn btn-primary text-nowrap">Cambiar estado</button>
                    </form>
                <?php } ?>
            </div>

            <?php MostrarMensaje(); ?>

            <?php if ($indice !== false) { ?>
                <div class="order-steps mb-4">
                    <?php foreach ($pasos as $i => $paso) { ?>
                        <div class="order-step <?php echo $i < $indice ? 'done' : ($i == $indice ? 'current' : ''); ?>"><?php echo $paso; ?></div>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="row g-4">
                <div class="col-12 col-xl-8">
                    <div class="l-card">
                        <div class="table-responsive">
                            <table class="table l-table">
                                <thead><tr><th>Producto</th><th class="text-end">Precio</th><th class="text-center">Cant.</th><th class="text-end">Importe</th></tr></thead>
                                <tbody>
                                <?php foreach ($detalle as $d) { ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?php echo Texto($d['NombreProducto']); ?></div>
                                            <div class="small text-secondary"><?php echo Texto($d['Presentacion']); ?> &middot; <?php echo Texto($d['Sku']); ?></div>
                                        </td>
                                        <td class="text-end text-nowrap"><?php echo Dinero($d['PrecioUnitario']); ?></td>
                                        <td class="text-center"><?php echo $d['Cantidad']; ?></td>
                                        <td class="text-end text-nowrap"><?php echo Dinero($d['Subtotal']); ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="l-card l-card-body mb-3">
                        <h2 class="l-card-title">Totales</h2>
                        <div class="d-flex justify-content-between mb-1"><span class="text-secondary">Subtotal</span><span><?php echo Dinero($pedido['Subtotal']); ?></span></div>
                        <div class="d-flex justify-content-between mb-1"><span class="text-secondary">IVA (13 %)</span><span><?php echo Dinero($pedido['Impuesto']); ?></span></div>
                        <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Envío</span><span><?php echo Dinero($pedido['CostoEnvio']); ?></span></div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fs-5 fw-bold"><span>Total</span><span><?php echo Dinero($pedido['Total']); ?></span></div>
                    </div>

                    <div class="l-card l-card-body mb-3">
                        <h2 class="l-card-title">Entrega y pago</h2>
                        <?php if ($esAdmin) { ?>
                            <div class="small text-secondary">Cliente</div>
                            <div class="mb-2"><?php echo Texto($pedido['NombreCliente']); ?> &middot; <?php echo Texto($pedido['CorreoElectronico']); ?></div>
                        <?php } ?>
                        <div class="small text-secondary">Dirección</div>
                        <div class="mb-2"><?php echo Texto($pedido['Direccion']); ?></div>
                        <div class="small text-secondary">Método de pago</div>
                        <div><?php echo Texto($pedido['NombreMetodoPago']); ?> <span class="badge text-bg-light border fw-normal">Simulado</span></div>
                    </div>

                    <?php if (!$esAdmin && $pedido['EstadoPedido'] == 'Pendiente') { ?>
                        <form action="" method="POST" class="l-card l-card-body validar" novalidate>
                            <?php CampoCsrf(); ?>
                            <input type="hidden" name="txtConsecutivo" value="<?php echo $pedido['Consecutivo']; ?>">
                            <h2 class="l-card-title">Completar el pago</h2>
                            <?php if ($esSinpe) { ?>
                                <label class="form-label small" for="txtTelefonoSinpe">Teléfono SINPE Móvil</label>
                                <input id="txtTelefonoSinpe" name="txtTelefonoSinpe" type="text" class="form-control mb-3" inputmode="numeric" maxlength="8" data-rule-telefono="true" required>
                            <?php } else { ?>
                                <div class="row g-2 mb-3">
                                    <div class="col-12"><input name="txtNumeroTarjeta" type="text" class="form-control" inputmode="numeric" placeholder="Número de tarjeta" autocomplete="off" data-rule-tarjeta="true" required></div>
                                    <div class="col-12"><input name="txtTitularTarjeta" type="text" class="form-control" placeholder="Titular" autocomplete="off" required></div>
                                    <div class="col-6"><input name="txtVencimiento" type="text" class="form-control" placeholder="MM/AA" maxlength="5" autocomplete="off" data-rule-vencimiento="true" required></div>
                                    <div class="col-6"><input name="txtCvv" type="password" class="form-control" placeholder="CVV" inputmode="numeric" maxlength="4" autocomplete="off" data-rule-digits="true" data-rule-minlength="3" required></div>
                                </div>
                            <?php } ?>
                            <button type="submit" name="btnPagarPedido" class="btn btn-primary w-100">Pagar <?php echo Dinero($pedido['Total']); ?></button>
                        </form>
                    <?php } ?>
                </div>
            </div>

            <?php MostrarFooter(); ?>

        </div>
    </main>

    <?php IncludeJS(); ?>

</body>

</html>
