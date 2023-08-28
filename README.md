Handlers: Framework MVC para Desarrollo Rápido de Aplicaciones Web
==================================================================

Handlers es un framework MVC (Modelo-Vista-Controlador) desarrollado en PHP que tiene como objetivo facilitar y agilizar el proceso de creación de aplicaciones web. A través de diversas características y componentes, Handlers proporciona herramientas para el acceso a bases de datos, gestión de permisos, generación de elementos visuales y manejo de controladores.

Es un microframework de PHP ligero pero potente. Está diseñado para ser fácil de aprender y usar, pero también ofrece una amplia gama de características para crear aplicaciones web robustas.

Características Principales
---------------------------

1.  **Capa de Acceso y Manipulación de la Base de Datos:** Handlers incluye una capa que simplifica la interacción con la base de datos. Esto permite a los desarrolladores realizar operaciones como consultas, inserciones, actualizaciones y eliminaciones de datos de manera más eficiente y estructurada.

2.  **Esquema de Permisos, Usuarios y Roles:** El framework incluye un sistema de gestión de permisos y roles que puede ser activado o desactivado a través de la configuración en la base de datos. Esto proporciona un control granular sobre quién puede acceder a qué recursos dentro de la aplicación.

3.  **Mapeo Sencillo de Controladores y Métodos:** Handlers ofrece una forma simple de mapear controladores a métodos de clase. Esto permite organizar el código de manera más comprensible y facilita el mantenimiento a medida que la aplicación crece.

4.  **Generación de Elementos Visuales:** Handlers simplifica la creación de elementos visuales en HTML, CSS y JavaScript. Los bloques de visualización incluyen generadores de formularios, botones, tablas, reportes y contenedores que pueden albergar otros bloques. Esto acelera el proceso de diseño y maquetación de la interfaz de usuario.

5.  **Capa de Controladores:** Handlers ofrece una capa de controladores que pueden ser utilizados para crear tanto APIs como sistemas web. Los controladores pueden ser seguros o no seguros, dependiendo de si requieren autorización. Esto proporciona flexibilidad en la creación de diferentes tipos de aplicaciones.

6.  **Instalación con Composer:** El framework se puede instalar fácilmente a través de Composer, un administrador de dependencias de PHP. La instalación se realiza mediante el comando `composer require jossuer/handler`.


Enlace al Repositorio
---------------------

Puedes encontrar más información y detalles sobre Handlers en su [repositorio oficial en GitHub](https://github.com/JossueR/Handler). Recuerda que para obtener información más actualizada sobre el framework y sus características, es recomendable visitar el repositorio oficial y consultar la documentación proporcionada allí.

* * *

#  Estructura de carpetas recomendada para Handlers

Para usarlo, se recomienda seguir la siguiente estructura de carpetas:
```
src
├── private
│   ├── config.php
│   ├── controllers
│   │   └── HomeController.php
│   ├── models
│   │   └── UserModel.php
│   └── views
│       └── home.php
└── public
├── .htaccess
└── index.php
```
**Explicación**

*   La carpeta `src` contiene todos los archivos fuente del proyecto, incluyendo los controladores, modelos, lógica y configuraciones.
*   La carpeta `private` contiene todos los archivos sensibles, como los controladores, modelos y la configuración. Esta carpeta debe estar protegida con un archivo `.htaccess` que deniegue el acceso a todos los usuarios.
*   La carpeta `public` contiene todos los archivos públicos, como las vistas y el archivo `index.php`. Esta carpeta debe estar configurada como el documento raíz del servidor web.

**Detalles**

*   El archivo `config.php` contiene la configuración del framework, incluyendo las conexiones a la base de datos y otras opciones.
*   Los controladores son los encargados de procesar las peticiones del usuario.
*   Los modelos proporcionan acceso a los datos de la base de datos.
*   Las vistas son responsables de mostrar los datos al usuario.
*   El archivo `index.php` es el punto de entrada del framework.
