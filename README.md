# Sistema de Gestión OPTILENTE 2020

[![Top Langs](https://img.shields.io/github/languages/top/gabozzz15/optilente_control)](https://github.com/gabozzz15/optilente_control)

Sistema de gestión integral para ópticas, diseñado para facilitar el control de inventarios, pedidos, proveedores, empleados y reportes.

[![image.png](https://i.postimg.cc/k51JVczp/image.png)](https://postimg.cc/06SRFp8Y)
[![image.png](https://i.postimg.cc/5t01Mzyq/image.png)](https://postimg.cc/9DvsdrGr)
[![image.png](https://i.postimg.cc/wxsqvDVw/image.png)](https://postimg.cc/bdpcVD4n)

## Descripción

Es un sistema desarrollado en PHP que permite administrar de manera eficiente los recursos y operaciones de una óptica. Incluye funcionalidades para la gestión de inventarios, control de pedidos, administración de usuarios y generación de reportes en formatos PDF y Excel.

## Requisitos

- Servidor con PHP 8.1 o superior
- Servidor MySQL 8.0 o superior
- Biblioteca PhpSpreadsheet (incluida en composer.json)

## Instalación

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/gabozzz15/optilente_control.git
   ```
2. Instalar dependencias con Composer:
   ```bash
   composer install
   ```
3. Configurar la base de datos importando el archivo `sistemaoptilente.sql` en MySQL.
4. Configurar la conexión a la base de datos en `./inc/conexionbd.php`.
5. Configurar el servidor web para apuntar al directorio del proyecto.

## Uso

- Acceder al sistema mediante el archivo `index.php`.
- Iniciar sesión con usuario y contraseña.
- Navegar por las secciones de inventario, pedidos, proveedores, usuarios y reportes.
- Generar reportes en PDF o exportar a Excel desde la sección de reportes.

## Funcionalidades principales

- Gestión de inventarios con control de stock y precios.
- Administración de proveedores y empleados.
- Control de pedidos y ventas.
- Generación de reportes detallados en PDF y Excel.
- Conversión automática de montos en dólares a bolívares según precio oficial del BCV.

## Base de datos

La base de datos `sistemaoptilente` contiene tablas para:
- Cristales
- Empleados
- Proveedores
- Pedidos
- Productos
- Ventas
- Prescripciones

(Ver archivo `sistemaoptilente.sql` para estructura completa)

## Dependencias

- [PhpSpreadsheet](https://phpspreadsheet.readthedocs.io/en/latest/) para exportación a Excel.

## Solución de problemas

- Verificar que la biblioteca PhpSpreadsheet esté instalada correctamente.
- Asegurarse de que PHP tenga permisos para escribir archivos temporales.
- Comprobar que las extensiones PHP necesarias estén habilitadas.

## Autor
Gabriel (gabozzz15) - Desarrollador del sistema OPTILENTE 2020
