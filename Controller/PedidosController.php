<?php
include_once __DIR__ . '/../Model/PedidosModel.php';
include_once __DIR__ . '/../Model/InventarioModel.php';
include_once __DIR__ . '/../Model/PersonalModel.php';
include_once __DIR__ . '/ConfigController.php';

function TransicionesPedido($estadoActual)
{
    $transiciones = [
        'Pendiente' => ['Cancelado'],
        'Pagado'    => ['Enviado', 'Cancelado'],
        'Enviado'   => ['Entregado', 'Cancelado'],
        'Entregado' => [],
        'Cancelado' => []
    ];

    return $transiciones[$estadoActual] ?? [];
}

function ClaseEstadoPedido($estado)
{
    $clases = ['Pendiente' => 'warning', 'Pagado' => 'info', 'Enviado' => 'primary', 'Entregado' => 'success', 'Cancelado' => 'danger'];
    return $clases[$estado] ?? 'secondary';
}

function ConsultarCarrito()
{
    $lineas = [];
    $subtotal = 0;

    foreach (($_SESSION['Carrito'] ?? []) as $variante => $cantidad) {
        $datos = ConsultarVarianteModel($variante);

        if (!$datos || $datos['Estado'] != 1 || $datos['EstadoProducto'] != 1) {
            unset($_SESSION['Carrito'][$variante]);
            continue;
        }

        $datos['Cantidad'] = min((int) $cantidad, max((int) $datos['Stock'], 0));
        $datos['Importe'] = $datos['Cantidad'] * $datos['Precio'];
        $subtotal += $datos['Importe'];
        $lineas[] = $datos;
    }

    $impuesto = round($subtotal * PORCENTAJE_IVA, 2);
    $envio = $subtotal > 0 ? COSTO_ENVIO : 0;

    return ['Lineas' => $lineas, 'Subtotal' => $subtotal, 'Impuesto' => $impuesto, 'Envio' => $envio, 'Total' => $subtotal + $impuesto + $envio];
}

if (isset($_POST['btnAgregarCarrito'])) {
    $variante = LeerEntero('txtVariante');
    $cantidad = LeerEntero('txtCantidad');
    $retorno = '../Catalogo/product.php?id=' . LeerEntero('txtProducto');

    if (!EstaAutenticado()) {
        AsignarMensaje('Inicie sesión o regístrese para agregar productos al carrito.', 'info');
        Redirigir('../Inicio/login.php');
    }

    $datos = ConsultarVarianteModel($variante);
    $enCarrito = $_SESSION['Carrito'][$variante] ?? 0;

    if (!EsCliente()) {
        AsignarMensaje('Solo los clientes pueden comprar.', 'warning');
    }
    else if (!$datos || $datos['Estado'] != 1 || $datos['EstadoProducto'] != 1 || $cantidad < 1) {
        AsignarMensaje('Seleccione una presentación disponible y una cantidad válida.', 'danger');
    }
    else if ($enCarrito + $cantidad > $datos['Stock']) {
        AsignarMensaje('No hay suficiente stock. Disponibles: ' . $datos['Stock'] . ($enCarrito > 0 ? ' (ya tiene ' . $enCarrito . ' en el carrito).' : '.'), 'warning');
    }
    else {
        $_SESSION['Carrito'][$variante] = $enCarrito + $cantidad;
        AsignarMensaje('Se agregó al carrito: ' . $datos['NombreProducto'] . ' (' . $datos['Presentacion'] . ').');
        $retorno = '../Pedidos/cart.php';
    }

    Redirigir($retorno);
}

if (isset($_POST['btnActualizarCarrito'])) {
    $variante = LeerEntero('txtVariante');
    $cantidad = LeerEntero('txtCantidad');
    $datos = ConsultarVarianteModel($variante);

    if (!isset($_SESSION['Carrito'][$variante])) {
        AsignarMensaje('Ese producto no está en el carrito.', 'warning');
    }
    else if ($cantidad < 1) {
        unset($_SESSION['Carrito'][$variante]);
        AsignarMensaje('Se quitó el producto del carrito.');
    }
    else if (!$datos || $cantidad > $datos['Stock']) {
        AsignarMensaje('No hay suficiente stock. Disponibles: ' . ($datos ? $datos['Stock'] : 0) . '.', 'warning');
    }
    else {
        $_SESSION['Carrito'][$variante] = $cantidad;
        AsignarMensaje('Carrito actualizado.');
    }

    Redirigir('cart.php');
}

if (isset($_POST['btnQuitarCarrito'])) {
    unset($_SESSION['Carrito'][LeerEntero('txtVariante')]);
    AsignarMensaje('Se quitó el producto del carrito.');
    Redirigir('cart.php');
}

function ValidarPagoSimulado($nombreMetodo)
{
    if (stripos($nombreMetodo, 'SINPE') !== false) {
        return preg_match('/^[0-9]{8}$/', LeerTexto('txtTelefonoSinpe')) ? '' : 'Ingrese el número de teléfono SINPE Móvil (8 dígitos).';
    }

    $numero = preg_replace('/\s+/', '', LeerTexto('txtNumeroTarjeta'));

    if (!preg_match('/^[0-9]{13,19}$/', $numero)) {
        return 'El número de tarjeta debe tener entre 13 y 19 dígitos.';
    }
    if (LeerTexto('txtTitularTarjeta') == '') {
        return 'Ingrese el nombre del titular de la tarjeta.';
    }
    if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', LeerTexto('txtVencimiento'))) {
        return 'El vencimiento debe tener el formato MM/AA.';
    }
    if (!preg_match('/^[0-9]{3,4}$/', LeerTexto('txtCvv'))) {
        return 'El código de seguridad debe tener 3 o 4 dígitos.';
    }

    return '';
}

function ConsultarMetodoPagoActivo($consecutivo)
{
    foreach (ConsultarMetodosPagoModel(1) as $metodo) {
        if ($metodo['Consecutivo'] == $consecutivo) {
            return $metodo;
        }
    }
    return null;
}

function ProcesarPagoPedido($consecutivoPedido)
{
    if (!PagarPedidoModel($consecutivoPedido, $_SESSION['ConsecutivoUsuario'])) {
        return false;
    }

    $pedido = ConsultarPedidoModel($consecutivoPedido);
    $plantilla = ArmarPlantilla('PedidoPagado.html', [
        'Nombre' => $_SESSION['NombreUsuario'], 'Pedido' => $consecutivoPedido, 'Total' => Dinero($pedido['Total'])
    ]);
    EnviarCorreoElectronico('Pago confirmado del pedido #' . $consecutivoPedido, $plantilla, $_SESSION['CorreoElectronico']);

    return true;
}

if (isset($_POST['btnConfirmarCompra'])) {
    ValidarCliente();

    $carrito = ConsultarCarrito();
    $direccion = LeerEntero('txtDireccion');
    $metodo = ConsultarMetodoPagoActivo(LeerEntero('txtMetodoPago'));
    $direccionValida = false;

    foreach (ConsultarDireccionesModel($_SESSION['ConsecutivoUsuario']) as $d) {
        if ($d['Consecutivo'] == $direccion) {
            $direccionValida = true;
        }
    }

    $errorPago = $metodo ? ValidarPagoSimulado($metodo['Nombre']) : 'Seleccione un método de pago.';
    $sinStock = false;

    foreach ($carrito['Lineas'] as $linea) {
        if ($linea['Cantidad'] != $_SESSION['Carrito'][$linea['Consecutivo']]) {
            $sinStock = true;
        }
    }

    if (count($carrito['Lineas']) == 0) {
        AsignarMensaje('Su carrito está vacío.', 'warning');
        Redirigir('cart.php');
    }
    else if ($sinStock) {
        AsignarMensaje('Algunas cantidades superan el stock disponible. Revise su carrito.', 'warning');
        Redirigir('cart.php');
    }
    else if (!$direccionValida) {
        AsignarMensaje('Seleccione una dirección de entrega.', 'danger');
        Redirigir('cart.php');
    }
    else if ($errorPago != '') {
        AsignarMensaje($errorPago, 'danger');
        Redirigir('cart.php');
    }

    $lineas = [];
    foreach ($carrito['Lineas'] as $linea) {
        $lineas[] = ['Variante' => $linea['Consecutivo'], 'Cantidad' => $linea['Cantidad'], 'Precio' => $linea['Precio']];
    }

    $consecutivoPedido = RegistrarPedidoModel($_SESSION['ConsecutivoUsuario'], $direccion, $metodo['Consecutivo'],
        $carrito['Subtotal'], $carrito['Impuesto'], $carrito['Envio'], $carrito['Total'], $lineas);

    if ($consecutivoPedido == 0) {
        AsignarMensaje('No se pudo crear el pedido. Intente de nuevo.', 'danger');
        Redirigir('cart.php');
    }

    unset($_SESSION['Carrito']);

    if (ProcesarPagoPedido($consecutivoPedido)) {
        AsignarMensaje('¡Pago aprobado! Su pedido #' . $consecutivoPedido . ' quedó registrado como Pagado.');
    }
    else {
        AsignarMensaje('Su pedido #' . $consecutivoPedido . ' quedó Pendiente: no hubo existencias suficientes para cobrarlo.', 'warning');
    }

    Redirigir('order.php?id=' . $consecutivoPedido);
}

if (isset($_POST['btnPagarPedido'])) {
    ValidarCliente();

    $consecutivoPedido = LeerEntero('txtConsecutivo');
    $pedido = ConsultarPedidoModel($consecutivoPedido);

    if (!$pedido || $pedido['ConsecutivoUsuario'] != $_SESSION['ConsecutivoUsuario'] || $pedido['EstadoPedido'] != 'Pendiente') {
        AsignarMensaje('Ese pedido no se puede pagar.', 'danger');
    }
    else if (($errorPago = ValidarPagoSimulado($pedido['NombreMetodoPago'])) != '') {
        AsignarMensaje($errorPago, 'danger');
    }
    else if (ProcesarPagoPedido($consecutivoPedido)) {
        AsignarMensaje('¡Pago aprobado! Su pedido #' . $consecutivoPedido . ' quedó Pagado.');
    }
    else {
        AsignarMensaje('No hay existencias suficientes para cobrar este pedido en este momento.', 'warning');
    }

    Redirigir('order.php?id=' . $consecutivoPedido);
}

function ConsultarPedidos()
{
    return ConsultarPedidosModel(EsAdministrador() ? 0 : $_SESSION['ConsecutivoUsuario']);
}

function ConsultarPedido($consecutivo)
{
    $pedido = ConsultarPedidoModel($consecutivo);

    if ($pedido && (EsAdministrador() || $pedido['ConsecutivoUsuario'] == $_SESSION['ConsecutivoUsuario'])) {
        return $pedido;
    }
    return null;
}

function ConsultarDetallePedido($consecutivo)
{
    return ConsultarDetallePedidoModel($consecutivo);
}

if (isset($_POST['btnCambiarEstadoPedido'])) {
    ValidarAdmin();

    $consecutivo = LeerEntero('txtConsecutivo');
    $nuevoEstado = LeerTexto('txtEstadoPedido');
    $pedido = ConsultarPedidoModel($consecutivo);
    $retorno = LeerTexto('txtRetorno') == 'detalle' ? 'order.php?id=' . $consecutivo : 'orders.php';

    if (!$pedido || !in_array($nuevoEstado, TransicionesPedido($pedido['EstadoPedido']))) {
        AsignarMensaje('Ese cambio de estado no es válido para el pedido.', 'danger');
    }
    else if (ActualizarEstadoPedidoModel($consecutivo, $nuevoEstado)) {
        AsignarMensaje('El pedido #' . $consecutivo . ' pasó a ' . $nuevoEstado . '.');
    }
    else {
        AsignarMensaje('No se pudo cambiar el estado del pedido.', 'danger');
    }

    Redirigir($retorno);
}
