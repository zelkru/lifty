<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

/*
 * ConfigController: sesion, mensajes, control de acceso y correo.
 * Lo incluyen todos los demas controladores.
 */
include_once __DIR__ . '/../Model/ConfigModel.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/* Reglas de negocio acordadas por el equipo (ver RF-15). */
const PORCENTAJE_IVA = 0.13;
const COSTO_ENVIO = 2500;
/* Existencias iguales o menores a este numero se consideran stock bajo. */
const UMBRAL_STOCK_BAJO = 5;
const ROL_CLIENTE = 1;
const ROL_ADMINISTRADOR = 2;

/* ---------- Mensajes ---------- */

function AsignarMensaje($texto, $tipo = 'success')
{
    $_SESSION['Mensaje'] = ['Texto' => $texto, 'Tipo' => $tipo];
}

function MostrarMensaje()
{
    if (isset($_SESSION['Mensaje'])) {
        $mensaje = $_SESSION['Mensaje'];
        unset($_SESSION['Mensaje']);

        $iconos = ['success' => 'ti-circle-check', 'danger' => 'ti-alert-circle', 'warning' => 'ti-alert-triangle', 'info' => 'ti-info-circle'];
        $icono = $iconos[$mensaje['Tipo']] ?? 'ti-info-circle';

        echo '<div class="alert alert-' . $mensaje['Tipo'] . ' d-flex align-items-center gap-2" role="alert">'
           . '<i class="ti ' . $icono . ' fs-5"></i><div>' . Texto($mensaje['Texto']) . '</div></div>';
    }
}

/* ---------- Utilidades ---------- */

function Redirigir($url)
{
    header('Location: ' . $url);
    exit();
}

/* Escapa texto para imprimirlo en HTML. */
function Texto($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/* Formato de dinero en colones: 32000 => ₡32.000 */
function Dinero($valor)
{
    return '₡' . number_format((float) $valor, 0, ',', '.');
}

/* Lee un campo de $_POST como texto sin espacios sobrantes. */
function LeerTexto($clave)
{
    return isset($_POST[$clave]) ? trim((string) $_POST[$clave]) : '';
}

function LeerEntero($clave)
{
    return isset($_POST[$clave]) ? (int) $_POST[$clave] : 0;
}

/* ---------- Seguridad de sesion ---------- */

function EstaAutenticado()
{
    return isset($_SESSION['ConsecutivoUsuario']);
}

function EsAdministrador()
{
    return EstaAutenticado() && $_SESSION['ConsecutivoRol'] == ROL_ADMINISTRADOR;
}

function EsCliente()
{
    return EstaAutenticado() && $_SESSION['ConsecutivoRol'] == ROL_CLIENTE;
}

/* Pagina de inicio segun el rol del usuario. */
function PaginaInicioRol()
{
    return EsAdministrador() ? '../Inicio/home.php' : '../Catalogo/home.php';
}

function ValidarSesion()
{
    if (!EstaAutenticado()) {
        Redirigir('../Inicio/login.php');
    }
}

function ValidarAdmin()
{
    ValidarSesion();
    if (!EsAdministrador()) {
        AsignarMensaje('No tiene permiso para ver esa página.', 'warning');
        Redirigir(PaginaInicioRol());
    }
}

function ValidarCliente()
{
    ValidarSesion();
    if (!EsCliente()) {
        AsignarMensaje('Esta sección es solo para clientes.', 'warning');
        Redirigir(PaginaInicioRol());
    }
}

/* Token CSRF: se agrega a cada formulario privado con <?php CampoCsrf(); ?> */
function CampoCsrf()
{
    if (empty($_SESSION['TokenCsrf'])) {
        $_SESSION['TokenCsrf'] = bin2hex(random_bytes(32));
    }
    echo '<input type="hidden" name="tokenCsrf" value="' . $_SESSION['TokenCsrf'] . '">';
}

/* Si un usuario con sesion envia un formulario sin token valido, se descarta el envio completo. */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && EstaAutenticado()) {
    $tokenRecibido = $_POST['tokenCsrf'] ?? '';
    $tokenSesion = $_SESSION['TokenCsrf'] ?? '';

    if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenRecibido)) {
        $_POST = [];
        AsignarMensaje('La sesión del formulario expiró. Intente de nuevo.', 'warning');
    }
}

/* ---------- Correo electronico ---------- */

function GenerarContrasennaTemporal()
{
    $caracteres = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
    $longitud = 8;
    $resultado = '';

    for ($i = 0; $i < $longitud; $i++) {
        $resultado .= $caracteres[random_int(0, strlen($caracteres) - 1)];
    }

    return $resultado;
}

/* Lee una plantilla de View/Template y reemplaza {{Clave}} por su valor. */
function ArmarPlantilla($archivo, $valores)
{
    $plantilla = file_get_contents(__DIR__ . '/../View/Template/' . $archivo);

    foreach ($valores as $clave => $valor) {
        $plantilla = str_replace('{{' . $clave . '}}', Texto($valor), $plantilla);
    }

    return $plantilla;
}

/*
 * Envia un correo por SMTP (Gmail). Las credenciales se toman de las variables de
 * entorno MAIL_USER y MAIL_PASS. Si no estan configuradas el envio se simula y el
 * mensaje queda en el log de errores de PHP (igual que en las practicas del curso).
 */
function EnviarCorreoElectronico($asunto, $contenido, $destinatario)
{
    $correoSalida = getenv('MAIL_USER') ?: '';
    $contrasennaSalida = getenv('MAIL_PASS') ?: '';

    if ($correoSalida == '' || $contrasennaSalida == '') {
        error_log('[Lifty correo simulado] Para: ' . $destinatario . ' | Asunto: ' . $asunto . ' | ' . trim(strip_tags($contenido)));
        return true;
    }

    try {
        require_once __DIR__ . '/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);
        $mail -> CharSet = 'UTF-8';
        $mail -> isSMTP();
        $mail -> Host = 'smtp.gmail.com';
        $mail -> SMTPAuth = true;
        $mail -> Username = $correoSalida;
        $mail -> Password = $contrasennaSalida;
        $mail -> SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail -> Port = 587;

        $mail -> setFrom($correoSalida, 'Lifty');
        $mail -> addAddress($destinatario);
        $mail -> isHTML(true);
        $mail -> Subject = $asunto;
        $mail -> Body = $contenido;

        $mail -> send();
        return true;
    }
    catch (Exception $exc) {
        RegistrarError($exc, 'EnviarCorreoElectronico');
        return false;
    }
}
