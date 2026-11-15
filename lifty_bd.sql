-- ============================================================================
-- Lifty - Base de datos lifty_bd (Submission 02)
-- Compatible con MariaDB 10.4 (XAMPP) y MySQL 8+.
-- Importar completo desde phpMyAdmin o con: mysql -u root < lifty_bd.sql
-- Convenciones (las del curso): tablas tNombre, llave primaria Consecutivo,
-- procedimientos spNombre, Estado 1 = activo / 0 = inactivo (borrado logico).
-- Usuarios de ejemplo: admin@lifty.com / Admin123*  y  cliente@lifty.com / Cliente123*
-- ============================================================================

CREATE DATABASE IF NOT EXISTS `lifty_bd` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `lifty_bd`;

-- Los procedimientos toman la collation de la conexion con la que se crean.
SET NAMES utf8mb4 COLLATE utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `tdetallepedido`, `tpedido`, `tmetodopago`, `tvariante`, `tproducto`,
                     `tmarca`, `tcategoria`, `tdireccion`, `tusuario`, `trol`, `terror`;
SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- Tablas
-- ----------------------------------------------------------------------------

CREATE TABLE `terror` (
  `Consecutivo` int NOT NULL AUTO_INCREMENT,
  `MensajeError` varchar(8000) DEFAULT NULL,
  `Accion` varchar(255) DEFAULT NULL,
  `ConsecutivoUsuario` int DEFAULT NULL,
  `FechaHora` datetime DEFAULT NULL,
  PRIMARY KEY (`Consecutivo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `trol` (
  `Consecutivo` int NOT NULL AUTO_INCREMENT,
  `NombreRol` varchar(50) NOT NULL,
  PRIMARY KEY (`Consecutivo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tusuario` (
  `Consecutivo` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50) NOT NULL,
  `Apellido1` varchar(50) NOT NULL,
  `Apellido2` varchar(50) NOT NULL DEFAULT '',
  `CorreoElectronico` varchar(100) NOT NULL,
  `Telefono` varchar(20) NOT NULL,
  `Contrasenna` varchar(255) NOT NULL,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `ConsecutivoRol` int NOT NULL,
  `FechaRegistro` datetime NOT NULL,
  PRIMARY KEY (`Consecutivo`),
  UNIQUE KEY `UK_tusuario_CorreoElectronico` (`CorreoElectronico`),
  KEY `FK_tusuario_ConsecutivoRol` (`ConsecutivoRol`),
  CONSTRAINT `FK_tusuario_ConsecutivoRol` FOREIGN KEY (`ConsecutivoRol`) REFERENCES `trol` (`Consecutivo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tdireccion` (
  `Consecutivo` int NOT NULL AUTO_INCREMENT,
  `ConsecutivoUsuario` int NOT NULL,
  `Provincia` varchar(50) NOT NULL,
  `Canton` varchar(50) NOT NULL,
  `Distrito` varchar(50) NOT NULL,
  `Detalle` varchar(250) NOT NULL,
  `CodigoPostal` varchar(10) NOT NULL DEFAULT '',
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Consecutivo`),
  KEY `FK_tdireccion_ConsecutivoUsuario` (`ConsecutivoUsuario`),
  CONSTRAINT `FK_tdireccion_ConsecutivoUsuario` FOREIGN KEY (`ConsecutivoUsuario`) REFERENCES `tusuario` (`Consecutivo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tcategoria` (
  `Consecutivo` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50) NOT NULL,
  `Descripcion` varchar(150) NOT NULL DEFAULT '',
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Consecutivo`),
  UNIQUE KEY `UK_tcategoria_Nombre` (`Nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tmarca` (
  `Consecutivo` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50) NOT NULL,
  `Descripcion` varchar(150) NOT NULL DEFAULT '',
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Consecutivo`),
  UNIQUE KEY `UK_tmarca_Nombre` (`Nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tproducto` (
  `Consecutivo` int NOT NULL AUTO_INCREMENT,
  `ConsecutivoCategoria` int NOT NULL,
  `ConsecutivoMarca` int NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Descripcion` text NOT NULL,
  `Imagen` varchar(255) NOT NULL DEFAULT '',
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  `Destacado` tinyint(1) NOT NULL DEFAULT 0,
  `FechaCreacion` datetime NOT NULL,
  PRIMARY KEY (`Consecutivo`),
  KEY `FK_tproducto_ConsecutivoCategoria` (`ConsecutivoCategoria`),
  KEY `FK_tproducto_ConsecutivoMarca` (`ConsecutivoMarca`),
  CONSTRAINT `FK_tproducto_ConsecutivoCategoria` FOREIGN KEY (`ConsecutivoCategoria`) REFERENCES `tcategoria` (`Consecutivo`),
  CONSTRAINT `FK_tproducto_ConsecutivoMarca` FOREIGN KEY (`ConsecutivoMarca`) REFERENCES `tmarca` (`Consecutivo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tvariante` (
  `Consecutivo` int NOT NULL AUTO_INCREMENT,
  `ConsecutivoProducto` int NOT NULL,
  `Sku` varchar(30) NOT NULL,
  `Presentacion` varchar(100) NOT NULL,
  `Precio` decimal(10,2) NOT NULL,
  `Stock` int NOT NULL DEFAULT 0,
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Consecutivo`),
  UNIQUE KEY `UK_tvariante_Sku` (`Sku`),
  KEY `FK_tvariante_ConsecutivoProducto` (`ConsecutivoProducto`),
  CONSTRAINT `FK_tvariante_ConsecutivoProducto` FOREIGN KEY (`ConsecutivoProducto`) REFERENCES `tproducto` (`Consecutivo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tmetodopago` (
  `Consecutivo` int NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(50) NOT NULL,
  `Descripcion` varchar(150) NOT NULL DEFAULT '',
  `Estado` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Consecutivo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- EstadoPedido: Pendiente, Pagado, Enviado, Entregado, Cancelado
CREATE TABLE `tpedido` (
  `Consecutivo` int NOT NULL AUTO_INCREMENT,
  `ConsecutivoUsuario` int NOT NULL,
  `ConsecutivoDireccion` int NOT NULL,
  `ConsecutivoMetodoPago` int NOT NULL,
  `EstadoPedido` varchar(20) NOT NULL DEFAULT 'Pendiente',
  `FechaPedido` datetime NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL,
  `Impuesto` decimal(10,2) NOT NULL,
  `CostoEnvio` decimal(10,2) NOT NULL,
  `Total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`Consecutivo`),
  KEY `FK_tpedido_ConsecutivoUsuario` (`ConsecutivoUsuario`),
  KEY `FK_tpedido_ConsecutivoDireccion` (`ConsecutivoDireccion`),
  KEY `FK_tpedido_ConsecutivoMetodoPago` (`ConsecutivoMetodoPago`),
  CONSTRAINT `FK_tpedido_ConsecutivoUsuario` FOREIGN KEY (`ConsecutivoUsuario`) REFERENCES `tusuario` (`Consecutivo`),
  CONSTRAINT `FK_tpedido_ConsecutivoDireccion` FOREIGN KEY (`ConsecutivoDireccion`) REFERENCES `tdireccion` (`Consecutivo`),
  CONSTRAINT `FK_tpedido_ConsecutivoMetodoPago` FOREIGN KEY (`ConsecutivoMetodoPago`) REFERENCES `tmetodopago` (`Consecutivo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tdetallepedido` (
  `Consecutivo` int NOT NULL AUTO_INCREMENT,
  `ConsecutivoPedido` int NOT NULL,
  `ConsecutivoVariante` int NOT NULL,
  `Cantidad` int NOT NULL,
  `PrecioUnitario` decimal(10,2) NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`Consecutivo`),
  KEY `FK_tdetallepedido_ConsecutivoPedido` (`ConsecutivoPedido`),
  KEY `FK_tdetallepedido_ConsecutivoVariante` (`ConsecutivoVariante`),
  CONSTRAINT `FK_tdetallepedido_ConsecutivoPedido` FOREIGN KEY (`ConsecutivoPedido`) REFERENCES `tpedido` (`Consecutivo`),
  CONSTRAINT `FK_tdetallepedido_ConsecutivoVariante` FOREIGN KEY (`ConsecutivoVariante`) REFERENCES `tvariante` (`Consecutivo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------------------------------------------------------
-- Datos de ejemplo
-- ----------------------------------------------------------------------------

INSERT INTO trol (Consecutivo, NombreRol) VALUES (1,'Cliente'),(2,'Administrador');

INSERT INTO tusuario (Consecutivo,Nombre,Apellido1,Apellido2,CorreoElectronico,Telefono,Contrasenna,Estado,ConsecutivoRol,FechaRegistro) VALUES
(1,'Administrador','Lifty','','admin@lifty.com','88880001','$2y$10$gYXux5/VZNSWHQGSAsozx.F0Z6CJKXLc5.qU2PxXtYMozBReba3yK',1,2,NOW()),
(2,'Cliente','Demo','Lifty','cliente@lifty.com','88880002','$2y$10$RYAUAT780rsknhq/o2ZAPO/nyDBLjsyoNMuTx5Ada.e3U4zjKF.D2',1,1,NOW());

INSERT INTO tdireccion (ConsecutivoUsuario,Provincia,Canton,Distrito,Detalle,CodigoPostal,Estado) VALUES
(2,'San José','Montes de Oca','San Pedro','200 m este de la Universidad Fidélitas','11501',1);

INSERT INTO tcategoria (Consecutivo,Nombre,Descripcion) VALUES
(1,'Suplementos','Proteínas, creatinas, pre entrenos y aminoácidos'),
(2,'Ropa deportiva','Camisetas, shorts y ropa para entrenar'),
(3,'Snacks y bebidas','Barras, snacks saludables e hidratación'),
(4,'Accesorios','Shakers, guantes y más');

INSERT INTO tmarca (Consecutivo,Nombre,Descripcion) VALUES
(1,'Lifty Pro','Proteínas y ganadores de peso'),
(2,'Lifty Labs','Suplementos especializados'),
(3,'Lifty Wear','Ropa deportiva'),
(4,'Lifty Gear','Accesorios de entrenamiento'),
(5,'Lifty Snacks','Snacks saludables y barras'),
(6,'Lifty Drinks','Bebidas e hidratación');

INSERT INTO tproducto (Consecutivo,ConsecutivoCategoria,ConsecutivoMarca,Nombre,Descripcion,Imagen,Estado,FechaCreacion) VALUES
(1,1,1,'Whey Gold 100%','Proteína de suero de leche con 24 g de proteína por porción. Ideal para la recuperación muscular.','',1,NOW()),
(2,1,1,'Iso Hydro 100','Proteína aislada hidrolizada de rápida absorción, baja en azúcar y grasa.','',1,NOW()),
(3,1,2,'Creatina Platinum','Creatina monohidratada micronizada para aumentar fuerza y rendimiento.','',1,NOW()),
(4,1,2,'Pre-Workout Ignite','Pre entreno con cafeína y beta-alanina para energía y enfoque.','',1,NOW()),
(5,3,6,'Zero Isotónica','Bebida isotónica sin azúcar para reponer electrolitos.','',1,NOW()),
(6,2,3,'Camiseta Dry-Fit','Camiseta deportiva de secado rápido, corte atlético.','',1,NOW()),
(7,4,4,'Shaker Lifty 700 ml','Shaker con mezclador metálico y tapa antiderrames.','',1,NOW()),
(8,3,5,'Barras Proteicas Lifty','Caja de 12 barras con 20 g de proteína y bajo azúcar. El snack perfecto después de entrenar.','',1,NOW()),
(9,2,3,'Short Training','Short ligero con bolsillos y cintura elástica para entrenamiento y running.','',1,NOW()),
(10,1,2,'BCAA 2:1:1 Recovery','Aminoácidos ramificados en proporción 2:1:1 para apoyar la recuperación muscular durante y después del entrenamiento.','',1,NOW()),
(11,1,2,'Glutamina Pura','L-glutamina micronizada sin sabor. Se mezcla fácil y no tiene azúcar añadida.','',1,NOW()),
(12,1,2,'Omega 3 Ultra','Aceite de pescado concentrado con EPA y DHA. Apoya la salud cardiovascular y articular.','',1,NOW()),
(13,1,2,'Multivitamínico Daily','Complejo de vitaminas y minerales para deportistas, una cápsula al día.','',1,NOW()),
(14,1,1,'Caseína Micelar Night','Proteína de digestión lenta, ideal antes de dormir para una recuperación sostenida.','',1,NOW()),
(15,1,1,'Mass Gainer 5 lb','Ganador de peso con carbohidratos complejos y 50 g de proteína por porción.','',1,NOW()),
(16,2,3,'Leggings Seamless','Leggings sin costuras de alta compresión, cintura alta y tejido que respira.','',1,NOW()),
(17,2,3,'Top Deportivo Impact','Top de soporte medio con tirantes cruzados y tela de secado rápido.','',1,NOW()),
(18,2,3,'Sudadera Hoodie Training','Hoodie de felpa ligera con bolsillo canguro, perfecta para el calentamiento.','',1,NOW()),
(19,2,3,'Camiseta Oversize Gym','Corte amplio de algodón peinado con estampado Lifty en la espalda.','',1,NOW()),
(20,2,3,'Chaqueta Cortaviento','Chaqueta ligera y plegable con capucha, resistente a la llovizna.','',1,NOW()),
(21,2,3,'Medias de Compresión','Medias de compresión graduada para mejorar la circulación al correr.','',1,NOW()),
(22,2,3,'Gorra Training','Gorra ajustable con malla trasera y banda absorbente.','',1,NOW()),
(23,2,3,'Tank Top Performance','Camiseta sin mangas de tejido técnico, ideal para entrenamientos intensos.','',1,NOW()),
(24,3,5,'Barras Energéticas de Avena','Barras de avena con miel y frutos secos. Energía natural para antes de entrenar.','',1,NOW()),
(25,3,5,'Mix de Nueces Premium','Mezcla de almendras, nueces y marañones tostados sin sal.','',1,NOW()),
(26,3,5,'Mantequilla de Maní Natural','Mantequilla de maní 100 % natural, sin azúcar ni aceites añadidos.','',1,NOW()),
(27,3,5,'Chips de Proteína','Chips horneados con 15 g de proteína por bolsa. Salados y crocantes.','',1,NOW()),
(28,3,5,'Galletas Proteicas','Galletas suaves con 12 g de proteína y bajo contenido de azúcar.','',1,NOW()),
(29,3,6,'Agua con Electrolitos','Agua mineral con sodio, potasio y magnesio para hidratarte mejor.','',1,NOW()),
(30,3,6,'Café Proteico Listo','Café frío listo para tomar con 20 g de proteína de suero.','',1,NOW()),
(31,3,5,'Granola Fit','Granola crujiente con semillas y frutos rojos, baja en azúcar.','',1,NOW()),
(32,4,4,'Guantes de Entrenamiento','Guantes acolchados con agarre antideslizante y muñequera ajustable.','',1,NOW()),
(33,4,4,'Cinturón de Levantamiento','Cinturón de cuero reforzado para sentadilla y peso muerto, 10 cm de ancho.','',1,NOW()),
(34,4,4,'Bandas de Resistencia Set','Set de 5 bandas elásticas con distintos niveles de resistencia y bolsa.','',1,NOW()),
(35,4,4,'Cuerda para Saltar Speed','Cuerda de velocidad con rodamientos y mangos de aluminio antideslizantes.','',1,NOW()),
(36,4,4,'Botella Térmica 1 L','Botella de acero inoxidable que mantiene el frío 24 horas.','',1,NOW()),
(37,4,4,'Toalla de Microfibra','Toalla de secado rápido, compacta y con clip para colgar en la máquina.','',1,NOW()),
(38,4,4,'Straps de Agarre','Correas de algodón reforzado para jalones y peso muerto pesado.','',1,NOW()),
(39,4,4,'Rodilleras Deportivas','Rodilleras de neopreno de 7 mm con soporte para sentadillas.','',1,NOW()),
(40,4,4,'Bolso Deportivo 40 L','Bolso con compartimiento para zapatos y bolsillo húmedo.','',1,NOW());

INSERT INTO tvariante (ConsecutivoProducto,Sku,Presentacion,Precio,Stock,Estado) VALUES
(1,'WHEY-CHO-2LB','Chocolate 2 lb',32000.00,25,1),
(1,'WHEY-VAI-2LB','Vainilla 2 lb',32000.00,18,1),
(1,'WHEY-CHO-5LB','Chocolate 5 lb',68000.00,10,1),
(2,'ISO-CHO-16LB','Chocolate 1.6 lb',48000.00,12,1),
(2,'ISO-FRE-16LB','Fresa 1.6 lb',48000.00,3,1),
(3,'CREA-300G','300 g sin sabor',22000.00,40,1),
(3,'CREA-600G','600 g sin sabor',39000.00,2,1),
(4,'C4-FRU-30','Fruit Punch 30 porciones',27500.00,22,1),
(4,'C4-AZU-30','Azul Raspberry 30 porciones',27500.00,15,1),
(5,'GAT-ZERO-LIM','Limón 500 ml',1500.00,120,1),
(5,'GAT-ZERO-NAR','Naranja 500 ml',1500.00,95,1),
(6,'CAM-DRY-M','Talla M',12500.00,30,1),
(6,'CAM-DRY-L','Talla L',12500.00,28,1),
(6,'CAM-DRY-XL','Talla XL',12500.00,5,1),
(7,'SHK-700-NEG','Negro 700 ml',6500.00,50,1),
(7,'SHK-700-AZU','Azul 700 ml',6500.00,44,1),
(8,'QST-CHO-12','Chocolate chip cookie, caja x12',24500.00,30,1),
(8,'QST-CAR-12','Caramelo y nueces, caja x12',24500.00,22,1),
(9,'SHT-TRN-M','Talla M',9800.00,20,1),
(9,'SHT-TRN-L','Talla L',9800.00,16,1),
(10,'BCAA-SAN-30','Sandía 30 porciones',18500.00,26,1),
(10,'BCAA-LIM-30','Limón 30 porciones',18500.00,19,1),
(11,'GLU-300G','300 g sin sabor',16500.00,31,1),
(11,'GLU-500G','500 g sin sabor',24500.00,14,1),
(12,'OME-60','60 cápsulas',12500.00,44,1),
(12,'OME-120','120 cápsulas',21500.00,25,1),
(13,'MUL-60','60 cápsulas',13500.00,38,1),
(13,'MUL-120','120 cápsulas',23500.00,12,1),
(14,'CAS-CHO-2LB','Chocolate 2 lb',34500.00,15,1),
(14,'CAS-VAI-2LB','Vainilla 2 lb',34500.00,9,1),
(15,'MAS-CHO-5LB','Chocolate 5 lb',39500.00,11,1),
(15,'MAS-FRE-5LB','Fresa 5 lb',39500.00,6,1),
(16,'LEG-SEA-S','Talla S',18900.00,18,1),
(16,'LEG-SEA-M','Talla M',18900.00,22,1),
(16,'LEG-SEA-L','Talla L',18900.00,10,1),
(17,'TOP-IMP-S','Talla S',11900.00,20,1),
(17,'TOP-IMP-M','Talla M',11900.00,24,1),
(18,'HOO-TRN-M','Talla M',24900.00,14,1),
(18,'HOO-TRN-L','Talla L',24900.00,13,1),
(18,'HOO-TRN-XL','Talla XL',24900.00,7,1),
(19,'OVR-GYM-M','Talla M',13900.00,26,1),
(19,'OVR-GYM-L','Talla L',13900.00,21,1),
(20,'COR-VIE-M','Talla M',27900.00,9,1),
(20,'COR-VIE-L','Talla L',27900.00,8,1),
(21,'MED-COM-M','Talla M, par',7900.00,35,1),
(21,'MED-COM-L','Talla L, par',7900.00,30,1),
(22,'GOR-TRN-NEG','Negra',6900.00,40,1),
(22,'GOR-TRN-NAR','Naranja',6900.00,28,1),
(23,'TNK-PER-M','Talla M',9400.00,25,1),
(23,'TNK-PER-L','Talla L',9400.00,17,1),
(24,'BAR-AVE-12','Caja x12',16900.00,33,1),
(24,'BAR-AVE-24','Caja x24',31500.00,15,1),
(25,'NUE-250G','Bolsa 250 g',6900.00,42,1),
(25,'NUE-500G','Bolsa 500 g',12500.00,20,1),
(26,'MAN-CRE-454','Cremosa 454 g',5900.00,36,1),
(26,'MAN-CRO-454','Crujiente 454 g',5900.00,29,1),
(27,'CHP-QUE-6','Queso, caja x6',14500.00,24,1),
(27,'CHP-BBQ-6','BBQ, caja x6',14500.00,18,1),
(28,'GAL-CHO-8','Chocolate, caja x8',13500.00,27,1),
(28,'GAL-VAI-8','Vainilla, caja x8',13500.00,21,1),
(29,'AGU-ELE-6','Pack x6, 500 ml',5400.00,60,1),
(29,'AGU-ELE-12','Pack x12, 500 ml',9900.00,32,1),
(30,'CAF-VAI-4','Vainilla, pack x4',9800.00,30,1),
(30,'CAF-MOC-4','Moca, pack x4',9800.00,23,1),
(31,'GRA-FIT-350','Bolsa 350 g',5200.00,38,1),
(31,'GRA-FIT-700','Bolsa 700 g',9500.00,16,1),
(32,'GUA-TRN-M','Talla M',8900.00,22,1),
(32,'GUA-TRN-L','Talla L',8900.00,18,1),
(33,'CIN-LEV-M','Talla M',21900.00,10,1),
(33,'CIN-LEV-L','Talla L',21900.00,8,1),
(34,'BAN-RES-5','Set de 5 bandas',12900.00,28,1),
(35,'CUE-SPD-NEG','Negra',6500.00,34,1),
(35,'CUE-SPD-NAR','Naranja',6500.00,26,1),
(36,'BOT-TER-NEG','Negra 1 L',14900.00,20,1),
(36,'BOT-TER-BLA','Blanca 1 L',14900.00,16,1),
(37,'TOA-MIC-GRI','Gris',4900.00,45,1),
(37,'TOA-MIC-NEG','Negra',4900.00,40,1),
(38,'STR-AGA-NEG','Negras, par',5900.00,31,1),
(39,'ROD-DEP-M','Talla M, par',17500.00,12,1),
(39,'ROD-DEP-L','Talla L, par',17500.00,9,1),
(40,'BOL-DEP-NEG','Negro 40 L',19900.00,14,1),
(40,'BOL-DEP-NAR','Naranja 40 L',19900.00,11,1);

UPDATE tproducto SET Destacado = 1 WHERE Consecutivo = 1;

INSERT INTO tmetodopago (Consecutivo,Nombre,Descripcion,Estado) VALUES
(1,'Tarjeta','Pago simulado con tarjeta de crédito o débito',1),
(2,'SINPE Móvil','Pago simulado por SINPE Móvil',1);

-- Datos de demostracion adicionales (clientes, usuarios, direcciones y pedidos para probar el flujo completo)
-- Todos los clientes usan la contrasena Cliente123*; soporte@lifty.com es administrador (Admin123*).

INSERT INTO tusuario (Consecutivo,Nombre,Apellido1,Apellido2,CorreoElectronico,Telefono,Contrasenna,Estado,ConsecutivoRol,FechaRegistro) VALUES
(3,'Mariana','Rojas','Vargas','mariana.rojas@correo.com','88110001','$2y$10$RYAUAT780rsknhq/o2ZAPO/nyDBLjsyoNMuTx5Ada.e3U4zjKF.D2',1,1,DATE_SUB(NOW(), INTERVAL 40 DAY)),
(4,'Carlos','Jiménez','Mora','carlos.jimenez@correo.com','88110002','$2y$10$RYAUAT780rsknhq/o2ZAPO/nyDBLjsyoNMuTx5Ada.e3U4zjKF.D2',1,1,DATE_SUB(NOW(), INTERVAL 35 DAY)),
(5,'Sofía','Araya','Campos','sofia.araya@correo.com','88110003','$2y$10$RYAUAT780rsknhq/o2ZAPO/nyDBLjsyoNMuTx5Ada.e3U4zjKF.D2',1,1,DATE_SUB(NOW(), INTERVAL 28 DAY)),
(6,'Diego','Quesada','Solano','diego.quesada@correo.com','88110004','$2y$10$RYAUAT780rsknhq/o2ZAPO/nyDBLjsyoNMuTx5Ada.e3U4zjKF.D2',1,1,DATE_SUB(NOW(), INTERVAL 22 DAY)),
(7,'Valeria','Chaves','Brenes','valeria.chaves@correo.com','88110005','$2y$10$RYAUAT780rsknhq/o2ZAPO/nyDBLjsyoNMuTx5Ada.e3U4zjKF.D2',1,1,DATE_SUB(NOW(), INTERVAL 15 DAY)),
(8,'Andrés','Salas','Umaña','andres.salas@correo.com','88110006','$2y$10$RYAUAT780rsknhq/o2ZAPO/nyDBLjsyoNMuTx5Ada.e3U4zjKF.D2',0,1,DATE_SUB(NOW(), INTERVAL 10 DAY)),
(9,'Soporte','Lifty','','soporte@lifty.com','88110007','$2y$10$gYXux5/VZNSWHQGSAsozx.F0Z6CJKXLc5.qU2PxXtYMozBReba3yK',1,2,DATE_SUB(NOW(), INTERVAL 5 DAY));

INSERT INTO tdireccion (ConsecutivoUsuario,Provincia,Canton,Distrito,Detalle,CodigoPostal,Estado) VALUES
(3,'San José','Escazú','San Rafael','100 m norte del Multiplaza','10203',1),
(4,'Heredia','Heredia','Mercedes','Frente a la iglesia, casa 14B','40101',1),
(5,'Alajuela','Alajuela','San José','Condominio Los Robles, casa 7','20101',1),
(6,'Cartago','Cartago','Oriental','50 m sur del parque central','30101',1),
(7,'San José','Curridabat','Granadilla','Del Más x Menos 200 m este','11801',1),
(8,'Guanacaste','Liberia','Liberia','Barrio Capulín, casa verde','50101',1),
(3,'San José','Santa Ana','Pozos','Residencial Lindora, casa 22','10901',1);

INSERT INTO tpedido (ConsecutivoUsuario,ConsecutivoDireccion,ConsecutivoMetodoPago,EstadoPedido,FechaPedido,Subtotal,Impuesto,CostoEnvio,Total) VALUES
(2,1,1,'Entregado',DATE_SUB(DATE_SUB(NOW(), INTERVAL 28 DAY), INTERVAL 3 HOUR),38500.00,5005.00,2500.00,46005.00),
(3,2,2,'Entregado',DATE_SUB(DATE_SUB(NOW(), INTERVAL 26 DAY), INTERVAL 5 HOUR),49700.00,6461.00,2500.00,58661.00),
(4,3,1,'Entregado',DATE_SUB(DATE_SUB(NOW(), INTERVAL 22 DAY), INTERVAL 2 HOUR),40500.00,5265.00,2500.00,48265.00),
(5,4,1,'Entregado',DATE_SUB(DATE_SUB(NOW(), INTERVAL 19 DAY), INTERVAL 6 HOUR),24900.00,3237.00,2500.00,30637.00),
(6,5,2,'Enviado',DATE_SUB(DATE_SUB(NOW(), INTERVAL 7 DAY), INTERVAL 4 HOUR),52400.00,6812.00,2500.00,61712.00),
(3,8,1,'Enviado',DATE_SUB(DATE_SUB(NOW(), INTERVAL 6 DAY), INTERVAL 1 HOUR),25200.00,3276.00,2500.00,30976.00),
(7,6,1,'Enviado',DATE_SUB(DATE_SUB(NOW(), INTERVAL 5 DAY), INTERVAL 7 HOUR),39700.00,5161.00,2500.00,47361.00),
(2,1,2,'Pagado',DATE_SUB(DATE_SUB(NOW(), INTERVAL 3 DAY), INTERVAL 2 HOUR),36500.00,4745.00,2500.00,43745.00),
(4,3,1,'Pagado',DATE_SUB(DATE_SUB(NOW(), INTERVAL 2 DAY), INTERVAL 5 HOUR),31900.00,4147.00,2500.00,38547.00),
(5,4,2,'Pagado',DATE_SUB(DATE_SUB(NOW(), INTERVAL 1 DAY), INTERVAL 3 HOUR),42500.00,5525.00,2500.00,50525.00),
(2,1,1,'Pendiente',DATE_SUB(DATE_SUB(NOW(), INTERVAL 1 DAY), INTERVAL 1 HOUR),53900.00,7007.00,2500.00,63407.00),
(2,1,2,'Pendiente',DATE_SUB(DATE_SUB(NOW(), INTERVAL 0 DAY), INTERVAL 6 HOUR),39400.00,5122.00,2500.00,47022.00),
(6,5,1,'Pendiente',DATE_SUB(DATE_SUB(NOW(), INTERVAL 0 DAY), INTERVAL 3 HOUR),9800.00,1274.00,2500.00,13574.00),
(7,6,1,'Cancelado',DATE_SUB(DATE_SUB(NOW(), INTERVAL 14 DAY), INTERVAL 2 HOUR),34500.00,4485.00,2500.00,41485.00),
(3,2,2,'Cancelado',DATE_SUB(DATE_SUB(NOW(), INTERVAL 9 DAY), INTERVAL 4 HOUR),39400.00,5122.00,2500.00,47022.00);

INSERT INTO tdetallepedido (ConsecutivoPedido,ConsecutivoVariante,Cantidad,PrecioUnitario,Subtotal) VALUES
(1,1,1,32000.00,32000.00),
(1,15,1,6500.00,6500.00),
(2,34,2,18900.00,37800.00),
(2,37,1,11900.00,11900.00),
(3,6,1,22000.00,22000.00),
(3,21,1,18500.00,18500.00),
(4,39,1,24900.00,24900.00),
(5,31,1,39500.00,39500.00),
(5,71,1,12900.00,12900.00),
(6,67,1,8900.00,8900.00),
(6,72,1,6500.00,6500.00),
(6,76,2,4900.00,9800.00),
(7,51,2,16900.00,33800.00),
(7,55,1,5900.00,5900.00),
(8,8,1,27500.00,27500.00),
(8,10,6,1500.00,9000.00),
(9,13,2,12500.00,25000.00),
(9,47,1,6900.00,6900.00),
(10,25,1,12500.00,12500.00),
(10,27,1,13500.00,13500.00),
(10,23,1,16500.00,16500.00),
(11,4,1,48000.00,48000.00),
(11,78,1,5900.00,5900.00),
(12,17,1,24500.00,24500.00),
(12,74,1,14900.00,14900.00),
(13,19,1,9800.00,9800.00),
(14,29,1,34500.00,34500.00),
(15,79,1,17500.00,17500.00),
(15,69,1,21900.00,21900.00);

-- El stock baja por los pedidos ya pagados (Pagado, Enviado y Entregado). Pendiente y Cancelado no descuentan.
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 1;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 6;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 8;
UPDATE tvariante SET Stock = Stock - 6 WHERE Consecutivo = 10;
UPDATE tvariante SET Stock = Stock - 2 WHERE Consecutivo = 13;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 15;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 21;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 23;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 25;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 27;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 31;
UPDATE tvariante SET Stock = Stock - 2 WHERE Consecutivo = 34;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 37;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 39;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 47;
UPDATE tvariante SET Stock = Stock - 2 WHERE Consecutivo = 51;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 55;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 67;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 71;
UPDATE tvariante SET Stock = Stock - 1 WHERE Consecutivo = 72;
UPDATE tvariante SET Stock = Stock - 2 WHERE Consecutivo = 76;

-- ----------------------------------------------------------------------------
-- Procedimientos almacenados
-- ----------------------------------------------------------------------------

DELIMITER ;;

DROP PROCEDURE IF EXISTS spRegistrarError;;
CREATE PROCEDURE spRegistrarError(pMensajeError varchar(8000), pAccion varchar(255), pConsecutivoUsuario int)
BEGIN
	INSERT INTO terror(MensajeError,Accion,ConsecutivoUsuario,FechaHora)
	VALUES (pMensajeError,pAccion,pConsecutivoUsuario,NOW());
END;;

-- ---------- Usuarios y seguridad ----------

DROP PROCEDURE IF EXISTS spRegistrarUsuario;;
CREATE PROCEDURE spRegistrarUsuario(pNombre varchar(50), pApellido1 varchar(50), pApellido2 varchar(50),
	pCorreoElectronico varchar(100), pTelefono varchar(20), pContrasenna varchar(255))
BEGIN
	INSERT INTO tusuario(Nombre,Apellido1,Apellido2,CorreoElectronico,Telefono,Contrasenna,Estado,ConsecutivoRol,FechaRegistro)
	VALUES (pNombre,pApellido1,pApellido2,pCorreoElectronico,pTelefono,pContrasenna,1,1,NOW());
	SELECT LAST_INSERT_ID() AS Consecutivo;
END;;

-- Devuelve la fila (con el hash) solo si el usuario esta activo; PHP verifica la contrasena.
DROP PROCEDURE IF EXISTS spIniciarSesionUsuario;;
CREATE PROCEDURE spIniciarSesionUsuario(pCorreoElectronico varchar(100))
BEGIN
	SELECT U.Consecutivo, U.Nombre, U.Apellido1, U.Apellido2, U.CorreoElectronico, U.Contrasenna,
		   U.Estado, U.ConsecutivoRol, R.NombreRol
	FROM   tusuario U
	INNER JOIN trol R ON U.ConsecutivoRol = R.Consecutivo
	WHERE  U.CorreoElectronico = pCorreoElectronico
	   AND U.Estado = 1;
END;;

DROP PROCEDURE IF EXISTS spConsultarCorreo;;
CREATE PROCEDURE spConsultarCorreo(pCorreoElectronico varchar(100))
BEGIN
	SELECT Consecutivo, Nombre, Apellido1, CorreoElectronico, Estado
	FROM   tusuario
	WHERE  CorreoElectronico = pCorreoElectronico AND Estado = 1;
END;;

DROP PROCEDURE IF EXISTS spConsultarContrasenna;;
CREATE PROCEDURE spConsultarContrasenna(pConsecutivo int)
BEGIN
	SELECT Contrasenna FROM tusuario WHERE Consecutivo = pConsecutivo;
END;;

DROP PROCEDURE IF EXISTS spActualizarContrasenna;;
CREATE PROCEDURE spActualizarContrasenna(pConsecutivo int, pContrasenna varchar(255))
BEGIN
	UPDATE tusuario SET Contrasenna = pContrasenna WHERE Consecutivo = pConsecutivo;
	SELECT ROW_COUNT() > 0 AS Actualizado;
END;;

DROP PROCEDURE IF EXISTS spConsultarUsuario;;
CREATE PROCEDURE spConsultarUsuario(pConsecutivo int)
BEGIN
	SELECT U.Consecutivo, U.Nombre, U.Apellido1, U.Apellido2, U.CorreoElectronico, U.Telefono,
		   U.Estado, U.ConsecutivoRol, R.NombreRol, U.FechaRegistro
	FROM   tusuario U
	INNER JOIN trol R ON U.ConsecutivoRol = R.Consecutivo
	WHERE  U.Consecutivo = pConsecutivo;
END;;

DROP PROCEDURE IF EXISTS spActualizarDatosCuenta;;
CREATE PROCEDURE spActualizarDatosCuenta(pConsecutivo int, pNombre varchar(50), pApellido1 varchar(50),
	pApellido2 varchar(50), pCorreoElectronico varchar(100), pTelefono varchar(20))
BEGIN
	UPDATE tusuario
	SET    Nombre = pNombre, Apellido1 = pApellido1, Apellido2 = pApellido2,
		   CorreoElectronico = pCorreoElectronico, Telefono = pTelefono
	WHERE  Consecutivo = pConsecutivo;
	SELECT 1 AS Actualizado;
END;;

DROP PROCEDURE IF EXISTS spConsultarUsuarios;;
CREATE PROCEDURE spConsultarUsuarios()
BEGIN
	SELECT U.Consecutivo, U.Nombre, U.Apellido1, U.Apellido2, U.CorreoElectronico, U.Telefono,
		   U.Estado, U.ConsecutivoRol, R.NombreRol, U.FechaRegistro
	FROM   tusuario U
	INNER JOIN trol R ON U.ConsecutivoRol = R.Consecutivo
	ORDER BY U.Consecutivo;
END;;

DROP PROCEDURE IF EXISTS spActualizarEstadoUsuario;;
CREATE PROCEDURE spActualizarEstadoUsuario(pConsecutivo int, pEstado tinyint)
BEGIN
	UPDATE tusuario SET Estado = pEstado WHERE Consecutivo = pConsecutivo;
	SELECT ROW_COUNT() > 0 AS Actualizado;
END;;

-- Gestion de usuarios por el administrador (crear y editar con rol y estado)

DROP PROCEDURE IF EXISTS spConsultarRoles;;
CREATE PROCEDURE spConsultarRoles()
BEGIN
	SELECT Consecutivo, NombreRol FROM trol ORDER BY Consecutivo;
END;;

DROP PROCEDURE IF EXISTS spRegistrarUsuarioAdmin;;
CREATE PROCEDURE spRegistrarUsuarioAdmin(pNombre varchar(50), pApellido1 varchar(50), pApellido2 varchar(50),
	pCorreoElectronico varchar(100), pTelefono varchar(20), pContrasenna varchar(255), pConsecutivoRol int)
BEGIN
	INSERT INTO tusuario(Nombre,Apellido1,Apellido2,CorreoElectronico,Telefono,Contrasenna,Estado,ConsecutivoRol,FechaRegistro)
	VALUES (pNombre,pApellido1,pApellido2,pCorreoElectronico,pTelefono,pContrasenna,1,pConsecutivoRol,NOW());
	SELECT LAST_INSERT_ID() AS Consecutivo;
END;;

DROP PROCEDURE IF EXISTS spActualizarUsuarioAdmin;;
CREATE PROCEDURE spActualizarUsuarioAdmin(pConsecutivo int, pNombre varchar(50), pApellido1 varchar(50), pApellido2 varchar(50),
	pCorreoElectronico varchar(100), pTelefono varchar(20), pConsecutivoRol int, pEstado tinyint)
BEGIN
	UPDATE tusuario
	SET    Nombre = pNombre, Apellido1 = pApellido1, Apellido2 = pApellido2,
		   CorreoElectronico = pCorreoElectronico, Telefono = pTelefono,
		   ConsecutivoRol = pConsecutivoRol, Estado = pEstado
	WHERE  Consecutivo = pConsecutivo;
	SELECT 1 AS Actualizado;
END;;

-- ---------- Direcciones ----------

DROP PROCEDURE IF EXISTS spConsultarDirecciones;;
CREATE PROCEDURE spConsultarDirecciones(pConsecutivoUsuario int)
BEGIN
	SELECT Consecutivo, Provincia, Canton, Distrito, Detalle, CodigoPostal
	FROM   tdireccion
	WHERE  ConsecutivoUsuario = pConsecutivoUsuario AND Estado = 1
	ORDER BY Consecutivo;
END;;

DROP PROCEDURE IF EXISTS spRegistrarDireccion;;
CREATE PROCEDURE spRegistrarDireccion(pConsecutivoUsuario int, pProvincia varchar(50), pCanton varchar(50),
	pDistrito varchar(50), pDetalle varchar(250), pCodigoPostal varchar(10))
BEGIN
	INSERT INTO tdireccion(ConsecutivoUsuario,Provincia,Canton,Distrito,Detalle,CodigoPostal,Estado)
	VALUES (pConsecutivoUsuario,pProvincia,pCanton,pDistrito,pDetalle,pCodigoPostal,1);
	SELECT LAST_INSERT_ID() AS Consecutivo;
END;;

DROP PROCEDURE IF EXISTS spActualizarDireccion;;
CREATE PROCEDURE spActualizarDireccion(pConsecutivo int, pConsecutivoUsuario int, pProvincia varchar(50),
	pCanton varchar(50), pDistrito varchar(50), pDetalle varchar(250), pCodigoPostal varchar(10))
BEGIN
	UPDATE tdireccion
	SET    Provincia = pProvincia, Canton = pCanton, Distrito = pDistrito,
		   Detalle = pDetalle, CodigoPostal = pCodigoPostal
	WHERE  Consecutivo = pConsecutivo AND ConsecutivoUsuario = pConsecutivoUsuario;
	SELECT 1 AS Actualizado;
END;;

DROP PROCEDURE IF EXISTS spInactivarDireccion;;
CREATE PROCEDURE spInactivarDireccion(pConsecutivo int, pConsecutivoUsuario int)
BEGIN
	UPDATE tdireccion SET Estado = 0
	WHERE  Consecutivo = pConsecutivo AND ConsecutivoUsuario = pConsecutivoUsuario;
	SELECT ROW_COUNT() > 0 AS Actualizado;
END;;

-- ---------- Categorias y marcas ----------

DROP PROCEDURE IF EXISTS spConsultarCategorias;;
CREATE PROCEDURE spConsultarCategorias(pSoloActivas tinyint)
BEGIN
	SELECT Consecutivo, Nombre, Descripcion, Estado
	FROM   tcategoria
	WHERE  (pSoloActivas = 0 OR Estado = 1)
	ORDER BY Consecutivo;
END;;

DROP PROCEDURE IF EXISTS spRegistrarCategoria;;
CREATE PROCEDURE spRegistrarCategoria(pNombre varchar(50), pDescripcion varchar(150))
BEGIN
	INSERT INTO tcategoria(Nombre,Descripcion,Estado) VALUES (pNombre,pDescripcion,1);
	SELECT LAST_INSERT_ID() AS Consecutivo;
END;;

DROP PROCEDURE IF EXISTS spActualizarCategoria;;
CREATE PROCEDURE spActualizarCategoria(pConsecutivo int, pNombre varchar(50), pDescripcion varchar(150), pEstado tinyint)
BEGIN
	UPDATE tcategoria SET Nombre = pNombre, Descripcion = pDescripcion, Estado = pEstado
	WHERE Consecutivo = pConsecutivo;
	SELECT 1 AS Actualizado;
END;;

DROP PROCEDURE IF EXISTS spConsultarMarcas;;
CREATE PROCEDURE spConsultarMarcas(pSoloActivas tinyint)
BEGIN
	SELECT Consecutivo, Nombre, Descripcion, Estado
	FROM   tmarca
	WHERE  (pSoloActivas = 0 OR Estado = 1)
	ORDER BY Consecutivo;
END;;

DROP PROCEDURE IF EXISTS spRegistrarMarca;;
CREATE PROCEDURE spRegistrarMarca(pNombre varchar(50), pDescripcion varchar(150))
BEGIN
	INSERT INTO tmarca(Nombre,Descripcion,Estado) VALUES (pNombre,pDescripcion,1);
	SELECT LAST_INSERT_ID() AS Consecutivo;
END;;

DROP PROCEDURE IF EXISTS spActualizarMarca;;
CREATE PROCEDURE spActualizarMarca(pConsecutivo int, pNombre varchar(50), pDescripcion varchar(150), pEstado tinyint)
BEGIN
	UPDATE tmarca SET Nombre = pNombre, Descripcion = pDescripcion, Estado = pEstado
	WHERE Consecutivo = pConsecutivo;
	SELECT 1 AS Actualizado;
END;;

-- ---------- Productos y variantes ----------

-- Vista del administrador: todos los productos con resumen de variantes.
DROP PROCEDURE IF EXISTS spConsultarProductosAdmin;;
CREATE PROCEDURE spConsultarProductosAdmin()
BEGIN
	SELECT P.Consecutivo, P.Nombre, P.Descripcion, P.Imagen, P.Estado, P.Destacado,
		   P.ConsecutivoCategoria, C.Nombre AS NombreCategoria,
		   P.ConsecutivoMarca, M.Nombre AS NombreMarca,
		   (SELECT COUNT(*) FROM tvariante V WHERE V.ConsecutivoProducto = P.Consecutivo AND V.Estado = 1) AS Variantes,
		   (SELECT IFNULL(SUM(V.Stock),0) FROM tvariante V WHERE V.ConsecutivoProducto = P.Consecutivo AND V.Estado = 1) AS StockTotal,
		   (SELECT MIN(V.Precio) FROM tvariante V WHERE V.ConsecutivoProducto = P.Consecutivo AND V.Estado = 1) AS PrecioDesde
	FROM   tproducto P
	INNER JOIN tcategoria C ON P.ConsecutivoCategoria = C.Consecutivo
	INNER JOIN tmarca M ON P.ConsecutivoMarca = M.Consecutivo
	ORDER BY P.Consecutivo DESC;
END;;

-- Catalogo publico (paginado): solo productos activos, con categoria y marca activas y al menos una variante activa.
DROP PROCEDURE IF EXISTS spConsultarCatalogo;;
CREATE PROCEDURE spConsultarCatalogo(pConsecutivoCategoria int, pConsecutivoMarca int, pBusqueda varchar(100),
	pLimite int, pDesplazamiento int)
BEGIN
	SELECT P.Consecutivo, P.Nombre, P.Descripcion, P.Imagen, P.Destacado,
		   C.Nombre AS NombreCategoria, M.Nombre AS NombreMarca,
		   MIN(V.Precio) AS PrecioDesde, SUM(V.Stock) AS StockTotal
	FROM   tproducto P
	INNER JOIN tcategoria C ON P.ConsecutivoCategoria = C.Consecutivo AND C.Estado = 1
	INNER JOIN tmarca M ON P.ConsecutivoMarca = M.Consecutivo AND M.Estado = 1
	INNER JOIN tvariante V ON V.ConsecutivoProducto = P.Consecutivo AND V.Estado = 1
	WHERE  P.Estado = 1
	   AND (pConsecutivoCategoria = 0 OR P.ConsecutivoCategoria = pConsecutivoCategoria)
	   AND (pConsecutivoMarca = 0 OR P.ConsecutivoMarca = pConsecutivoMarca)
	   AND (pBusqueda = '' OR P.Nombre LIKE CONCAT('%', pBusqueda, '%'))
	GROUP BY P.Consecutivo, P.Nombre, P.Descripcion, P.Imagen, P.Destacado, C.Nombre, M.Nombre
	ORDER BY P.Destacado DESC, P.Nombre
	LIMIT pLimite OFFSET pDesplazamiento;
END;;

-- Cantidad total de productos del catalogo con los mismos filtros (para calcular las paginas).
DROP PROCEDURE IF EXISTS spContarCatalogo;;
CREATE PROCEDURE spContarCatalogo(pConsecutivoCategoria int, pConsecutivoMarca int, pBusqueda varchar(100))
BEGIN
	SELECT COUNT(DISTINCT P.Consecutivo) AS Total
	FROM   tproducto P
	INNER JOIN tcategoria C ON P.ConsecutivoCategoria = C.Consecutivo AND C.Estado = 1
	INNER JOIN tmarca M ON P.ConsecutivoMarca = M.Consecutivo AND M.Estado = 1
	INNER JOIN tvariante V ON V.ConsecutivoProducto = P.Consecutivo AND V.Estado = 1
	WHERE  P.Estado = 1
	   AND (pConsecutivoCategoria = 0 OR P.ConsecutivoCategoria = pConsecutivoCategoria)
	   AND (pConsecutivoMarca = 0 OR P.ConsecutivoMarca = pConsecutivoMarca)
	   AND (pBusqueda = '' OR P.Nombre LIKE CONCAT('%', pBusqueda, '%'));
END;;

DROP PROCEDURE IF EXISTS spConsultarProducto;;
CREATE PROCEDURE spConsultarProducto(pConsecutivo int)
BEGIN
	SELECT P.Consecutivo, P.Nombre, P.Descripcion, P.Imagen, P.Estado, P.Destacado,
		   P.ConsecutivoCategoria, C.Nombre AS NombreCategoria,
		   P.ConsecutivoMarca, M.Nombre AS NombreMarca
	FROM   tproducto P
	INNER JOIN tcategoria C ON P.ConsecutivoCategoria = C.Consecutivo
	INNER JOIN tmarca M ON P.ConsecutivoMarca = M.Consecutivo
	WHERE  P.Consecutivo = pConsecutivo;
END;;

DROP PROCEDURE IF EXISTS spRegistrarProducto;;
CREATE PROCEDURE spRegistrarProducto(pConsecutivoCategoria int, pConsecutivoMarca int, pNombre varchar(100),
	pDescripcion text, pImagen varchar(255))
BEGIN
	INSERT INTO tproducto(ConsecutivoCategoria,ConsecutivoMarca,Nombre,Descripcion,Imagen,Estado,FechaCreacion)
	VALUES (pConsecutivoCategoria,pConsecutivoMarca,pNombre,pDescripcion,pImagen,1,NOW());
	SELECT LAST_INSERT_ID() AS Consecutivo;
END;;

DROP PROCEDURE IF EXISTS spActualizarProducto;;
CREATE PROCEDURE spActualizarProducto(pConsecutivo int, pConsecutivoCategoria int, pConsecutivoMarca int,
	pNombre varchar(100), pDescripcion text, pImagen varchar(255), pEstado tinyint, pDestacado tinyint)
BEGIN
	-- Solo un producto puede estar destacado en la portada.
	IF pDestacado = 1 THEN
		UPDATE tproducto SET Destacado = 0 WHERE Consecutivo <> pConsecutivo;
	END IF;

	UPDATE tproducto
	SET    ConsecutivoCategoria = pConsecutivoCategoria, ConsecutivoMarca = pConsecutivoMarca,
		   Nombre = pNombre, Descripcion = pDescripcion, Imagen = pImagen, Estado = pEstado, Destacado = pDestacado
	WHERE  Consecutivo = pConsecutivo;
	SELECT 1 AS Actualizado;
END;;

DROP PROCEDURE IF EXISTS spConsultarVariantes;;
CREATE PROCEDURE spConsultarVariantes(pConsecutivoProducto int, pSoloActivas tinyint)
BEGIN
	SELECT Consecutivo, ConsecutivoProducto, Sku, Presentacion, Precio, Stock, Estado
	FROM   tvariante
	WHERE  ConsecutivoProducto = pConsecutivoProducto AND (pSoloActivas = 0 OR Estado = 1)
	ORDER BY Consecutivo;
END;;

-- Variante con datos del producto, para el carrito y el detalle del pedido.
DROP PROCEDURE IF EXISTS spConsultarVariante;;
CREATE PROCEDURE spConsultarVariante(pConsecutivo int)
BEGIN
	SELECT V.Consecutivo, V.ConsecutivoProducto, V.Sku, V.Presentacion, V.Precio, V.Stock, V.Estado,
		   P.Nombre AS NombreProducto, P.Imagen, P.Estado AS EstadoProducto
	FROM   tvariante V
	INNER JOIN tproducto P ON V.ConsecutivoProducto = P.Consecutivo
	WHERE  V.Consecutivo = pConsecutivo;
END;;

DROP PROCEDURE IF EXISTS spRegistrarVariante;;
CREATE PROCEDURE spRegistrarVariante(pConsecutivoProducto int, pSku varchar(30), pPresentacion varchar(100),
	pPrecio decimal(10,2), pStock int)
BEGIN
	INSERT INTO tvariante(ConsecutivoProducto,Sku,Presentacion,Precio,Stock,Estado)
	VALUES (pConsecutivoProducto,pSku,pPresentacion,pPrecio,pStock,1);
	SELECT LAST_INSERT_ID() AS Consecutivo;
END;;

DROP PROCEDURE IF EXISTS spActualizarVariante;;
CREATE PROCEDURE spActualizarVariante(pConsecutivo int, pSku varchar(30), pPresentacion varchar(100),
	pPrecio decimal(10,2), pStock int, pEstado tinyint)
BEGIN
	UPDATE tvariante
	SET    Sku = pSku, Presentacion = pPresentacion, Precio = pPrecio, Stock = pStock, Estado = pEstado
	WHERE  Consecutivo = pConsecutivo;
	SELECT 1 AS Actualizado;
END;;

-- ---------- Metodos de pago ----------

DROP PROCEDURE IF EXISTS spConsultarMetodosPago;;
CREATE PROCEDURE spConsultarMetodosPago(pSoloActivos tinyint)
BEGIN
	SELECT Consecutivo, Nombre, Descripcion, Estado
	FROM   tmetodopago
	WHERE  (pSoloActivos = 0 OR Estado = 1)
	ORDER BY Consecutivo;
END;;

DROP PROCEDURE IF EXISTS spActualizarEstadoMetodoPago;;
CREATE PROCEDURE spActualizarEstadoMetodoPago(pConsecutivo int, pEstado tinyint)
BEGIN
	UPDATE tmetodopago SET Estado = pEstado WHERE Consecutivo = pConsecutivo;
	SELECT 1 AS Actualizado;
END;;

-- ---------- Pedidos ----------

DROP PROCEDURE IF EXISTS spRegistrarPedido;;
CREATE PROCEDURE spRegistrarPedido(pConsecutivoUsuario int, pConsecutivoDireccion int, pConsecutivoMetodoPago int,
	pSubtotal decimal(10,2), pImpuesto decimal(10,2), pCostoEnvio decimal(10,2), pTotal decimal(10,2))
BEGIN
	INSERT INTO tpedido(ConsecutivoUsuario,ConsecutivoDireccion,ConsecutivoMetodoPago,EstadoPedido,FechaPedido,Subtotal,Impuesto,CostoEnvio,Total)
	VALUES (pConsecutivoUsuario,pConsecutivoDireccion,pConsecutivoMetodoPago,'Pendiente',NOW(),pSubtotal,pImpuesto,pCostoEnvio,pTotal);
	SELECT LAST_INSERT_ID() AS Consecutivo;
END;;

DROP PROCEDURE IF EXISTS spRegistrarDetallePedido;;
CREATE PROCEDURE spRegistrarDetallePedido(pConsecutivoPedido int, pConsecutivoVariante int, pCantidad int, pPrecioUnitario decimal(10,2))
BEGIN
	INSERT INTO tdetallepedido(ConsecutivoPedido,ConsecutivoVariante,Cantidad,PrecioUnitario,Subtotal)
	VALUES (pConsecutivoPedido,pConsecutivoVariante,pCantidad,pPrecioUnitario,pCantidad * pPrecioUnitario);
	SELECT 1 AS Registrado;
END;;

-- Pago simulado: valida existencias, descuenta stock y pasa el pedido a Pagado (una sola transaccion).
-- Devuelve Pagado = 1 si se pudo, 0 si el pedido no estaba pendiente o no hay stock suficiente.
DROP PROCEDURE IF EXISTS spPagarPedido;;
CREATE PROCEDURE spPagarPedido(pConsecutivo int, pConsecutivoUsuario int)
BEGIN
	DECLARE vFaltantes int DEFAULT 0;
	DECLARE vPendiente int DEFAULT 0;

	START TRANSACTION;

	SELECT COUNT(*) INTO vPendiente FROM tpedido
	WHERE Consecutivo = pConsecutivo AND ConsecutivoUsuario = pConsecutivoUsuario AND EstadoPedido = 'Pendiente';

	SELECT COUNT(*) INTO vFaltantes
	FROM   tdetallepedido D
	INNER JOIN tvariante V ON D.ConsecutivoVariante = V.Consecutivo
	WHERE  D.ConsecutivoPedido = pConsecutivo AND (V.Stock < D.Cantidad OR V.Estado = 0);

	IF vPendiente = 1 AND vFaltantes = 0 THEN
		UPDATE tvariante V
		INNER JOIN tdetallepedido D ON D.ConsecutivoVariante = V.Consecutivo
		SET    V.Stock = V.Stock - D.Cantidad
		WHERE  D.ConsecutivoPedido = pConsecutivo;

		UPDATE tpedido SET EstadoPedido = 'Pagado' WHERE Consecutivo = pConsecutivo;
		COMMIT;
		SELECT 1 AS Pagado;
	ELSE
		ROLLBACK;
		SELECT 0 AS Pagado;
	END IF;
END;;

DROP PROCEDURE IF EXISTS spConsultarPedidos;;
CREATE PROCEDURE spConsultarPedidos(pConsecutivoUsuario int)
BEGIN
	SELECT P.Consecutivo, P.ConsecutivoUsuario, CONCAT(U.Nombre,' ',U.Apellido1) AS NombreCliente,
		   P.EstadoPedido, P.FechaPedido, P.Subtotal, P.Impuesto, P.CostoEnvio, P.Total,
		   MP.Nombre AS NombreMetodoPago
	FROM   tpedido P
	INNER JOIN tusuario U ON P.ConsecutivoUsuario = U.Consecutivo
	INNER JOIN tmetodopago MP ON P.ConsecutivoMetodoPago = MP.Consecutivo
	WHERE  (pConsecutivoUsuario = 0 OR P.ConsecutivoUsuario = pConsecutivoUsuario)
	ORDER BY P.Consecutivo DESC;
END;;

DROP PROCEDURE IF EXISTS spConsultarPedido;;
CREATE PROCEDURE spConsultarPedido(pConsecutivo int)
BEGIN
	SELECT P.Consecutivo, P.ConsecutivoUsuario, CONCAT(U.Nombre,' ',U.Apellido1) AS NombreCliente,
		   U.CorreoElectronico, P.EstadoPedido, P.FechaPedido, P.Subtotal, P.Impuesto, P.CostoEnvio, P.Total,
		   MP.Nombre AS NombreMetodoPago,
		   CONCAT(D.Detalle,', ',D.Distrito,', ',D.Canton,', ',D.Provincia) AS Direccion
	FROM   tpedido P
	INNER JOIN tusuario U ON P.ConsecutivoUsuario = U.Consecutivo
	INNER JOIN tmetodopago MP ON P.ConsecutivoMetodoPago = MP.Consecutivo
	INNER JOIN tdireccion D ON P.ConsecutivoDireccion = D.Consecutivo
	WHERE  P.Consecutivo = pConsecutivo;
END;;

DROP PROCEDURE IF EXISTS spConsultarDetallePedido;;
CREATE PROCEDURE spConsultarDetallePedido(pConsecutivoPedido int)
BEGIN
	SELECT D.Consecutivo, D.ConsecutivoVariante, D.Cantidad, D.PrecioUnitario, D.Subtotal,
		   V.Sku, V.Presentacion, P.Nombre AS NombreProducto
	FROM   tdetallepedido D
	INNER JOIN tvariante V ON D.ConsecutivoVariante = V.Consecutivo
	INNER JOIN tproducto P ON V.ConsecutivoProducto = P.Consecutivo
	WHERE  D.ConsecutivoPedido = pConsecutivoPedido;
END;;

-- Cambio de estado por el administrador. Si se cancela un pedido ya pagado, se devuelve el stock.
DROP PROCEDURE IF EXISTS spActualizarEstadoPedido;;
CREATE PROCEDURE spActualizarEstadoPedido(pConsecutivo int, pEstadoPedido varchar(20))
BEGIN
	DECLARE vActual varchar(20) DEFAULT NULL;

	SELECT EstadoPedido INTO vActual FROM tpedido WHERE Consecutivo = pConsecutivo;

	IF vActual IS NOT NULL AND vActual <> pEstadoPedido THEN
		IF pEstadoPedido = 'Cancelado' AND vActual IN ('Pagado','Enviado') THEN
			UPDATE tvariante V
			INNER JOIN tdetallepedido D ON D.ConsecutivoVariante = V.Consecutivo
			SET    V.Stock = V.Stock + D.Cantidad
			WHERE  D.ConsecutivoPedido = pConsecutivo;
		END IF;
		UPDATE tpedido SET EstadoPedido = pEstadoPedido WHERE Consecutivo = pConsecutivo;
		SELECT 1 AS Actualizado;
	ELSE
		SELECT 0 AS Actualizado;
	END IF;
END;;

-- ---------- Reportes ----------

DROP PROCEDURE IF EXISTS spReportePedidosPorEstado;;
CREATE PROCEDURE spReportePedidosPorEstado()
BEGIN
	SELECT EstadoPedido, COUNT(*) AS Cantidad, IFNULL(SUM(Total),0) AS Monto
	FROM   tpedido
	GROUP BY EstadoPedido
	ORDER BY FIELD(EstadoPedido,'Pendiente','Pagado','Enviado','Entregado','Cancelado');
END;;

DROP PROCEDURE IF EXISTS spReporteStockBajo;;
CREATE PROCEDURE spReporteStockBajo(pUmbral int)
BEGIN
	SELECT V.Consecutivo, P.Nombre AS NombreProducto, V.Sku, V.Presentacion, V.Stock
	FROM   tvariante V
	INNER JOIN tproducto P ON V.ConsecutivoProducto = P.Consecutivo
	WHERE  V.Estado = 1 AND P.Estado = 1 AND V.Stock <= pUmbral
	ORDER BY V.Stock, P.Nombre;
END;;

DROP PROCEDURE IF EXISTS spConsultarResumen;;
CREATE PROCEDURE spConsultarResumen()
BEGIN
	SELECT (SELECT COUNT(*) FROM tproducto WHERE Estado = 1) AS Productos,
		   (SELECT IFNULL(SUM(Stock),0) FROM tvariante WHERE Estado = 1) AS UnidadesStock,
		   (SELECT COUNT(*) FROM tpedido) AS Pedidos,
		   (SELECT COUNT(*) FROM tpedido WHERE EstadoPedido = 'Pendiente') AS PedidosPendientes,
		   (SELECT IFNULL(SUM(Total),0) FROM tpedido WHERE EstadoPedido IN ('Pagado','Enviado','Entregado')) AS Ventas,
		   (SELECT COUNT(*) FROM tusuario WHERE ConsecutivoRol = 1 AND Estado = 1) AS Clientes;
END;;

DELIMITER ;
