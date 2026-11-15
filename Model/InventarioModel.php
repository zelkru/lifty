<?php
include_once __DIR__ . '/ConfigModel.php';

/* ---------- Categorias y marcas ---------- */

function ConsultarCategoriasModel($soloActivas)
{
    try {
        $context = Conectar();
        $data = Procedimiento($context, 'spConsultarCategorias', [(int) $soloActivas]);
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarCategoriasModel');
        return [];
    }
}

/* Devuelve true, o -1 si el nombre ya existe. */
function RegistrarCategoriaModel($nombre, $descripcion)
{
    try {
        $context = Conectar();
        $fila = ProcedimientoFila($context, 'spRegistrarCategoria', [$nombre, $descripcion]);
        Cerrar($context);
        return $fila !== null;
    }
    catch (Exception $exc) {
        if ($exc -> getCode() == 1062) {
            return -1;
        }
        RegistrarError($exc, 'RegistrarCategoriaModel');
        return false;
    }
}

function ActualizarCategoriaModel($consecutivo, $nombre, $descripcion, $estado)
{
    try {
        $context = Conectar();
        $actualizado = ProcedimientoBooleano($context, 'spActualizarCategoria', [(int) $consecutivo, $nombre, $descripcion, (int) $estado]);
        Cerrar($context);
        return $actualizado;
    }
    catch (Exception $exc) {
        if ($exc -> getCode() == 1062) {
            return -1;
        }
        RegistrarError($exc, 'ActualizarCategoriaModel');
        return false;
    }
}

function ConsultarMarcasModel($soloActivas)
{
    try {
        $context = Conectar();
        $data = Procedimiento($context, 'spConsultarMarcas', [(int) $soloActivas]);
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarMarcasModel');
        return [];
    }
}

function RegistrarMarcaModel($nombre, $descripcion)
{
    try {
        $context = Conectar();
        $fila = ProcedimientoFila($context, 'spRegistrarMarca', [$nombre, $descripcion]);
        Cerrar($context);
        return $fila !== null;
    }
    catch (Exception $exc) {
        if ($exc -> getCode() == 1062) {
            return -1;
        }
        RegistrarError($exc, 'RegistrarMarcaModel');
        return false;
    }
}

function ActualizarMarcaModel($consecutivo, $nombre, $descripcion, $estado)
{
    try {
        $context = Conectar();
        $actualizado = ProcedimientoBooleano($context, 'spActualizarMarca', [(int) $consecutivo, $nombre, $descripcion, (int) $estado]);
        Cerrar($context);
        return $actualizado;
    }
    catch (Exception $exc) {
        if ($exc -> getCode() == 1062) {
            return -1;
        }
        RegistrarError($exc, 'ActualizarMarcaModel');
        return false;
    }
}

/* ---------- Metodos de pago ---------- */

function ConsultarMetodosPagoModel($soloActivos)
{
    try {
        $context = Conectar();
        $data = Procedimiento($context, 'spConsultarMetodosPago', [(int) $soloActivos]);
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarMetodosPagoModel');
        return [];
    }
}

function ActualizarEstadoMetodoPagoModel($consecutivo, $estado)
{
    try {
        $context = Conectar();
        $actualizado = ProcedimientoBooleano($context, 'spActualizarEstadoMetodoPago', [(int) $consecutivo, (int) $estado]);
        Cerrar($context);
        return $actualizado;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ActualizarEstadoMetodoPagoModel');
        return false;
    }
}

/* ---------- Productos ---------- */

function ConsultarProductosAdminModel()
{
    try {
        $context = Conectar();
        $data = Procedimiento($context, 'spConsultarProductosAdmin');
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarProductosAdminModel');
        return [];
    }
}

function ConsultarProductoModel($consecutivo)
{
    try {
        $context = Conectar();
        $data = ProcedimientoFila($context, 'spConsultarProducto', [(int) $consecutivo]);
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarProductoModel');
        return null;
    }
}

function RegistrarProductoModel($categoria, $marca, $nombre, $descripcion, $imagen)
{
    try {
        $context = Conectar();
        $fila = ProcedimientoFila($context, 'spRegistrarProducto', [(int) $categoria, (int) $marca, $nombre, $descripcion, $imagen]);
        Cerrar($context);
        return $fila ? (int) $fila['Consecutivo'] : 0;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'RegistrarProductoModel');
        return 0;
    }
}

function ActualizarProductoModel($consecutivo, $categoria, $marca, $nombre, $descripcion, $imagen, $estado, $destacado)
{
    try {
        $context = Conectar();
        $actualizado = ProcedimientoBooleano($context, 'spActualizarProducto',
            [(int) $consecutivo, (int) $categoria, (int) $marca, $nombre, $descripcion, $imagen, (int) $estado, (int) $destacado]);
        Cerrar($context);
        return $actualizado;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ActualizarProductoModel');
        return false;
    }
}

/* ---------- Variantes (presentaciones con precio y stock) ---------- */

function ConsultarVariantesModel($consecutivoProducto, $soloActivas)
{
    try {
        $context = Conectar();
        $data = Procedimiento($context, 'spConsultarVariantes', [(int) $consecutivoProducto, (int) $soloActivas]);
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarVariantesModel');
        return [];
    }
}

function ConsultarVarianteModel($consecutivo)
{
    try {
        $context = Conectar();
        $data = ProcedimientoFila($context, 'spConsultarVariante', [(int) $consecutivo]);
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarVarianteModel');
        return null;
    }
}

/* Devuelve true, o -1 si el SKU ya existe. */
function RegistrarVarianteModel($consecutivoProducto, $sku, $presentacion, $precio, $stock)
{
    try {
        $context = Conectar();
        $fila = ProcedimientoFila($context, 'spRegistrarVariante',
            [(int) $consecutivoProducto, $sku, $presentacion, (float) $precio, (int) $stock]);
        Cerrar($context);
        return $fila !== null;
    }
    catch (Exception $exc) {
        if ($exc -> getCode() == 1062) {
            return -1;
        }
        RegistrarError($exc, 'RegistrarVarianteModel');
        return false;
    }
}

function ActualizarVarianteModel($consecutivo, $sku, $presentacion, $precio, $stock, $estado)
{
    try {
        $context = Conectar();
        $actualizado = ProcedimientoBooleano($context, 'spActualizarVariante',
            [(int) $consecutivo, $sku, $presentacion, (float) $precio, (int) $stock, (int) $estado]);
        Cerrar($context);
        return $actualizado;
    }
    catch (Exception $exc) {
        if ($exc -> getCode() == 1062) {
            return -1;
        }
        RegistrarError($exc, 'ActualizarVarianteModel');
        return false;
    }
}

/* ---------- Catalogo publico ---------- */

function ConsultarCatalogoModel($categoria, $marca, $busqueda, $limite = 12, $desplazamiento = 0)
{
    try {
        $context = Conectar();
        $data = Procedimiento($context, 'spConsultarCatalogo', [(int) $categoria, (int) $marca, $busqueda, (int) $limite, (int) $desplazamiento]);
        Cerrar($context);
        return $data;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ConsultarCatalogoModel');
        return [];
    }
}

function ContarCatalogoModel($categoria, $marca, $busqueda)
{
    try {
        $context = Conectar();
        $fila = ProcedimientoFila($context, 'spContarCatalogo', [(int) $categoria, (int) $marca, $busqueda]);
        Cerrar($context);
        return $fila ? (int) $fila['Total'] : 0;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'ContarCatalogoModel');
        return 0;
    }
}
