<?php
include_once __DIR__ . '/../Model/InventarioModel.php';
include_once __DIR__ . '/ConfigController.php';

function ValidarAccionAdmin()
{
    if (!EsAdministrador()) {
        AsignarMensaje('No tiene permiso para esta acción.', 'danger');
        Redirigir('../Inicio/login.php');
    }
}

function ConsultarProductosAdmin()
{
    return ConsultarProductosAdminModel();
}

function ConsultarProducto($consecutivo)
{
    return ConsultarProductoModel($consecutivo);
}

function ConsultarVariantes($consecutivoProducto, $soloActivas = false)
{
    return ConsultarVariantesModel($consecutivoProducto, $soloActivas ? 1 : 0);
}

function ConsultarCategorias($soloActivas = false)
{
    return ConsultarCategoriasModel($soloActivas ? 1 : 0);
}

function ConsultarMarcas($soloActivas = false)
{
    return ConsultarMarcasModel($soloActivas ? 1 : 0);
}

function ConsultarMetodosPago($soloActivos = false)
{
    return ConsultarMetodosPagoModel($soloActivos ? 1 : 0);
}

function LeerProducto()
{
    return [
        LeerEntero('txtCategoria'), LeerEntero('txtMarca'),
        LeerTexto('txtNombre'), LeerTexto('txtDescripcion'), LeerTexto('txtImagen')
    ];
}

function ProductoValido($datosProducto)
{
    return $datosProducto[0] > 0 && $datosProducto[1] > 0 && $datosProducto[2] != '' && strlen($datosProducto[2]) <= 100 && $datosProducto[3] != ''
        && ($datosProducto[4] == '' || filter_var($datosProducto[4], FILTER_VALIDATE_URL) || preg_match('/^[\w.\- ]+\.(png|jpe?g|webp|avif|gif)$/i', $datosProducto[4]));
}

if (isset($_POST['btnRegistrarProducto'])) {
    ValidarAccionAdmin();
    $datosProducto = LeerProducto();

    if (!ProductoValido($datosProducto)) {
        AsignarMensaje('Complete nombre, descripción, categoría y marca. La imagen debe ser una dirección web o el nombre de un archivo (png, jpg, webp).', 'danger');
        Redirigir('inventory.php');
    }

    $consecutivo = RegistrarProductoModel($datosProducto[0], $datosProducto[1], $datosProducto[2], $datosProducto[3], $datosProducto[4]);

    if ($consecutivo > 0) {
        AsignarMensaje('Producto creado. Ahora agregue sus presentaciones (variantes).');
        Redirigir('variants.php?id=' . $consecutivo);
    }

    AsignarMensaje('El producto no se ha podido registrar.', 'danger');
    Redirigir('inventory.php');
}

if (isset($_POST['btnActualizarProducto'])) {
    ValidarAccionAdmin();
    $datosProducto = LeerProducto();
    $consecutivo = LeerEntero('txtConsecutivo');
    $estado = LeerEntero('txtEstado') == 1 ? 1 : 0;
    $destacado = LeerEntero('txtDestacado') == 1 ? 1 : 0;
    $retorno = LeerTexto('txtRetorno') == 'variants' ? 'variants.php?id=' . $consecutivo : 'inventory.php';

    if (!ProductoValido($datosProducto)) {
        AsignarMensaje('Complete nombre, descripción, categoría y marca. La imagen debe ser una dirección web o el nombre de un archivo (png, jpg, webp).', 'danger');
    }
    else if (ActualizarProductoModel($consecutivo, $datosProducto[0], $datosProducto[1], $datosProducto[2], $datosProducto[3], $datosProducto[4], $estado, $destacado)) {
        AsignarMensaje('El producto se ha actualizado correctamente.');
    }
    else {
        AsignarMensaje('El producto no se ha podido actualizar.', 'danger');
    }

    Redirigir($retorno);
}

function LeerVariante()
{
    return [LeerTexto('txtSku'), LeerTexto('txtPresentacion'), LeerTexto('txtPrecio'), LeerTexto('txtStock')];
}

function VarianteValida($datosVariante)
{
    return $datosVariante[0] != '' && strlen($datosVariante[0]) <= 30 && $datosVariante[1] != '' && strlen($datosVariante[1]) <= 100
        && is_numeric($datosVariante[2]) && (float) $datosVariante[2] > 0
        && ctype_digit($datosVariante[3]);
}

if (isset($_POST['btnRegistrarVariante'])) {
    ValidarAccionAdmin();
    $producto = LeerEntero('txtProducto');
    $datosVariante = LeerVariante();

    if (!VarianteValida($datosVariante)) {
        AsignarMensaje('Verifique SKU, presentación, precio mayor a cero y stock (número entero).', 'danger');
    }
    else {
        $response = RegistrarVarianteModel($producto, strtoupper($datosVariante[0]), $datosVariante[1], $datosVariante[2], $datosVariante[3]);

        if ($response === -1) {
            AsignarMensaje('Ese SKU ya existe. Use uno distinto.', 'danger');
        }
        else if ($response) {
            AsignarMensaje('La presentación se ha registrado correctamente.');
        }
        else {
            AsignarMensaje('La presentación no se ha podido registrar.', 'danger');
        }
    }

    Redirigir('variants.php?id=' . $producto);
}

if (isset($_POST['btnActualizarVariante'])) {
    ValidarAccionAdmin();
    $producto = LeerEntero('txtProducto');
    $datosVariante = LeerVariante();
    $estado = LeerEntero('txtEstado') == 1 ? 1 : 0;

    if (!VarianteValida($datosVariante)) {
        AsignarMensaje('Verifique SKU, presentación, precio mayor a cero y stock (número entero).', 'danger');
    }
    else {
        $response = ActualizarVarianteModel(LeerEntero('txtConsecutivo'), strtoupper($datosVariante[0]), $datosVariante[1], $datosVariante[2], $datosVariante[3], $estado);

        if ($response === -1) {
            AsignarMensaje('Ese SKU ya existe. Use uno distinto.', 'danger');
        }
        else if ($response) {
            AsignarMensaje('La presentación se ha actualizado correctamente.');
        }
        else {
            AsignarMensaje('La presentación no se ha podido actualizar.', 'danger');
        }
    }

    Redirigir('variants.php?id=' . $producto);
}

function GuardarMaestro($tipo, $registrar, $actualizar)
{
    ValidarAccionAdmin();
    $nombre = LeerTexto('txtNombre');
    $descripcion = LeerTexto('txtDescripcion');
    $consecutivo = LeerEntero('txtConsecutivo');

    if ($nombre == '' || strlen($nombre) > 50 || strlen($descripcion) > 150) {
        AsignarMensaje("El nombre es obligatorio (máximo 50 caracteres) y la descripción admite hasta 150.", 'danger');
    }
    else {
        $response = $consecutivo > 0
            ? $actualizar($consecutivo, $nombre, $descripcion, LeerEntero('txtEstado') == 1 ? 1 : 0)
            : $registrar($nombre, $descripcion);

        if ($response === -1) {
            AsignarMensaje("Ya existe $tipo con ese nombre.", 'danger');
        }
        else if ($response) {
            AsignarMensaje('Los cambios se guardaron correctamente.');
        }
        else {
            AsignarMensaje('No se pudieron guardar los cambios.', 'danger');
        }
    }

    Redirigir('catalogs.php');
}

if (isset($_POST['btnGuardarCategoria'])) {
    GuardarMaestro('una categoría', 'RegistrarCategoriaModel', 'ActualizarCategoriaModel');
}

if (isset($_POST['btnGuardarMarca'])) {
    GuardarMaestro('una marca', 'RegistrarMarcaModel', 'ActualizarMarcaModel');
}

if (isset($_POST['btnCambiarEstadoMetodoPago'])) {
    ValidarAccionAdmin();
    $estado = LeerEntero('txtEstado') == 1 ? 1 : 0;

    if (ActualizarEstadoMetodoPagoModel(LeerEntero('txtConsecutivo'), $estado)) {
        AsignarMensaje($estado == 1 ? 'El método de pago se activó.' : 'El método de pago se inactivó.');
    }
    else {
        AsignarMensaje('No se pudo cambiar el método de pago.', 'danger');
    }

    Redirigir('catalogs.php');
}
