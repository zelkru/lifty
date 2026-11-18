<?php
include_once __DIR__ . '/../Model/InventarioModel.php';
include_once __DIR__ . '/ConfigController.php';

const PRODUCTOS_POR_PAGINA = 12;
const PRODUCTOS_POR_CATEGORIA = 4;

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

function ConsultarPaginaCatalogo()
{
    $busqueda = trim($_GET['q'] ?? '');
    $categoria = (int) ($_GET['categoria'] ?? 0);
    $marca = (int) ($_GET['marca'] ?? 0);
    $categorias = ConsultarFiltrosCategorias();

    $total = ContarCatalogo($categoria, $marca, $busqueda);
    $paginas = max(1, (int) ceil($total / PRODUCTOS_POR_PAGINA));
    $pagina = min(max(1, (int) ($_GET['pagina'] ?? 1)), $paginas);

    $nombreCategoria = '';
    foreach ($categorias as $categoriaDisponible) {
        if ($categoriaDisponible['Consecutivo'] == $categoria) {
            $nombreCategoria = $categoriaDisponible['Nombre'];
        }
    }

    return [
        'Busqueda' => $busqueda,
        'Categoria' => $categoria,
        'Marca' => $marca,
        'Pagina' => $pagina,
        'Paginas' => $paginas,
        'Total' => $total,
        'Desde' => ($pagina - 1) * PRODUCTOS_POR_PAGINA + 1,
        'Hasta' => min($pagina * PRODUCTOS_POR_PAGINA, $total),
        'Categorias' => $categorias,
        'Marcas' => ConsultarFiltrosMarcas(),
        'NombreCategoria' => $nombreCategoria,
        'HayFiltros' => $busqueda != '' || $categoria > 0 || $marca > 0,
        'Productos' => ConsultarCatalogo($categoria, $marca, $busqueda, PRODUCTOS_POR_PAGINA, ($pagina - 1) * PRODUCTOS_POR_PAGINA)
    ];
}

function UrlCatalogo($cambios)
{
    $parametros = array_merge(['q' => $_GET['q'] ?? '', 'categoria' => $_GET['categoria'] ?? 0, 'marca' => $_GET['marca'] ?? 0, 'pagina' => 1], $cambios);
    $parametros = array_filter($parametros, function ($valor) { return $valor !== '' && $valor !== 0 && $valor !== '0'; });
    if (isset($parametros['pagina']) && $parametros['pagina'] == 1) {
        unset($parametros['pagina']);
    }
    return 'catalog.php' . (count($parametros) ? '?' . http_build_query($parametros) : '');
}

function ConsultarSeccionesInicio()
{
    $secciones = [];
    foreach (ConsultarFiltrosCategorias() as $categoriaDisponible) {
        $secciones[] = [
            'Categoria' => $categoriaDisponible,
            'Total' => ContarCatalogo($categoriaDisponible['Consecutivo'], 0, ''),
            'Productos' => ConsultarCatalogo($categoriaDisponible['Consecutivo'], 0, '', PRODUCTOS_POR_CATEGORIA, 0)
        ];
    }
    return $secciones;
}

function ConsultarDetalleProducto($consecutivo)
{
    $producto = ConsultarProductoPublico($consecutivo);
    if (!$producto) {
        return null;
    }

    $producto['Variantes'] = ConsultarVariantesPublicas($producto['Consecutivo']);
    $producto['HayStock'] = array_sum(array_column($producto['Variantes'], 'Stock')) > 0;
    return $producto;
}
