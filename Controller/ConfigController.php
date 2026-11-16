<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

include_once __DIR__ . '/../Model/ConfigModel.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

const PORCENTAJE_IVA = 0.13;
const COSTO_ENVIO = 2500;
const UMBRAL_STOCK_BAJO = 5;
const ROL_CLIENTE = 1;
const ROL_ADMINISTRADOR = 2;

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

function Redirigir($url)
{
    header('Location: ' . $url);
    exit();
}

function Texto($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function Dinero($valor)
{
    return '₡' . number_format((float) $valor, 0, ',', '.');
}

function LeerTexto($clave)
{
    return isset($_POST[$clave]) ? trim((string) $_POST[$clave]) : '';
}

function LeerEntero($clave)
{
    return isset($_POST[$clave]) ? (int) $_POST[$clave] : 0;
}

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

function CampoCsrf()
{
    if (empty($_SESSION['TokenCsrf'])) {
        $_SESSION['TokenCsrf'] = bin2hex(random_bytes(32));
    }
    echo '<input type="hidden" name="tokenCsrf" value="' . $_SESSION['TokenCsrf'] . '">';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && EstaAutenticado()) {
    $tokenRecibido = $_POST['tokenCsrf'] ?? '';
    $tokenSesion = $_SESSION['TokenCsrf'] ?? '';

    if ($tokenSesion === '' || !hash_equals($tokenSesion, $tokenRecibido)) {
        $_POST = [];
        AsignarMensaje('La sesión del formulario expiró. Intente de nuevo.', 'warning');
    }
}

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

function ArmarPlantilla($archivo, $valores)
{
    $plantilla = file_get_contents(__DIR__ . '/../View/Template/' . $archivo);

    foreach ($valores as $clave => $valor) {
        $plantilla = str_replace('{{' . $clave . '}}', Texto($valor), $plantilla);
    }

    return $plantilla;
}

function EnviarCorreoElectronico($asunto, $contenido, $destinatario)
{
    $servidor = getenv('MAIL_HOST') ?: '';
    $usuario = getenv('MAIL_USER') ?: '';
    $contrasenna = getenv('MAIL_PASS') ?: '';

    if ($servidor == '') {
        error_log('[Lifty correo simulado] Para: ' . $destinatario . ' | Asunto: ' . $asunto . ' | ' . trim(strip_tags($contenido)));
        return true;
    }

    $cifrado = strtolower(getenv('MAIL_SECURE') ?: 'tls');
    $remitente = getenv('MAIL_FROM') ?: $usuario;

    try {
        require_once __DIR__ . '/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);
        $mail -> CharSet = 'UTF-8';
        $mail -> isSMTP();
        $mail -> Host = $servidor;
        $mail -> Port = (int) (getenv('MAIL_PORT') ?: 587);
        $mail -> SMTPAuth = $usuario != '';
        $mail -> Username = $usuario;
        $mail -> Password = $contrasenna;
        $mail -> SMTPSecure = $cifrado == 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : ($cifrado == 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : '');
        $mail -> SMTPAutoTLS = $cifrado == 'tls';

        $mail -> setFrom($remitente, getenv('MAIL_FROM_NAME') ?: 'Lifty');
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
