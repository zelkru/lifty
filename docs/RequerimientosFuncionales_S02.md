# Requerimientos Funcionales y Casos de Uso de Lifty (versión S02)

Escuela de Ingeniería en Sistemas de Computación, Universidad Fidélitas. Curso SC-502.
Jeferson Andrew Fuentes García, Ignacio Hidalgo Méndez, Donald Josué Matarrita Solano,
Gabriel David Moreno Jiménez.

Esta versión amplía los 24 requerimientos de la entrega 1 siguiendo la observación del
profesor ("los requerimientos se deben detallar o ampliar mucho más"). Se conservan los
identificadores RF-01 a RF-24 y se agregan, para cada uno, el actor, las precondiciones, el
comportamiento esperado, las reglas de negocio, las validaciones con sus mensajes, los
criterios de aceptación verificables y su estado de implementación en esta entrega.

## 1. Alcance

Lifty es un comercio electrónico ficticio de productos para entrenar. Con datos de ejemplo y
pagos simulados. Queda **fuera del alcance**: pasarelas de pago reales, facturación
electrónica, cupones y descuentos, reseñas de productos, lista de deseos, múltiples
almacenes, cálculo de envío por zona y devoluciones.

## 2. Actores

| Actor | Descripción |
|---|---|
| Visitante | Persona sin sesión. Consulta el catálogo, se registra, inicia sesión y recupera su contraseña. |
| Cliente | Usuario con rol Cliente. Compra, administra sus direcciones y consulta sus pedidos. |
| Administrador | Usuario con rol Administrador. Gestiona catálogo, inventario, pedidos y usuarios. |
| Sistema de correo | Componente (PHPMailer sobre SMTP de Gmail) que envía las notificaciones. |

## 3. Reglas de negocio transversales

| ID | Regla |
|---|---|
| RN-01 | Los precios se expresan en colones costarricenses (CRC) y se muestran sin decimales (₡32.000). |
| RN-02 | IVA = 13 % del subtotal, redondeado a 2 decimales. |
| RN-03 | Costo de envío fijo de ₡2.500 por pedido con al menos una línea. |
| RN-04 | Total = subtotal + IVA + envío. Los montos se calculan en el servidor con los precios vigentes en la base de datos, nunca con valores enviados por el navegador. |
| RN-05 | El precio unitario se congela en el detalle del pedido al crearlo. |
| RN-06 | El stock se descuenta únicamente al aprobar el pago, de forma atómica (transacción). Agregar al carrito o crear el pedido no descuenta stock. |
| RN-07 | Borrado lógico: nada se elimina; las entidades se inactivan con el campo Estado (1 activo, 0 inactivo). |
| RN-08 | Las contraseñas se almacenan con hash (`password_hash`, bcrypt). Nunca se guardan ni se envían en texto plano, salvo la contraseña temporal de recuperación, que viaja una sola vez por correo y se almacena con hash. |
| RN-09 | Contraseña válida: mínimo 8 caracteres, con al menos una letra y un número. |
| RN-10 | Teléfono válido: exactamente 8 dígitos (formato de Costa Rica). |
| RN-11 | Todo formulario valida en el cliente (jQuery Validate) y se vuelve a validar en el servidor. |
| RN-12 | Todo formulario de usuarios con sesión lleva un token anti-CSRF; un envío sin token válido se descarta. |
| RN-13 | Los errores técnicos se registran en la tabla `terror` y el usuario ve un mensaje genérico. |

## 4. Requerimientos funcionales

### A. Seguridad y usuarios

#### RF-01 Registro de usuario (Alta)

- **Actor:** Visitante.
- **Descripción:** el visitante crea una cuenta de cliente con nombre, primer apellido, segundo apellido (opcional), correo electrónico, teléfono y contraseña con confirmación.
- **Precondición:** no tener sesión iniciada; el correo no está registrado.
- **Comportamiento:** el sistema valida los datos, asigna el rol Cliente, guarda la contraseña con hash, envía un correo de bienvenida y redirige al inicio de sesión con un mensaje de éxito.
- **Validaciones y mensajes:**
  - Nombre y primer apellido obligatorios, máximo 50 caracteres: "Este campo es obligatorio."
  - Correo con formato válido y único: "Ingrese un correo electrónico válido." / "Ese correo electrónico ya está registrado."
  - Teléfono de 8 dígitos: "El teléfono debe tener 8 dígitos."
  - Contraseña según RN-09: "Mínimo 8 caracteres, con letras y números."
  - Confirmación igual a la contraseña: "Las contraseñas no coinciden."
- **Criterios de aceptación:**
  1. Con datos válidos se crea un usuario con rol Cliente y estado activo.
  2. Registrar un correo existente (sin distinguir mayúsculas) no crea otro usuario y muestra el mensaje de duplicado.
  3. La contraseña guardada en la base de datos empieza con `$2y$` (hash bcrypt).
  4. Se envía (o se simula) un correo de bienvenida al correo registrado.
- **Estado S02:** implementado (`View/Inicio/register.php`, `InicioController`, `spRegistrarUsuario`).

#### RF-02 Inicio de sesión (Alta)

- **Actor:** Visitante, Cliente, Administrador.
- **Descripción:** el usuario inicia sesión con correo y contraseña.
- **Precondición:** la cuenta existe y está activa.
- **Comportamiento:** el sistema busca la cuenta por correo, verifica la contraseña contra el hash, regenera el identificador de sesión y guarda en sesión el consecutivo, nombre, correo y rol. Redirige al panel (Administrador) o al catálogo (Cliente).
- **Validaciones y mensajes:** correo y contraseña obligatorios. Ante credenciales inválidas o cuenta inactiva: "Correo o contraseña incorrectos, o la cuenta está inactiva." (el mensaje no revela cuál de los dos falló).
- **Criterios de aceptación:**
  1. Un usuario activo con credenciales correctas entra y llega a la página de su rol.
  2. Un usuario inactivo no puede entrar aunque la contraseña sea correcta.
  3. Un usuario con sesión que abre la pantalla de inicio de sesión es redirigido a su página de inicio.
- **Estado S02:** implementado.

#### RF-03 Cierre de sesión (Alta)

- **Actor:** Cliente, Administrador.
- **Descripción:** el usuario cierra su sesión desde el menú de su cuenta.
- **Comportamiento:** el sistema destruye la sesión (incluido el carrito) y redirige al inicio de sesión con el mensaje "Cerró sesión correctamente."
- **Criterios de aceptación:** después de cerrar sesión, abrir cualquier vista privada redirige al inicio de sesión; el botón "atrás" del navegador no permite ejecutar acciones privadas porque los formularios exigen token vigente.
- **Estado S02:** implementado.

#### RF-04 Recuperación de contraseña (Alta)

- **Actor:** Visitante.
- **Descripción:** el usuario que olvidó su contraseña indica su correo y recibe una contraseña temporal.
- **Decisión de alcance:** se reemplaza el token con vencimiento de la entrega 1 por una contraseña temporal de 8 caracteres, que es el mecanismo enseñado en el curso y evita una tabla adicional. El usuario debe cambiarla al ingresar.
- **Comportamiento:** el sistema genera la contraseña temporal con `random_int`, la guarda con hash y envía el correo con la plantilla de recuperación. La respuesta en pantalla es siempre la misma exista o no el correo ("Si el correo está registrado, le enviamos una contraseña temporal"), para no revelar qué cuentas existen.
- **Criterios de aceptación:**
  1. Para un correo activo, la contraseña anterior deja de funcionar y la temporal sí permite iniciar sesión.
  2. Para un correo inexistente se muestra el mismo mensaje y no se modifica nada.
  3. Una cuenta inactiva no recibe contraseña temporal.
- **Estado S02:** implementado.

#### RF-05 Cambio de contraseña (Alta)

- **Actor:** Cliente, Administrador.
- **Descripción:** el usuario autenticado cambia su contraseña indicando la actual, la nueva y su confirmación (vista Mi cuenta).
- **Validaciones y mensajes:** "La contraseña actual no es correcta."; la nueva debe cumplir RN-09; la confirmación debe coincidir.
- **Criterios de aceptación:** con la actual correcta y una nueva válida, el siguiente inicio de sesión solo funciona con la nueva; con la actual incorrecta no cambia nada.
- **Estado S02:** implementado.

#### RF-06 Control de acceso por rol (Alta)

- **Actor:** todos.
- **Descripción:** existen dos roles (Cliente y Administrador) y cada vista valida la sesión y el rol antes de mostrarse. El menú lateral solo muestra lo que el rol puede usar.
- **Reglas:** visitante: catálogo, detalle, registro, inicio de sesión y recuperación. Cliente: catálogo, carrito, mis pedidos, mi cuenta. Administrador: panel, inventario, categorías y marcas, pedidos, usuarios, reportes y mi cuenta.
- **Criterios de aceptación:**
  1. Un visitante que abre una vista privada es enviado al inicio de sesión.
  2. Un cliente que abre una vista de administrador es enviado al catálogo con el mensaje "No tiene permiso para ver esa página."
  3. Las acciones de administración (POST) también validan el rol en el servidor, no solo el menú.
- **Estado S02:** implementado.

#### RF-07 Gestión de direcciones (Media)

- **Actor:** Cliente.
- **Descripción:** el cliente registra, edita y elimina (inactiva) sus direcciones de entrega: provincia, cantón, distrito, señas y código postal opcional.
- **Reglas:** un cliente solo ve y modifica sus propias direcciones (la consulta y la actualización filtran por usuario). Una dirección inactiva no aparece al comprar, pero se conserva en los pedidos anteriores.
- **Validaciones:** provincia, cantón, distrito y señas obligatorios; código postal numérico de 4 a 10 dígitos si se indica.
- **Criterios de aceptación:** se puede crear, editar e inactivar; el carrito solo ofrece direcciones activas del propio cliente; sin direcciones no se puede confirmar la compra y se muestra el enlace para agregar una.
- **Estado S02:** implementado.

#### RF-08 Gestión de usuarios (Media)

- **Actor:** Administrador.
- **Descripción:** el administrador lista todos los usuarios y activa o inactiva el acceso (borrado lógico). El listado incluye nombre, correo, teléfono, rol, fecha de registro y estado, con búsqueda rápida.
- **Reglas:** el administrador no puede inactivar su propia cuenta. Un usuario inactivo no puede iniciar sesión.
- **Criterios de aceptación:** inactivar un cliente le impide iniciar sesión; reactivarlo se lo permite; el botón no aparece en la fila del propio administrador y el servidor rechaza el intento si se fuerza.
- **Estado S02:** implementado (crear y editar usuarios desde el panel queda fuera del alcance; el alta es por el registro público).

### B. Catálogo de productos

#### RF-09 Consulta del catálogo (Alta)

- **Actor:** Visitante, Cliente, Administrador.
- **Descripción:** cualquier persona consulta los productos activos en una cuadrícula con imagen, marca, categoría, precio "desde" y disponibilidad.
- **Reglas:** solo se muestran productos activos, con categoría y marca activas y al menos una presentación activa.
- **Criterios de aceptación:** un producto inactivo no aparece ni se abre por su dirección directa (se redirige con mensaje); un producto sin stock total aparece con la etiqueta "Agotado".
- **Estado S02:** implementado.

#### RF-10 Filtros y búsqueda (Alta)

- **Descripción:** el catálogo se filtra por texto en el nombre, categoría y marca, de forma combinable, con botón para limpiar los filtros y contador de resultados.
- **Criterios de aceptación:** combinar categoría y marca devuelve solo los productos que cumplen ambos; una búsqueda sin resultados muestra "No encontramos productos con esos filtros."
- **Estado S02:** implementado.

#### RF-11 Detalle del producto (Alta)

- **Descripción:** muestra nombre, descripción, marca, categoría, imagen y todas las presentaciones activas con SKU, precio y disponibilidad ("Disponible", "Quedan N" cuando hay 5 o menos, "Agotado"). Las presentaciones agotadas no se pueden seleccionar.
- **Criterios de aceptación:** el cliente debe elegir una presentación y una cantidad entera mayor o igual a 1 para agregar al carrito; si no elige, se muestra "Seleccione una presentación."
- **Estado S02:** implementado.

#### RF-12 Gestión de productos (Media)

- **Actor:** Administrador.
- **Descripción:** crea, edita e inactiva productos con nombre (máx. 100), descripción, categoría, marca e imagen (dirección web, opcional). Al crear un producto se redirige a la gestión de sus presentaciones.
- **Validaciones:** nombre, descripción, categoría y marca obligatorios; la imagen debe ser una URL válida.
- **Criterios de aceptación:** un producto inactivo deja de verse en el catálogo pero conserva su historial en pedidos; la lista del administrador muestra todos con número de presentaciones, stock total y precio desde.
- **Estado S02:** implementado.

#### RF-13 Variantes e inventario (Alta)

- **Actor:** Administrador.
- **Descripción:** cada producto tiene una o más presentaciones (variantes) con SKU único, descripción de la presentación, precio y stock. El administrador las crea, edita e inactiva. El precio y el stock se controlan por presentación, no por producto.
- **Validaciones y mensajes:** SKU obligatorio, máx. 30, único ("Ese SKU ya existe. Use uno distinto."); precio mayor a cero; stock entero mayor o igual a cero.
- **Criterios de aceptación:** dos presentaciones no pueden compartir SKU; el stock mostrado en el catálogo es la suma de las presentaciones activas; el stock bajo (5 unidades o menos) se resalta.
- **Estado S02:** implementado.

#### RF-14 Categorías y marcas (Media)

- **Actor:** Administrador.
- **Descripción:** crea, edita e inactiva categorías y marcas (nombre único de hasta 50 caracteres y descripción opcional de hasta 150).
- **Reglas:** una categoría o marca inactiva desaparece de los filtros y de las listas de selección y oculta del catálogo los productos asociados.
- **Criterios de aceptación:** un nombre repetido se rechaza con "Ya existe una categoría/marca con ese nombre."
- **Estado S02:** implementado.

### C. Proceso de compra

#### RF-15 Carrito de compras (Alta)

- **Actor:** Cliente.
- **Descripción:** agrega presentaciones al carrito, cambia cantidades y quita líneas. Una presentación no se repite: solo cambia su cantidad. El carrito vive en la sesión y muestra subtotal, IVA, envío y total.
- **Reglas:** la cantidad total de una presentación (incluida la ya agregada) no puede superar su stock; el carrito no reserva ni descuenta stock. Si el administrador inactiva o agota algo mientras está en el carrito, la línea se ajusta o se retira en la siguiente consulta.
- **Mensajes:** "No hay suficiente stock. Disponibles: N."; "Se agregó al carrito: ..."; "Carrito actualizado."; "Su carrito está vacío."
- **Criterios de aceptación:** agregar dos veces la misma presentación suma cantidades; una cantidad mayor al stock se rechaza; un visitante que intenta agregar es enviado al inicio de sesión.
- **Estado S02:** implementado.

#### RF-16 Validación de existencias (Alta)

- **Descripción:** el sistema impide cantidades mayores al stock al agregar, al actualizar el carrito, al confirmar la compra y al pagar.
- **Criterios de aceptación:** si entre el carrito y el pago otro cliente consume el stock, el pedido queda Pendiente sin descontar nada y se informa al cliente.
- **Estado S02:** implementado (la última validación ocurre dentro de la transacción de `spPagarPedido`).

#### RF-17 Confirmación de compra (Alta)

- **Actor:** Cliente.
- **Descripción:** desde el carrito el cliente elige una dirección propia y un método de pago activo, ve el resumen con los montos de las reglas RN-02 a RN-04 y confirma.
- **Validaciones:** carrito no vacío, dirección del propio cliente y método activo. Datos de pago según RF-19.
- **Criterios de aceptación:** ejemplo con subtotal ₡64.000: IVA ₡8.320, envío ₡2.500, total ₡74.820. Una dirección ajena (manipulando el formulario) se rechaza.
- **Estado S02:** implementado.

#### RF-18 Generación del pedido (Alta)

- **Descripción:** al confirmar se crea el pedido en estado Pendiente con su detalle (variante, cantidad, precio unitario congelado, subtotal) dentro de una transacción; si algo falla no queda un pedido a medias. En este paso no se descuenta stock.
- **Criterios de aceptación:** el total guardado coincide con el resumen mostrado; el pedido aparece en "Mis pedidos" del cliente y en "Pedidos" del administrador.
- **Estado S02:** implementado.

#### RF-19 Pago simulado (Alta)

- **Descripción:** el cliente paga con tarjeta o SINPE Móvil simulados. El sistema solo valida el formato de los datos; no cobra ni guarda números de tarjeta, titular, vencimiento ni código de seguridad.
- **Validaciones:** tarjeta: 13 a 19 dígitos, titular, vencimiento MM/AA, código de 3 o 4 dígitos. SINPE Móvil: teléfono de 8 dígitos.
- **Comportamiento:** al aprobar, en una sola transacción se verifican las existencias, se descuenta el stock de cada presentación y el pedido pasa a Pagado; se envía el correo de confirmación. Un pedido Pendiente puede pagarse luego desde su detalle.
- **Criterios de aceptación:** tras pagar, el stock baja exactamente la cantidad comprada; sin existencias suficientes el pedido sigue Pendiente y el stock no cambia; un pedido ajeno o no pendiente no se puede pagar.
- **Estado S02:** implementado.

#### RF-20 Historial de pedidos (Media)

- **Actor:** Cliente.
- **Descripción:** lista de sus pedidos (número, fecha, pago, total, estado) con filtro por estado y un detalle con línea de tiempo de estados, líneas, totales, dirección y método de pago.
- **Reglas:** un cliente solo puede abrir sus pedidos; abrir uno ajeno redirige con "El pedido no existe."
- **Estado S02:** implementado.

#### RF-21 Gestión de pedidos (Alta)

- **Actor:** Administrador.
- **Descripción:** lista todos los pedidos con filtro por estado, abre el detalle (cliente, dirección, pago, líneas) y cambia el estado.
- **Reglas de transición:** Pendiente a Cancelado; Pagado a Enviado o Cancelado; Enviado a Entregado o Cancelado; Entregado y Cancelado son finales. El paso a Pagado solo lo hace el pago del cliente. Cancelar un pedido Pagado o Enviado devuelve el stock.
- **Criterios de aceptación:** una transición no permitida se rechaza con "Ese cambio de estado no es válido para el pedido."; al cancelar un pedido pagado el stock de sus líneas aumenta.
- **Estado S02:** implementado.

#### RF-22 Métodos de pago (Media)

- **Actor:** Administrador.
- **Descripción:** existen dos métodos simulados (Tarjeta y SINPE Móvil). El administrador los activa o inactiva; solo los activos se ofrecen al comprar.
- **Estado S02:** implementado.

### D. Notificaciones, reportes y transversales

#### RF-23 Notificaciones por correo (Alta)

- **Descripción:** el sistema envía correos con plantilla HTML: bienvenida (registro), recuperación de acceso y confirmación de pago del pedido.
- **Decisión de alcance:** se elimina la tabla de notificaciones del modelo de datos de la entrega 1. No se persiste un historial de envíos; un fallo de envío se registra en `terror`. Para el curso basta con una notificación por correo.
- **Configuración:** el envío usa PHPMailer con el SMTP de Gmail y las variables `MAIL_USER` y `MAIL_PASS`. Si no están definidas el envío se simula y el contenido queda en el log de PHP. Esto resuelve el punto pendiente de la entrega 1 (se descarta Resend para no depender de un servicio externo).
- **Criterios de aceptación:** cada uno de los tres eventos produce exactamente un correo al destinatario correcto con su nombre y los datos del evento.
- **Estado S02:** implementado (simulado hasta configurar la cuenta de correo).

#### RF-24 Reportes básicos (Baja)

- **Actor:** Administrador.
- **Descripción:** el panel muestra ventas, pedidos, pendientes, productos activos, unidades en stock y clientes; la vista de reportes muestra los pedidos por estado (cantidad y monto) y las presentaciones con 5 unidades o menos.
- **Definición de "ventas":** suma del total de los pedidos en estado Pagado, Enviado o Entregado.
- **Estado S02:** implementado.

#### RF-25 Mensajes, formato y validación (Alta)

- **Descripción:** todos los formularios validan formato y obligatoriedad en el navegador (jQuery Validate, mensajes en español) y de nuevo en el servidor. Los resultados se comunican con alertas de éxito, error, advertencia o información; las acciones destructivas piden confirmación.
- **Criterios de aceptación:** ningún formulario acepta campos obligatorios vacíos; los mensajes de error aparecen junto al campo.
- **Estado S02:** implementado.

#### RF-26 Interfaz responsive (Alta)

- **Descripción:** todas las vistas se adaptan a escritorio y móvil con Bootstrap: el menú lateral se oculta en pantallas pequeñas y las tablas se desplazan horizontalmente.
- **Estado S02:** implementado (verificado a 375 px de ancho en el catálogo).

> Nota de numeración: la entrega 1 tenía 24 requerimientos. En esta versión "Consulta del
> catálogo" se divide en consulta (RF-09), filtros (RF-10) y detalle (RF-11), por lo que la
> numeración llega a RF-26. La tabla de la sección 6 relaciona ambas numeraciones.

## 5. Casos de uso principales

Los cinco casos de uso de la entrega 1 se mantienen. Cambios de esta versión:

- **CU-01 Registrarse e iniciar sesión:** el inicio de sesión es por correo y contraseña (RF-01, RF-02). Tablas: `tusuario`, `trol`.
- **CU-02 Recuperar contraseña:** usa contraseña temporal por correo en lugar de token (RF-04). Tablas: `tusuario`.
- **CU-03 Comprar productos:** carrito en sesión, pedido y pago simulado en una transacción (RF-15 a RF-19). Tablas: `tproducto`, `tvariante`, `tdireccion`, `tmetodopago`, `tpedido`, `tdetallepedido`.
- **CU-04 Gestionar catálogo:** productos, presentaciones, categorías y marcas (RF-12 a RF-14). Tablas: `tproducto`, `tvariante`, `tcategoria`, `tmarca`.
- **CU-05 Gestionar pedidos:** cambio de estado con transiciones válidas y devolución de stock (RF-21). Tablas: `tpedido`, `tdetallepedido`, `tvariante`.

## 6. Relación con la numeración de la entrega 1

| Entrega 1 | Esta versión |
|---|---|
| RF-01 a RF-08 (registro, inicio, cierre, recuperación, cambio, roles, direcciones, usuarios) | RF-01 a RF-08, mismo orden |
| RF-09 Consulta del catálogo | RF-09 Consulta, RF-10 Filtros y búsqueda, RF-11 Detalle |
| RF-10 Gestión de productos | RF-12 |
| RF-11 Variantes e inventario | RF-13 |
| RF-12 Categorías y marcas | RF-14 |
| RF-13 Carrito a RF-20 Métodos de pago (carrito, existencias, confirmación, pedido, pago, historial, gestión de pedidos, métodos) | RF-15 a RF-22, mismo orden |
| RF-21 Notificaciones, RF-22 Reportes, RF-23 Mensajes y validación, RF-24 Responsive | RF-23, RF-24, RF-25, RF-26 |

## 7. Requerimientos técnicos

| ID | Requerimiento |
|---|---|
| RT-01 | Arquitectura Modelo, Vista, Controlador (MVC). |
| RT-02 | PHP y JavaScript con jQuery. |
| RT-03 | CSS, JavaScript y PHP incluidos por medio de enlaces externos. |
| RT-04 | Estándar de programación definido por el equipo (ver README). |
| RT-05 | Uso obligatorio de procedimientos almacenados (todo el acceso a datos pasa por `CALL sp...`). |
| RT-06 | Base de datos MySQL, compatible con MariaDB de XAMPP. |
| RT-07 | Bootstrap y diseño responsive. |
| RT-08 | Se ejecuta copiando la carpeta `lifty` a `htdocs` de XAMPP e importando `lifty_bd.sql`. |

## 8. Pendientes y decisiones por confirmar

1. Cuenta de correo real (Gmail con contraseña de aplicación) para activar el envío; hoy se simula.
2. Contenido adicional de reportes (por ejemplo, ventas por mes) si el profesor lo solicita.
3. Gestión de imágenes: hoy se usa una dirección web; subir archivos queda para la entrega final si se considera necesario.
