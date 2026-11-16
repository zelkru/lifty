<?php
include_once __DIR__ . '/../Model/InventarioModel.php';
include_once __DIR__ . '/ConfigController.php';

function ConsultarCatalogo($categoria, $marca, $busqueda, $limite = 12, $desplazamiento = 0)
{
    return ConsultarCatalogoModel($categoria, $marca, $busqueda, $limite, $desplazamiento);
}

function ContarCatalogo($categoria, $marca, $busqueda)
{
    return ContarCatalogoModel($categoria, $marca, $busqueda);
}

function ConsultarFiltrosCategorias()
{
    return ConsultarCategoriasModel(1);
}

function ConsultarFiltrosMarcas()
{
    return ConsultarMarcasModel(1);
}

function ConsultarProductoPublico($consecutivo)
{
    $producto = ConsultarProductoModel($consecutivo);
    return ($producto && $producto['Estado'] == 1) ? $producto : null;
}

function ConsultarVariantesPublicas($consecutivoProducto)
{
    return ConsultarVariantesModel($consecutivoProducto, 1);
}
