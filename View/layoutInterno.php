<?php
/* Layout de las vistas con barra lateral: catalogo publico y modulos privados. */
include_once __DIR__ . '/../Controller/ConfigController.php';
include_once __DIR__ . '/../Model/InventarioModel.php';

/*
 * La tienda (visitantes, clientes y el catalogo visto por el administrador) usa una barra
 * superior. El menu lateral es solo del panel de administracion.
 */
/* Agrega la fecha del archivo a su direccion para que el navegador no use una copia vieja. */
function Version($ruta)
{
    $archivo = __DIR__ . '/' . $ruta;
    return $ruta . (file_exists($archivo) ? '?v=' . filemtime($archivo) : '');
}

function UsaTienda()
{
    return !EsAdministrador() || basename(dirname($_SERVER['SCRIPT_NAME'])) == 'Catalogo';
}

/*
 * Direccion de la imagen del producto. Acepta una URL completa o el nombre de un archivo
 * guardado en View/assets/img/productos/ (por ejemplo "whey-chocolate.png").
 */
function ImagenUrl($imagen)
{
    if ($imagen == '') {
        return '';
    }
    if (preg_match('#^(https?:)?//#', $imagen) || $imagen[0] == '/') {
        return $imagen;
    }
    return '../assets/img/productos/' . rawurlencode($imagen);
}

/*
 * Imagen del producto: la foto si tiene una. Sin foto se muestra un bloque tipografico
 * con el nombre (nunca un icono), para que el catalogo se vea bien mientras se cargan fotos.
 */
/* Tarjeta de producto del inicio y del catalogo. */
function TarjetaProducto($p)
{
    $agotado = $p['StockTotal'] <= 0 ? '<span class="agotado">Agotado</span>' : '';
    return '
        <a href="product.php?id=' . $p['Consecutivo'] . '" class="pcard rv">
            <span class="etiqueta">' . Texto($p['NombreCategoria']) . '</span>' . $agotado . '
            <div class="escena">' . ArteProducto($p['Imagen'], $p['NombreCategoria'], $p['NombreMarca'], $p['Nombre']) . '</div>
            <div class="cuerpo">
                <div class="marca">' . Texto($p['NombreMarca']) . '</div>
                <div class="nombre">' . Texto($p['Nombre']) . '</div>
                <div class="pie">
                    <div class="precio"><small>Desde</small>' . Dinero($p['PrecioDesde']) . '</div>
                    <span class="agregar"><i class="ti ti-plus"></i> Agregar</span>
                </div>
            </div>
        </a>';
}

function ArteProducto($imagen, $categoria, $marca, $nombre)
{
    if ($imagen != '') {
        return '<img src="' . Texto(ImagenUrl($imagen)) . '" alt="' . Texto($nombre) . '" loading="lazy">';
    }

    $palabra = mb_strtoupper(strtok($nombre, ' '));
    return '<div class="sinfoto" role="img" aria-label="' . Texto($nombre) . '"><span>' . Texto($palabra) . '</span><small>' . Texto(mb_strtoupper($marca)) . '</small></div>';
}

function IncludeCSS($titulo = 'Lifty - Suplementos deportivos')
{
    $tienda = UsaTienda()
        ? '<link rel="stylesheet" href="../' . Version('assets/css/tienda.css') . '">'
        : '<link rel="stylesheet" href="../' . Version('assets/css/admin.css') . '">';

    echo '
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>' . htmlspecialchars($titulo) . '</title>

            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@700;800;900&family=Manrope:wght@400;500;600;700;800&display=swap">
            <link rel="stylesheet" href="../assets/css/main.css">
            <link rel="stylesheet" href="../' . Version('assets/css/lifty.css') . '">
            ' . $tienda . '
        </head>
    ';
}

/* Icono de Tabler segun el nombre de la categoria (si no hay coincidencia, una etiqueta). */
function IconoCategoria($nombre)
{
    $iconos = ['suplement' => 'ti-pill', 'ropa' => 'ti-shirt', 'snack' => 'ti-cookie', 'bebida' => 'ti-bottle', 'accesorio' => 'ti-barbell'];
    foreach ($iconos as $clave => $ti) {
        if (stripos($nombre, $clave) !== false) {
            return $ti;
        }
    }
    return 'ti-tag';
}

/*
 * Barra superior de la tienda: ancho completo y fija, sin menu lateral.
 * Las secciones del menu son las categorias activas de la base de datos (maximo 5).
 */
function MostrarHeaderTienda()
{
    $cantidad = EsCliente() ? count($_SESSION['Carrito'] ?? []) : 0;
    $enCatalogo = basename($_SERVER['SCRIPT_NAME']) == 'catalog.php';
    $categoriaActual = (int) ($_GET['categoria'] ?? 0);

    $enlaces = '';
    foreach (array_slice(ConsultarCategoriasModel(1), 0, 5) as $c) {
        $icono = IconoCategoria($c['Nombre']);
        $activo = ($enCatalogo && $categoriaActual == $c['Consecutivo']) ? ' active' : '';
        $enlaces .= '<li class="nav-item"><a class="nav-link' . $activo . '" href="../Catalogo/catalog.php?categoria=' . $c['Consecutivo'] . '"><i class="ti ' . $icono . '"></i>' . Texto($c['Nombre']) . '</a></li>';
    }

    echo '
        <nav class="navbar navbar-expand-xl tienda-nav">
          <div class="tienda-nav-in">
            <a href="../Catalogo/home.php" class="tienda-logo">LIFTY</a>
            <button class="navbar-toggler border-0 ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#menuTienda" aria-controls="menuTienda" aria-expanded="false" aria-label="Menú">
              <span class="tienda-burger"><i></i><i></i><i></i></span>
            </button>
            <div class="collapse navbar-collapse" id="menuTienda">
              <ul class="navbar-nav">' . $enlaces . '</ul>
              <ul class="navbar-nav ms-xl-auto align-items-xl-center gap-xl-1">';

    if (EsCliente()) {
        echo '<li class="nav-item"><a href="../Pedidos/cart.php" class="nav-link" aria-label="Carrito"><i class="ti ti-shopping-cart fs-5"></i><span class="d-xl-none">Carrito</span>' .
             ($cantidad > 0 ? '<span class="tienda-contador">' . $cantidad . '</span>' : '') . '</a></li>';
    }

    if (EstaAutenticado()) {
        echo '
                <li class="nav-item dropdown">
                  <a href="#" class="nav-link dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="tienda-avatar"><i class="ti ti-user"></i></span>
                    <span class="d-none d-xl-inline">' . Texto($_SESSION['NombreUsuario']) . '</span>
                    <i class="ti ti-chevron-down small d-none d-xl-inline"></i>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end tienda-menu-usuario">
                    <div class="px-3 pt-2 pb-2 small text-secondary">' . Texto($_SESSION['NombreRol']) . '</div>'
                    . (EsAdministrador() ? '<a href="../Inicio/home.php" class="dropdown-item"><i class="ti ti-layout-dashboard"></i> Panel de administración</a>' : '')
                    . (EsCliente() ? '<a href="../Pedidos/orders.php" class="dropdown-item"><i class="ti ti-receipt"></i> Mis pedidos</a>' : '') . '
                    <a href="../Personal/account.php" class="dropdown-item"><i class="ti ti-settings"></i> Mi cuenta</a>
                    <div class="dropdown-divider"></div>
                    <form action="../Inicio/login.php" method="POST" class="m-0">';
        CampoCsrf();
        echo '
                      <button type="submit" name="btnCerrarSesion" class="dropdown-item text-danger"><i class="ti ti-logout"></i> Cerrar sesión</button>
                    </form>
                  </div>
                </li>';
    }
    else {
        echo '
                <li class="nav-item"><a href="../Inicio/login.php" class="tienda-btn tienda-btn-linea"><i class="ti ti-user"></i> Iniciar sesión</a></li>
                <li class="nav-item"><a href="../Inicio/register.php" class="tienda-btn tienda-btn-lleno">Registrarse</a></li>';
    }

    echo '
              </ul>
            </div>
          </div>
        </nav>
    ';
}

function MostrarHeader()
{
    if (UsaTienda()) {
        MostrarHeaderTienda();
        return;
    }

    $cantidad = EsCliente() ? count($_SESSION['Carrito'] ?? []) : 0;

    echo '
          <div id="overlay" class="overlay"></div>
          <nav id="topbar" class="navbar bg-white border-bottom fixed-top topbar px-3">
            <div class="d-flex align-items-center">
              <button id="toggleBtn" class="d-none d-lg-inline-flex btn btn-light btn-icon btn-sm ">
                <i class="ti ti-layout-sidebar-left-expand"></i>
              </button>
              <button id="mobileBtn" class="btn btn-light btn-icon btn-sm d-lg-none me-2">
                <i class="ti ti-layout-sidebar-left-expand"></i>
              </button>
            </div>
            <div>
              <ul class="list-unstyled d-flex align-items-center mb-0 gap-2">';

    if (EsCliente()) {
        echo '
                <li>
                  <a href="../Pedidos/cart.php" class="position-relative btn btn-light btn-icon btn-sm rounded-circle" aria-label="Carrito">
                    <i class="ti ti-shopping-cart"></i>' .
                    ($cantidad > 0 ? '<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">' . $cantidad . '</span>' : '') . '
                  </a>
                </li>';
    }

    if (EstaAutenticado()) {
        echo '
                <li class="ms-2 dropdown">
                  <a href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" class="admin-usuario text-decoration-none">
                    <span class="admin-avatar"><i class="ti ti-user"></i></span>
                    <span class="admin-usuario-texto d-none d-sm-flex"><strong>' . Texto($_SESSION['NombreUsuario']) . '</strong><small>' . Texto($_SESSION['NombreRol']) . '</small></span>
                    <i class="ti ti-chevron-down d-none d-sm-inline"></i>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end p-0" style="min-width: 240px;">
                    <div class="d-flex gap-3 align-items-center border-bottom px-3 py-3">
                      <div class="avatar avatar-md rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0"><i class="ti ti-user"></i></div>
                      <div class="text-truncate">
                        <h4 class="mb-0 small text-truncate">' . Texto($_SESSION['NombreUsuario']) . '</h4>
                        <p class="mb-0 small text-secondary">' . Texto($_SESSION['NombreRol']) . '</p>
                      </div>
                    </div>
                    <div class="p-3 d-flex flex-column gap-2 small">
                      <a href="../Personal/account.php" class="text-decoration-none"><i class="ti ti-settings"></i> Mi cuenta</a>
                      <form action="../Inicio/login.php" method="POST" class="m-0">';
        CampoCsrf();
        echo '
                        <button type="submit" name="btnCerrarSesion" class="btn btn-link link-danger p-0 small text-decoration-none">
                          <i class="ti ti-logout"></i> Cerrar sesión
                        </button>
                      </form>
                    </div>
                  </div>
                </li>';
    }
    else {
        echo '
                <li><a href="../Inicio/login.php" class="btn btn-outline-primary btn-sm">Iniciar sesión</a></li>
                <li><a href="../Inicio/register.php" class="btn btn-primary btn-sm">Registrarse</a></li>';
    }

    echo '
              </ul>
            </div>
          </nav>
    ';
}

function EnlaceMenu($destino, $icono, $texto)
{
    $activo = basename($_SERVER['SCRIPT_NAME']) == basename($destino) ? ' active' : '';
    return '<li><a class="nav-link' . $activo . '" href="' . $destino . '"><i class="ti ' . $icono . '"></i><span class="nav-text">' . $texto . '</span></a></li>';
}

function MostrarSidebar()
{
    if (UsaTienda()) {
        return;
    }

    echo '
        <aside id="sidebar" class="sidebar">
            <div class="logo-area">
                <a href="' . PaginaInicioRol() . '" class="admin-logo text-decoration-none"><span class="logo-text">LIFTY<b>.</b></span></a>
            </div>
            <ul class="nav flex-column">
                <li class="px-4 py-2"><small class="nav-text">Menú</small></li>';

    if (EsAdministrador()) {
        echo EnlaceMenu('../Inicio/home.php', 'ti-home', 'Inicio');
        echo EnlaceMenu('../Inventario/inventory.php', 'ti-building-warehouse', 'Inventario');
        echo EnlaceMenu('../Inventario/catalogs.php', 'ti-tags', 'Categorías y marcas');
        echo EnlaceMenu('../Pedidos/orders.php', 'ti-shopping-cart', 'Pedidos');
        echo EnlaceMenu('../Personal/users.php', 'ti-users', 'Usuarios');
        echo EnlaceMenu('../Reportes/reports.php', 'ti-chart-bar', 'Reportes');
        echo EnlaceMenu('../Catalogo/home.php', 'ti-building-store', 'Ver tienda');
    }
    else {
        echo EnlaceMenu('../Catalogo/catalog.php', 'ti-building-store', 'Catálogo');
        if (EsCliente()) {
            echo EnlaceMenu('../Pedidos/cart.php', 'ti-shopping-cart', 'Mi carrito');
            echo EnlaceMenu('../Pedidos/orders.php', 'ti-receipt', 'Mis pedidos');
        }
    }

    echo '<li class="px-4 pt-4 pb-2"><small class="nav-text">Cuenta</small></li>';

    if (EstaAutenticado()) {
        echo EnlaceMenu('../Personal/account.php', 'ti-user', 'Mi cuenta');
    }
    else {
        echo EnlaceMenu('../Inicio/login.php', 'ti-login', 'Iniciar sesión');
        echo EnlaceMenu('../Inicio/register.php', 'ti-user-plus', 'Registrarse');
    }

    echo '
            </ul>
        </aside>
    ';
}

function MostrarFooter()
{
    if (UsaTienda()) {
        $categorias = '';
        foreach (array_slice(ConsultarCategoriasModel(1), 0, 5) as $c) {
            $categorias .= '<a href="../Catalogo/catalog.php?categoria=' . $c['Consecutivo'] . '">' . Texto($c['Nombre']) . '</a>';
        }

        echo '
        <footer class="tienda-footer">
            <div class="tienda-footer-top">
                <div class="tienda-footer-col">
                    <div class="titulo">Lifty</div>
                    <p>Suplementos, ropa deportiva y snacks para entrenar. Proyecto académico con pagos simulados.</p>
                </div>
                <div class="tienda-footer-col">
                    <div class="titulo">Tienda</div>
                    ' . $categorias . '
                </div>
                <div class="tienda-footer-col">
                    <div class="titulo">Cuenta</div>
                    ' . (EstaAutenticado() ? '<a href="../Personal/account.php">Mi cuenta</a>' : '<a href="../Inicio/login.php">Iniciar sesión</a><a href="../Inicio/register.php">Crear cuenta</a>') . '
                    ' . (EsCliente() ? '<a href="../Pedidos/orders.php">Mis pedidos</a>' : '') . '
                </div>
            </div>
            <div class="tienda-footer-copy"><span>&copy; 2026 Lifty. Todos los derechos reservados.</span><span>Hecho para SC-502, Universidad Fidélitas</span></div>
        </footer>';
        return;
    }

    echo '
        <div class="row">
            <div class="col-12">
                <footer class="text-center py-2 mt-6 text-secondary">
                    <p class="mb-0">Copyright &copy; 2026 Lifty. Suplementos deportivos.</p>
                </footer>
            </div>
        </div>
    ';
}

function IncludeJS()
{
    echo '
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/jquery.validate.min.js"></script>
        <script src="../assets/js/validacion.js"></script>' . (UsaTienda() ? '' : '
        <script src="../' . Version('assets/js/tablas.js') . '"></script>') . '
        <script type="module" src="../assets/js/main.js"></script>' . (UsaTienda() ? '
        <script src="../' . Version('assets/js/tienda.js') . '"></script>' : '') . '
    ';
}
