# Lifty: Ecommerce de Productos para Entrenar

Jeferson Andrew Fuentes García, Ignacio Hidalgo Méndez, Donald Josué Matarrita Solano,
Gabriel David Moreno Jiménez. Escuela de Ingeniería en Sistemas de Computación,
Universidad Fidélitas, San Pedro, Costa Rica.

> Versión S02 (semana 10) del documento IEEE de la entrega 1. Se mantiene la estructura de
> secciones y se actualiza con el avance real. Los cambios respecto a S01 están marcados
> con **[S02]**. Para entregar, este contenido se pasa a la plantilla IEEE de dos columnas
> del curso.

**Resumen.** Este documento describe Lifty, un comercio electrónico ficticio de productos
para entrenar (suplementos, bebidas, ropa y accesorios) desarrollado como proyecto final del
curso Ambiente Web Cliente-Servidor (SC-502). El sistema permite que los clientes se
registren, consulten el catálogo, armen un carrito y generen pedidos, y que el administrador
gestione productos, inventario y pedidos. Se construye con arquitectura Modelo-Vista-Controlador,
PHP y JavaScript con jQuery, base de datos MySQL con procedimientos almacenados e interfaz
responsive con Bootstrap. **[S02]** El modelo de datos se simplificó de 16 a 11 tablas y la
aplicación ya cubre el flujo completo de compra, desde el registro hasta la gestión del pedido.

**Palabras clave.** comercio electrónico, tienda en línea, arquitectura MVC, PHP, MySQL,
procedimientos almacenados, Bootstrap.

## I. Introducción

El comercio electrónico permite que una tienda publique su catálogo, reciba pedidos y
administre a sus clientes por medio de un sitio web. En el caso de los productos para
entrenar, un mismo artículo suele ofrecerse en varias presentaciones (sabor y tamaño, o talla
y color), por lo que el precio y el inventario deben controlarse por cada presentación y no solo
por producto [1].

Lifty es un comercio electrónico de productos para entrenar, concebido como proyecto final
grupal del curso SC-502 de la Universidad Fidélitas [2]. El sistema contempla dos perfiles: el
cliente se registra, inicia sesión, consulta el catálogo, agrega presentaciones a su carrito,
elige una dirección y un método de pago y genera un pedido; el administrador gestiona el
catálogo, el inventario y los pedidos. Incluye cambio y recuperación de contraseña y
notificaciones por correo electrónico [2].

Conforme al enunciado [2], se desarrolla con arquitectura MVC, PHP [3] y JavaScript con
jQuery [4], base de datos MySQL [5] con procedimientos almacenados e interfaz responsive con
Bootstrap [6]. La interfaz parte de una plantilla de código abierto con licencia MIT [7].

**[S02]** El modelo de datos consta de 11 tablas organizadas en cuatro módulos (Tabla I). La
versión de la entrega 1 tenía 16 tablas; el profesor señaló que era amplia y se ajustó el
alcance (ver sección VI).

**TABLA I. Módulos del modelo de datos de Lifty**

| Módulo | Tablas | Total |
|---|---|---|
| Seguridad y usuarios | `trol`, `tusuario`, `tdireccion` | 3 |
| Catálogo de productos | `tcategoria`, `tmarca`, `tproducto`, `tvariante` | 4 |
| Proceso de compra | `tmetodopago`, `tpedido`, `tdetallepedido` | 3 |
| Soporte | `terror` | 1 |

El documento se organiza así: la sección II presenta los objetivos, la III la importancia, la
IV el tipo de proyecto, la V las necesidades que resuelve y la VI el avance de la
entrega 2. El detalle de los requerimientos y del diagrama entidad-relación se presenta en
documentos complementarios.

## II. Objetivos

### A. Objetivo general

Desarrollar un sistema web de comercio electrónico ficticio de productos para entrenar, que
integre los conocimientos del curso Ambiente Web Cliente-Servidor y cubra el proceso completo
de compra, desde el registro del cliente hasta la gestión del pedido por parte del
administrador.

### B. Objetivos específicos

- Diseñar e implementar en MySQL un modelo de datos de 11 tablas organizado en cuatro módulos y utilizar procedimientos almacenados para las operaciones de consulta, inserción, modificación y borrado lógico. **[S02]**
- Estructurar el sistema con la arquitectura MVC, separando la lógica de negocio, el acceso a datos y la presentación mediante archivos PHP, CSS y JavaScript enlazados de forma externa.
- Programar en PHP el registro de usuarios, el inicio y cierre de sesión, el cambio y la recuperación de contraseña con hash, y el control de acceso para los perfiles de cliente y administrador.
- Desarrollar con PHP y JavaScript con jQuery la gestión del catálogo, las variantes de producto y el inventario, así como el carrito de compras y la generación de pedidos con cálculo de montos y validación de existencias.
- Implementar con Bootstrap una interfaz responsive y amigable, con validación de campos, formato de datos y mensajes claros.
- Integrar el envío de notificaciones por correo electrónico desde PHP: bienvenida, confirmación de pago y recuperación de contraseña.
- Definir y aplicar un estándar de programación común en nombres de métodos, estructura de carpetas y comentarios.

## III. Importancia del proyecto

En el área académica, el proyecto integra y aplica la arquitectura MVC, la programación con
PHP y JavaScript, el diseño de una base de datos con procedimientos almacenados y una interfaz
responsive con Bootstrap. Obliga a resolver problemas propios de un sistema transaccional:
control de acceso por rol, almacenamiento de contraseñas con hash, validación de existencias,
cálculo de montos y cambios de estado de los pedidos. También permite ejercitar la definición
de requerimientos, el diseño de modelos de datos y la adopción de un estándar de programación
común.

En cuanto a su potencial de uso real, Lifty modela una situación frecuente en el comercio en
línea de productos para entrenar: un mismo artículo se ofrece en varias presentaciones, por
lo que el precio y el inventario se controlan por variante [1]. El sistema lo resuelve desde el
diseño, junto con el borrado lógico que conserva el historial. Una eventual puesta en
producción requeriría una etapa posterior de adaptación a las condiciones reales de un negocio,
fuera del alcance de este trabajo.

Por último, al ser un proyecto ficticio, con pagos simulados y datos de ejemplo, permite
practicar el flujo completo de una tienda en línea sin los riesgos de manejar dinero o
información personal real.

## IV. Tipo de proyecto

Lifty es un proyecto ficticio. No existe un negocio, una cartera de clientes ni un inventario
reales detrás del sistema; los productos, marcas, precios y usuarios son datos de ejemplo
creados con fines académicos y su desarrollo responde al proyecto final del curso [2].

Aun así, se concibe con potencial de llevarse a producción: arquitectura MVC que separa
responsabilidades, modelo relacional con borrado lógico y contraseñas con hash [1].

## V. Necesidades que resolver

Para los clientes, Lifty facilita la consulta de un catálogo organizado de suplementos,
bebidas, ropa y accesorios, ver las presentaciones disponibles de cada producto, agregarlas al
carrito y generar pedidos, además de registrar direcciones de entrega y elegir un método de
pago dentro del flujo de compra.

Para el administrador, facilita la gestión centralizada del catálogo, sus variantes, el
inventario y los pedidos. Como un mismo producto puede tener sabores, tamaños, tallas o
colores, el precio y la disponibilidad se manejan por variante.

Finalmente, ofrece soporte al usuario (recuperación y cambio de contraseña) y notificaciones
por correo, dentro de una plataforma web responsive.

## VI. Avance de la entrega 2 (semana 10) **[S02]**

### A. Decisiones de alcance

El profesor consideró la base de datos de la entrega 1 "un poco amplia". Se eliminaron las
tablas que no aportaban al alcance del curso (Tabla II) y se redujo el modelo a 11 tablas.

**TABLA II. Ajustes al modelo de datos**

| Se eliminó | Se resolvió con |
|---|---|
| Tabla de estados (11 llaves foráneas) | Columna `Estado` y columna `EstadoPedido` |
| Token de recuperación | Contraseña temporal enviada por correo |
| Carrito y detalle del carrito | Carrito en la sesión del servidor |
| Notificaciones y tipos de notificación | Envío directo de correo con plantillas |

### B. Funcionalidades implementadas

**TABLA III. Estado de los requerimientos**

| Bloque | Estado |
|---|---|
| Seguridad y usuarios (RF-01 a RF-08) | Implementado: registro, inicio y cierre de sesión, recuperación y cambio de contraseña, roles, direcciones y gestión de usuarios |
| Catálogo (RF-09 a RF-14) | Implementado: catálogo con filtros, detalle, productos, variantes, categorías y marcas |
| Compra (RF-15 a RF-22) | Implementado: carrito, IVA de 13 %, envío de ₡2.500, pedido, pago simulado con descuento de stock, historial y gestión de pedidos |
| Notificaciones, reportes y transversales (RF-23 a RF-26) | Implementado: correos de bienvenida, recuperación y pago (simulados hasta configurar la cuenta SMTP), reportes básicos, validación y diseño responsive |

### C. Pendiente para la entrega final

- Configurar la cuenta de correo real para el envío.
- Definir con el profesor si se amplía el contenido de los reportes.
- Revisión final de la interfaz, pruebas con todo el equipo y entrega del código y la base de datos al campus virtual.

## Referencias

[1] Equipo Lifty, Guía del diagrama entidad-relación de Lifty, Escuela de Ingeniería en Sistemas de Computación, Universidad Fidélitas, documento interno del curso SC-502.

[2] E. Calvo Castillo, Proyecto final grupal, curso Ambiente Web Cliente-Servidor (SC-502), Universidad Fidélitas, documento del curso.

[3] The PHP Group. (s. f.) PHP: Hypertext Preprocessor. [En línea]. Disponible: https://www.php.net/

[4] OpenJS Foundation. (s. f.) jQuery. [En línea]. Disponible: https://jquery.com/

[5] Oracle Corporation. (s. f.) MySQL Documentation. [En línea]. Disponible: https://dev.mysql.com/doc/

[6] The Bootstrap Authors. (s. f.) Bootstrap. [En línea]. Disponible: https://getbootstrap.com/

[7] CodesCandy. (s. f.) InApp Inventory Dashboard, ThemeWagon. [En línea]. Disponible: https://themewagon.com/themes/inapp/
