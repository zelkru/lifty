# Lifty

Lifty es un ecommerce de productos para entrenar: suplementos, bebidas, ropa y
accesorios. El sistema permite administrar el catálogo, el inventario, los pedidos y los
usuarios de la tienda.

Proyecto del curso **Ambiente Web Cliente Servidor** (SC-502). Avance 2.

## Integrantes

- Donald Josue Matarrita Solano
- Gabriel David Moreno Jimenez
- Ignacio Hidalgo Mendez
- Jeferson Andrew Fuentes García

## Stack

- PHP sobre Apache, arquitectura MVC
- MySQL / MariaDB con procedimientos almacenados
- HTML5, CSS3, JavaScript y jQuery (jQuery Validate)
- Bootstrap 5.3 y Tabler Icons
- PHPMailer para el correo

## Cómo correrlo en XAMPP

1. Copiar la carpeta `lifty` dentro de `htdocs`.
2. Iniciar Apache y MySQL desde el panel de XAMPP.
3. Abrir `lifty_bd.sql` en MySQL Workbench y ejecutarlo completo. Crea la base `lifty_bd`,
   las tablas, los procedimientos y los datos de ejemplo.
4. Abrir <http://localhost/lifty>.

La conexión usa por defecto `127.0.0.1:3307`, usuario `root` y sin contraseña. Si su MySQL de
XAMPP usa el puerto 3306, defina `DB_PORT=3306` en el archivo `.env` (ver Configuración).

## Usuarios de ejemplo

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | admin@lifty.com | Admin123* |
| Cliente | cliente@lifty.com | Cliente123* |

## Configuración (.env)

Copiar `.env.example` como `.env` dentro de la carpeta `lifty` y completar los valores. El
archivo es opcional: sin él se usa la conexión de XAMPP (`127.0.0.1:3307`, usuario `root`, sin
contraseña) y los correos se simulan (quedan en el log de errores de PHP).

Para enviar correos de verdad, completar `MAIL_USER`, `MAIL_PASS` (contraseña de aplicación de
Gmail), `MAIL_FROM` y dejar `MAIL_HOST=smtp.gmail.com`. Se usan en el registro, la recuperación
de contraseña y el pago confirmado.

## Estructura

```
lifty/
├── index.php          Punto de entrada (lleva al catálogo)
├── lifty_bd.sql       Base de datos completa
├── Controller/        Controladores (reciben formularios, validan, llaman al Model)
├── Model/             Acceso a datos: solo llamadas a procedimientos almacenados
└── View/
    ├── layoutExterno.php   Layout de las vistas de acceso
    ├── layoutInterno.php   Layout con menú lateral (según el rol)
    ├── Inicio/ Personal/ Catalogo/ Inventario/ Pedidos/ Reportes/
    ├── Template/           Plantillas de correo
    └── assets/             css, js y fonts
```

## Estándar de programación

- Carpetas: un módulo por carpeta en `View/`, con su `Controller/XController.php` y
  `Model/XModel.php`.
- Funciones en PascalCase: `ConsultarUsuarioModel`, `RegistrarProductoModel`. Las del Model
  terminan en `Model`.
- Procedimientos almacenados con prefijo `sp`, tablas con prefijo `t`, llave primaria
  `Consecutivo`, borrado lógico con la tabla `testado` (llave `ConsecutivoEstado`).
- Botones de formulario con prefijo `btn` (`btnRegistro`), campos con prefijo `txt`.
- Todo acceso a la base de datos pasa por `Procedimiento()` de `ConfigModel.php`, que escapa
  los parámetros. Las contraseñas se guardan con `password_hash`.
- Todo formulario privado lleva `<?php CampoCsrf(); ?>`.
- Código autoexplicativo con nombres descriptivos en español; solo se comenta lo que no se
  entiende leyéndolo (reglas de negocio y seguridad).

## Funcionalidades de esta entrega

- Registro, inicio y cierre de sesión, recuperación (contraseña temporal por correo) y cambio
  de contraseña; correo de bienvenida y de pago confirmado.
- Roles Cliente y Administrador con control de acceso por vista.
- Cuenta del usuario y direcciones de entrega.
- Catálogo público con filtros, detalle con presentaciones (variantes) y carrito.
- Compra con IVA de 13 %, envío fijo de ₡2.500 y pago simulado (tarjeta o SINPE Móvil);
  el stock se descuenta al pagar.
- Administrador: productos, presentaciones y stock, categorías, marcas, métodos de pago,
  pedidos con cambio de estado, usuarios y reportes básicos.

## Base de datos

13 tablas normalizadas (prefijo `t`, llave `Consecutivo`) y 46 procedimientos almacenados
(prefijo `sp`). El estado lógico vive en `testado` y las provincias en `tprovincia`. El
diagrama entidad-relación se entrega aparte, en draw.io.

## Flujo de trabajo

Ramas de trabajo (`feat/...`, `fix/...`) hacia `develop` con Pull Request, y de `develop`
hacia `main` para cada entrega. Nunca se hace commit directo a `main`. Commits con el
formato `tipo(alcance): mensaje` en inglés (`feat`, `fix`, `docs`, `style`, `refactor`,
`chore`).

## Créditos

La interfaz parte de la plantilla **InApp Inventory Dashboard** (ThemeWagon, CodesCandy,
licencia MIT), adaptada al tema del proyecto: <https://themewagon.com/themes/inapp/>
