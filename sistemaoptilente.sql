-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-06-2025 a las 15:26:32
-- Versión del servidor: 8.0.30
-- Versión de PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistemaoptilente`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cristales`
--

CREATE TABLE `cristales` (
  `id_cristal` int NOT NULL,
  `marca` varchar(100) NOT NULL,
  `tipo_cristal` enum('monofocal','bifocal','multifocal','propio') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'monofocal',
  `material_cristal` enum('policarbonato','hi-index','tallados','propio') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'policarbonato',
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int NOT NULL DEFAULT '1',
  `contador_venta` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `cristales`
--

INSERT INTO `cristales` (`id_cristal`, `marca`, `tipo_cristal`, `material_cristal`, `precio`, `cantidad`, `contador_venta`) VALUES
(1, 'Essilor', 'monofocal', 'policarbonato', 55.00, 13, 8),
(2, 'Zeiss', 'bifocal', 'hi-index', 85.00, 15, 1),
(3, 'Hoya', 'multifocal', 'tallados', 120.00, 3, 9),
(4, 'Varilux', 'monofocal', 'hi-index', 65.00, 25, 0),
(5, 'CRISTAL PROPIO', 'propio', 'propio', 0.00, 99999996, 2),
(6, 'Symetry', 'multifocal', 'hi-index', 30.00, 3, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cristales_proveedores`
--

CREATE TABLE `cristales_proveedores` (
  `id_proveedor_cristal` int NOT NULL,
  `id_cristal` int NOT NULL,
  `id_proveedor` int NOT NULL,
  `cantidad` int NOT NULL,
  `fecha_orden` date NOT NULL,
  `num_orden_compra` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `cristales_proveedores`
--

INSERT INTO `cristales_proveedores` (`id_proveedor_cristal`, `id_cristal`, `id_proveedor`, `cantidad`, `fecha_orden`, `num_orden_compra`) VALUES
(1, 1, 1, 50, '2024-02-01', 1001),
(2, 1, 2, 30, '2024-02-02', 1002),
(3, 2, 2, 25, '2024-02-03', 1003),
(4, 3, 3, 20, '2024-02-04', 1004),
(5, 4, 1, 40, '2024-02-05', 1005);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_clientes`
--

CREATE TABLE `datos_clientes` (
  `id_cliente` int NOT NULL,
  `cedula_cliente` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `num_telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `correo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `datos_clientes`
--

INSERT INTO `datos_clientes` (`id_cliente`, `cedula_cliente`, `nombre`, `apellido`, `num_telefono`, `correo`) VALUES
(2, '31278502', 'Gabriel Bastardo', '', '04120805543', ''),
(4, '12665029', 'José Javier', 'Bastardo Marín', '3055923972', 'elderbastardom@gmail.com'),
(5, '9256344', 'Antonio Bermudez', '', '04265868841', 'antonio@gmail.com'),
(8, '5895895', 'Pedro', 'Machado', '111222333', ''),
(9, '14419336', 'Ana', 'Coronado', '04121850324', ''),
(10, '32555222', 'Alfonzo', 'Coronado', '04248133681', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id_empleado` int NOT NULL,
  `cedula_empleado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nombre_empleado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `apellido_empleado` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `usuario` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `clave` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `cargo` enum('empleado','gerente') NOT NULL,
  `correo` varchar(100) NOT NULL,
  `num_telefono` varchar(20) NOT NULL,
  `estado_empleado` enum('activo','inactivo','retirado') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id_empleado`, `cedula_empleado`, `nombre_empleado`, `apellido_empleado`, `usuario`, `clave`, `cargo`, `correo`, `num_telefono`, `estado_empleado`) VALUES
(1, '31278502', 'Gabriel', 'Bastardo', '31278502', '123', 'empleado', 'gabo1234@gmail.com', '04120805543', 'activo'),
(2, '31156858', 'Saul', 'Ramos', '31156858', '123', 'gerente', 'saul@gmail.com', '04125555555', 'activo'),
(3, '12665029', 'Jose Bastardo', '', '12665029', '123', 'empleado', 'josebastardo@gmail.com', '04248133681', 'inactivo'),
(4, '14419336', 'Ana', 'Rodriguez', '14419336', '123', 'empleado', 'ana@gmail.com', '04121850324', 'retirado'),
(5, '23232322', 'luis', 'astudillo', 'luisastu', '123', 'empleado', 'tugatita123@gmail.com', '04159895467', 'retirado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `monturas`
--

CREATE TABLE `monturas` (
  `id_montura` int NOT NULL,
  `marca` varchar(100) NOT NULL,
  `material` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int NOT NULL DEFAULT '1',
  `contador_venta` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `monturas`
--

INSERT INTO `monturas` (`id_montura`, `marca`, `material`, `precio`, `cantidad`, `contador_venta`) VALUES
(1, 'Ray-Ban', 'Metal', 180.00, 22, NULL),
(2, 'Oakley', 'Titanio', 220.00, 10, NULL),
(3, 'Persol', 'Acetato', 250.00, 9, NULL),
(4, 'Gucci', 'Acetato Premium', 320.00, 10, NULL),
(5, 'MONTURA PROPIA', 'PROPIO', 0.00, 99999997, 1),
(8, 'Diteil', 'Acero', 30.00, 9, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `monturas_proveedores`
--

CREATE TABLE `monturas_proveedores` (
  `id_proveedor_montura` int NOT NULL,
  `id_montura` int NOT NULL,
  `id_proveedor` int NOT NULL,
  `cantidad` int NOT NULL,
  `fecha_orden` date NOT NULL,
  `num_orden_compra` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `monturas_proveedores`
--

INSERT INTO `monturas_proveedores` (`id_proveedor_montura`, `id_montura`, `id_proveedor`, `cantidad`, `fecha_orden`, `num_orden_compra`) VALUES
(1, 1, 1, 30, '2024-02-01', 2001),
(2, 2, 2, 20, '2024-02-02', 2002),
(3, 3, 3, 15, '2024-02-03', 2003),
(4, 4, 1, 10, '2024-02-04', 2004),
(5, 1, 3, 10, '2025-05-14', 1747248958),
(7, 8, 4, 5, '2025-06-08', 1749386072);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id_pedido` int NOT NULL,
  `id_empleado` int NOT NULL,
  `id_montura` int NOT NULL,
  `id_cristal1` int NOT NULL,
  `id_cristal2` int NOT NULL,
  `id_prescripcion` int NOT NULL,
  `cedula_cliente` varchar(20) NOT NULL,
  `fecha_pedido` date NOT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `estado_pedido` enum('no disponible','disponible','entregado') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `cantidad` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id_pedido`, `id_empleado`, `id_montura`, `id_cristal1`, `id_cristal2`, `id_prescripcion`, `cedula_cliente`, `fecha_pedido`, `fecha_entrega`, `estado_pedido`, `cantidad`) VALUES
(1, 1, 1, 1, 1, 1, '31278502', '2024-02-20', NULL, 'no disponible', 1),
(2, 1, 2, 2, 2, 2, '12665029', '2024-02-21', NULL, 'no disponible', 1),
(3, 2, 3, 3, 3, 3, '9256344', '2024-02-22', NULL, 'no disponible', 1),
(4, 2, 1, 1, 1, 4, '12665029', '2025-05-14', NULL, 'no disponible', 1),
(5, 2, 5, 1, 5, 5, '5895895', '2025-05-14', NULL, 'no disponible', 1),
(7, 2, 5, 6, 5, 7, '12665029', '2025-05-15', '2025-05-18', 'entregado', 1),
(8, 2, 8, 5, 6, 8, '32555222', '2025-06-08', NULL, 'disponible', 1),
(10, 2, 1, 1, 1, 1, '31278502', '2025-06-09', NULL, 'no disponible', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_seguridad`
--

CREATE TABLE `preguntas_seguridad` (
  `id_pregunta` int NOT NULL,
  `id_empleado` int NOT NULL,
  `pregunta` varchar(255) NOT NULL,
  `respuesta` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `preguntas_seguridad`
--

INSERT INTO `preguntas_seguridad` (`id_pregunta`, `id_empleado`, `pregunta`, `respuesta`) VALUES
(1, 2, '¿En qué ciudad naciste?', 'Cumana'),
(5, 2, '¿Cuál fue tu primer trabajo?', 'optilente'),
(6, 2, '¿Cuál es tu color favorito?', 'negro'),
(8, 2, '¿Cuál es el nombre de tu primera mascota?', 'polar'),
(9, 1, '¿Cuál es el nombre de tu mejor amigo de la infancia?', 'ismael'),
(10, 1, '¿Cuál es tu película favorita?', 'baby driver'),
(11, 1, '¿En qué ciudad naciste?', 'cumana'),
(12, 1, '¿Cuál es el nombre de tu primera mascota?', 'fortachon');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prescripcion`
--

CREATE TABLE `prescripcion` (
  `id_prescripcion` int NOT NULL,
  `id_cliente` int NOT NULL,
  `fecha_emision` date NOT NULL,
  `OD_esfera` varchar(20) NOT NULL,
  `OD_cilindro` varchar(20) NOT NULL,
  `OD_eje` varchar(20) NOT NULL,
  `OI_esfera` varchar(20) NOT NULL,
  `OI_cilindro` varchar(20) NOT NULL,
  `OI_eje` varchar(20) NOT NULL,
  `adicion` varchar(20) DEFAULT NULL,
  `altura_pupilar` varchar(20) DEFAULT NULL,
  `distancia_pupilar` varchar(20) DEFAULT NULL,
  `observacion` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `prescripcion`
--

INSERT INTO `prescripcion` (`id_prescripcion`, `id_cliente`, `fecha_emision`, `OD_esfera`, `OD_cilindro`, `OD_eje`, `OI_esfera`, `OI_cilindro`, `OI_eje`, `adicion`, `altura_pupilar`, `distancia_pupilar`, `observacion`) VALUES
(1, 2, '2024-02-15', '-2.50', '-1.25', '180', '-2.75', '-1.00', '170', '+2.50', '32', '62', 'Lentes para visión de cerca y lejos'),
(2, 4, '2024-02-16', '-1.75', '-0.75', '90', '-2.00', '-0.50', '85', NULL, '30', '60', 'Lentes para trabajo de oficina'),
(3, 5, '2024-02-17', '-3.25', '-1.50', '175', '-3.50', '-1.25', '165', '+2.00', '34', '64', 'Lentes multifocales'),
(4, 4, '2025-05-14', '2', '2', '2', '2', '2', '2', '', '45', '', ''),
(5, 8, '2025-05-14', '-2', '-0.5', '190', '0', '0', '0', '', '45', '', 'el cliente quiere cambiar nada mas el cristal derecho'),
(6, 9, '2025-05-14', '2', '-0.5', '200', '2', '-0.5', '180', '', '', '', ''),
(7, 4, '2025-05-15', '2', '-0.5', '200', '0', '0', '0', '', '', '', ''),
(8, 10, '2025-06-08', '0', '0', '0', '+2.00', '-0.5', '190', '', '', '', 'tiene un cristal propio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `id_proveedor` int NOT NULL,
  `rif_proveedor` varchar(100) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`id_proveedor`, `rif_proveedor`, `nombre`, `direccion`, `telefono`, `correo`) VALUES
(1, 'J-12345678-9', 'Óptica Global', 'Av. Principal, Caracas', '0212-5551234', 'contacto@opticaglobal.com'),
(2, 'J-87654321-0', 'Visión Médica', 'Calle 50, Maracaibo', '0261-7778888', 'ventas@visionmedica.com'),
(3, 'J-45678901-2', 'Lentes Express', 'Centro Comercial, Valencia', '0241-9992222', 'info@lentesexpress.com'),
(4, 'J-45678754-2', 'Inmodeca c.a', '2240 Nw 114th Ave', '3055923972', 'Inmodeca@gmail.com');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cristales`
--
ALTER TABLE `cristales`
  ADD PRIMARY KEY (`id_cristal`);

--
-- Indices de la tabla `cristales_proveedores`
--
ALTER TABLE `cristales_proveedores`
  ADD PRIMARY KEY (`id_proveedor_cristal`),
  ADD UNIQUE KEY `num_orden_compra` (`num_orden_compra`),
  ADD KEY `id_cristal` (`id_cristal`),
  ADD KEY `id_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `datos_clientes`
--
ALTER TABLE `datos_clientes`
  ADD PRIMARY KEY (`id_cliente`,`cedula_cliente`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id_empleado`),
  ADD UNIQUE KEY `username` (`usuario`),
  ADD UNIQUE KEY `cedula_empleado` (`cedula_empleado`);

--
-- Indices de la tabla `monturas`
--
ALTER TABLE `monturas`
  ADD PRIMARY KEY (`id_montura`);

--
-- Indices de la tabla `monturas_proveedores`
--
ALTER TABLE `monturas_proveedores`
  ADD PRIMARY KEY (`id_proveedor_montura`),
  ADD UNIQUE KEY `num_orden_compra` (`num_orden_compra`),
  ADD KEY `id_montura` (`id_montura`),
  ADD KEY `id_proveedor` (`id_proveedor`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `id_montura` (`id_montura`),
  ADD KEY `id_cristal` (`id_cristal1`),
  ADD KEY `id_empleado` (`id_empleado`),
  ADD KEY `cedula_cliente` (`cedula_cliente`),
  ADD KEY `id_prescripcion` (`id_prescripcion`);

--
-- Indices de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  ADD PRIMARY KEY (`id_pregunta`),
  ADD KEY `id_empleado` (`id_empleado`);

--
-- Indices de la tabla `prescripcion`
--
ALTER TABLE `prescripcion`
  ADD PRIMARY KEY (`id_prescripcion`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`id_proveedor`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cristales`
--
ALTER TABLE `cristales`
  MODIFY `id_cristal` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `cristales_proveedores`
--
ALTER TABLE `cristales_proveedores`
  MODIFY `id_proveedor_cristal` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `datos_clientes`
--
ALTER TABLE `datos_clientes`
  MODIFY `id_cliente` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id_empleado` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `monturas`
--
ALTER TABLE `monturas`
  MODIFY `id_montura` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `monturas_proveedores`
--
ALTER TABLE `monturas_proveedores`
  MODIFY `id_proveedor_montura` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id_pedido` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  MODIFY `id_pregunta` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `prescripcion`
--
ALTER TABLE `prescripcion`
  MODIFY `id_prescripcion` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `id_proveedor` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  ADD CONSTRAINT `preguntas_seguridad_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
