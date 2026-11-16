<?php
include_once __DIR__ . '/../layoutInterno.php';
include_once __DIR__ . '/../../Controller/PedidosController.php';

ValidarCliente();

$carrito = ConsultarCarrito();
$direcciones = ConsultarDireccionesModel($_SESSION['ConsecutivoUsuario']);
$metodos = ConsultarMetodosPagoModel(1);
$unidades = array_sum(array_column($carrito['Lineas'], 'Cantidad'));
?>

<!DOCTYPE html>
<html lang="es">

<?php IncludeCSS('Mi carrito - Lifty'); ?>

<body>

    <?php MostrarHeader(); ?>
    <?php MostrarSidebar(); ?>

    <main id="content" class="content">
        <div class="container-fluid">

            <div class="page-head">
                <div>
                    <h1>Mi carrito</h1>
                    <p><?php echo $unidades; ?> unidad<?php echo $unidades == 1 ? '' : 'es'; ?> en tu carrito</p>
                </div>
                <a href="../Catalogo/catalog.php#productos" class="btn btn-outline-secondary"><i class="ti ti-arrow-left"></i> Seguir comprando</a>
            </div>

            <?php MostrarMensaje(); ?>

            <?php if (count($carrito['Lineas']) == 0) { ?>
                <div class="l-card empty-state">
                    <i class="ti ti-shopping-cart-off"></i>
                    <p class="fw-bold">Tu carrito está vacío.</p>
                    <a href="../Catalogo/catalog.php#productos" class="btn btn-primary px-4">Explorar el catálogo</a>
                </div>
            <?php } else { ?>
            <div class="carrito-grid">

                <div>
                    <?php foreach ($carrito['Lineas'] as $l) { ?>
                        <div class="item-carrito">
                            <div class="foto"><?php echo ArteProducto($l['Imagen'], '', '', $l['NombreProducto']); ?></div>
                            <div>
                                <a href="../Catalogo/product.php?id=<?php echo $l['ConsecutivoProducto']; ?>" class="nombre"><?php echo Texto($l['NombreProducto']); ?></a>
                                <div class="pres"><?php echo Texto($l['Presentacion']); ?> &middot; <?php echo Dinero($l['Precio']); ?> c/u</div>

                                <form action="" method="POST" class="m-0 form-cantidad">
                                    <?php CampoCsrf(); ?>
                                    <input type="hidden" name="txtVariante" value="<?php echo $l['Consecutivo']; ?>">
                                    <input type="hidden" name="btnActualizarCarrito" value="1">
                                    <div class="paso">
                                        <button type="button" class="menos" aria-label="Menos"><i class="ti ti-minus"></i></button>
                                        <input type="number" name="txtCantidad" value="<?php echo $l['Cantidad']; ?>" min="1" max="<?php echo $l['Stock']; ?>" aria-label="Cantidad">
                                        <button type="button" class="mas" aria-label="Más"><i class="ti ti-plus"></i></button>
                                    </div>
                                    <?php if ($l['Cantidad'] != $_SESSION['Carrito'][$l['Consecutivo']]) { ?>
                                        <div class="small text-danger mt-1">Solo hay <?php echo $l['Stock']; ?> disponibles</div>
                                    <?php } ?>
                                </form>
                            </div>
                            <div class="derecha">
                                <div class="importe"><?php echo Dinero($l['Importe']); ?></div>
                                <form action="" method="POST" class="m-0">
                                    <?php CampoCsrf(); ?>
                                    <input type="hidden" name="txtVariante" value="<?php echo $l['Consecutivo']; ?>">
                                    <button type="submit" name="btnQuitarCarrito" class="quitar"><i class="ti ti-trash"></i> Quitar</button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                    <p class="small text-secondary mt-3"><i class="ti ti-info-circle"></i> Tener productos en el carrito no reserva el stock; se descuenta al pagar.</p>
                </div>

                <form action="" method="POST" class="validar resumen-sticky" novalidate>
                    <?php CampoCsrf(); ?>

                    <div class="l-card l-card-body mb-3">
                        <h2 class="l-card-title">Resumen</h2>
                        <div class="resumen-fila"><span>Subtotal</span><span><?php echo Dinero($carrito['Subtotal']); ?></span></div>
                        <div class="resumen-fila"><span>IVA (13 %)</span><span><?php echo Dinero($carrito['Impuesto']); ?></span></div>
                        <div class="resumen-fila"><span>Envío</span><span><?php echo Dinero($carrito['Envio']); ?></span></div>
                        <div class="resumen-total"><span>Total</span><span><?php echo Dinero($carrito['Total']); ?></span></div>
                    </div>

                    <div class="l-card l-card-body mb-3">
                        <h2 class="l-card-title"><i class="ti ti-map-pin"></i> Entrega</h2>
                        <?php if (count($direcciones) == 0) { ?>
                            <div class="alert alert-warning mb-0">Necesitas una dirección para comprar. <a href="../Personal/account.php" class="alert-link">Agregar dirección</a></div>
                        <?php } else { ?>
                            <select name="txtDireccion" class="form-select" required aria-label="Dirección de entrega">
                                <?php foreach ($direcciones as $d) { ?>
                                    <option value="<?php echo $d['Consecutivo']; ?>"><?php echo Texto($d['Detalle'] . ', ' . $d['Distrito'] . ', ' . $d['Canton']); ?></option>
                                <?php } ?>
                            </select>
                        <?php } ?>
                    </div>

                    <div class="l-card l-card-body mb-3">
                        <h2 class="l-card-title"><i class="ti ti-credit-card"></i> Pago <span class="badge text-bg-light border fw-normal">Simulado</span></h2>
                        <div class="pago-tabs">
                            <?php foreach ($metodos as $i => $m) { $esSinpe = stripos($m['Nombre'], 'SINPE') !== false; ?>
                                <div>
                                    <input class="btn-check metodo-pago" type="radio" name="txtMetodoPago" id="metodo<?php echo $m['Consecutivo']; ?>"
                                           value="<?php echo $m['Consecutivo']; ?>" data-tipo="<?php echo $esSinpe ? 'sinpe' : 'tarjeta'; ?>" <?php echo $i == 0 ? 'checked' : ''; ?> required>
                                    <label for="metodo<?php echo $m['Consecutivo']; ?>"><i class="ti <?php echo $esSinpe ? 'ti-device-mobile' : 'ti-credit-card'; ?>"></i> <?php echo Texto($m['Nombre']); ?></label>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="pay-box" data-tipo="tarjeta">
                            <div class="row g-2">
                                <div class="col-12"><input id="txtNumeroTarjeta" name="txtNumeroTarjeta" type="text" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Número de tarjeta" data-rule-tarjeta="true" required></div>
                                <div class="col-12"><input id="txtTitularTarjeta" name="txtTitularTarjeta" type="text" class="form-control" autocomplete="off" placeholder="Nombre del titular" required></div>
                                <div class="col-6"><input id="txtVencimiento" name="txtVencimiento" type="text" class="form-control" placeholder="MM/AA" maxlength="5" autocomplete="off" data-rule-vencimiento="true" required></div>
                                <div class="col-6"><input id="txtCvv" name="txtCvv" type="password" class="form-control" inputmode="numeric" maxlength="4" autocomplete="off" placeholder="CVV" data-rule-digits="true" data-rule-minlength="3" required></div>
                            </div>
                        </div>
                        <div class="pay-box" data-tipo="sinpe">
                            <input id="txtTelefonoSinpe" name="txtTelefonoSinpe" type="text" class="form-control" inputmode="numeric" maxlength="8" placeholder="Teléfono SINPE Móvil" data-rule-telefono="true" required>
                        </div>
                        <p class="small text-secondary mt-3 mb-0"><i class="ti ti-lock"></i> No se cobra dinero real ni se guardan los datos de pago.</p>
                    </div>

                    <button type="submit" name="btnConfirmarCompra" class="btn btn-primary btn-pagar" <?php echo count($direcciones) == 0 ? 'disabled' : ''; ?>>
                        <i class="ti ti-lock"></i> Pagar <?php echo Dinero($carrito['Total']); ?>
                    </button>
                </form>

            </div>
            <?php } ?>

            <?php MostrarFooter(); ?>

        </div>
    </main>

    <?php IncludeJS(); ?>
    <script>
        $(function () {
            function mostrarPago() {
                var tipo = $('.metodo-pago:checked').data('tipo');
                $('.pay-box').removeClass('active').filter('[data-tipo="' + tipo + '"]').addClass('active');
            }
            $('.metodo-pago').on('change', mostrarPago);
            mostrarPago();

            $('.form-cantidad').each(function () {
                var $f = $(this), $c = $f.find('input[type=number]');
                var max = parseInt($c.attr('max'), 10);
                $f.find('.mas').on('click', function () { if (parseInt($c.val(), 10) < max) { $c.val(parseInt($c.val(), 10) + 1); $f[0].submit(); } });
                $f.find('.menos').on('click', function () { var v = parseInt($c.val(), 10) - 1; $c.val(v < 1 ? 0 : v); $f[0].submit(); });
                $c.on('change', function () { $f[0].submit(); });
            });
        });
    </script>

</body>

</html>
