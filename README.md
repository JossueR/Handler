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

* * *

# Configuración de Handlers

El archivo `config.php` contiene la configuración del framework Handlers. Esta configuración se utiliza para ajustar el comportamiento y la apariencia del framework de acuerdo a las necesidades del proyecto.

**Clases y propiedades**

El archivo `config.php` utiliza la clase `Environment` para almacenar las variables de configuración. Esta clase tiene las siguientes propiedades:

*   `APP_DATE_FORMAT`: Formato de fecha de la aplicación.
*   `DB_DATE_FORMAT`: Formato de fecha de la base de datos.
*   `PATH_PRIVATE`: Ruta a la carpeta privada.
*   `APP_ENABLE_BD_FUNCTION`: Indica si se debe utilizar la función de secuenciales de la base de datos.
*   `APP_CONTENT_BODY`: Contenedor del contenido principal de la aplicación.
*   `APP_DEFAULT_LIMIT_PER_PAGE`: Límite predeterminado de registros por página.
*   `PATH_ROOT`: Ruta raíz de la aplicación.
*   `APP_LANG`: Idioma predeterminado de la aplicación.
*   `PATH_HANDLERS`: Ruta a los controladores.
*   `APP_CONTENT_TITLE`: Elemento para mostrar el título del contenido.
*   `APP_HIDDEN_CONTENT`: Contenido oculto.
*   `NAMESPACE_HANDLERS`: Espacio de nombres para los controladores.
*   `NAMESPACE_MODELS`: Espacio de nombres para los modelos.
*   `ACCESS_PERMISSION`: Permiso de acceso principal para el área privada.
*   `CONFIG_VAR_REPORT_TAG`: Etiqueta de reportes para mapear las variables de configuración.
*   `DB_DISPLAY_DATE_FORMAT`: Formato de fecha para visualización de la base de datos.
*   `ACCESS_HANDLER`: Controlador de acceso.
*   `START_HANDLER`: Controlador de inicio.
*   `PATH_UPLOAD`: Ruta para la carga de archivos.

**Ejemplo de configuración**

El siguiente es un ejemplo de configuración del archivo `config.php`:

PHP

    Environment::$APP_CONTENT_TITLE = "Mi aplicación";
    Environment::$APP_CONTENT_BODY = "Este es el contenido principal de mi aplicación";
    Environment::$APP_HIDDEN_CONTENT = "Este contenido no se mostrará al usuario";
    Environment::$APP_DATE_FORMAT = "d/m/Y";
    Environment::$DB_DATE_FORMAT = "Y-m-d";
    Environment::$PATH_PRIVATE = "/private";
    Environment::$PATH_ROOT = "/public";
    Environment::$PATH_UPLOAD = "/uploads";
    Environment::$APP_DEFAULT_LIMIT_PER_PAGE = 10;
    Environment::$NAMESPACE_HANDLERS = "\\App\\components\\handlers\\";
    Environment::$NAMESPACE_MODELS = "\\App\\models\\dao\\";


Esta configuración establece los siguientes valores:

*   El título del contenido principal será "Mi aplicación".
*   El contenido principal de la aplicación será "Este es el contenido principal de mi aplicación".
*   El contenido oculto será "Este contenido no se mostrará al usuario".
*   El formato de fecha de la aplicación será "d/m/Y".
*   El formato de fecha de la base de datos será "Y-m-d".
*   La carpeta privada se ubicará en `/private`.
*   La raíz de la aplicación se ubicará en `/public`.
*   La ruta para la carga de archivos se ubicará en `/uploads`.
*   El límite predeterminado de registros por página será 10.
*   El espacio de nombres para los controladores será `\\App\\components\\handlers\\`.
*   El espacio de nombres para los modelos será `\\App\\models\\dao\\`.

**Otras configuraciones**

Además de las propiedades mencionadas anteriormente, el archivo `config.php` también contiene otros valores de configuración que pueden ser personalizados. Estos valores incluyen:

*   `APP_ENABLE_SESSION`: Indica si se deben utilizar sesiones.
*   `APP_SESSION_NAME`: El nombre de la sesión.
*   `APP_SESSION_LIFETIME`: La duración de la sesión en segundos.
*   `APP_SESSION_PATH`: La ruta donde se almacenarán las sesiones.
*   `APP_SESSION_DOMAIN`: El dominio donde se almacenarán las sesiones.
*   `APP_SESSION_SECURE`: Indica si las sesiones deben ser seguras.
*   `APP_SESSION_HTTPONLY`: Indica si las sesiones deben ser solo HTTP.
*   `APP_DEBUG`: Indica si se debe habilitar el modo de depuración.
*   `APP_ENV`: El entorno de ejecución de la aplicación.

* * *

# Archivo index.php

El archivo `index.php` es el punto de entrada del framework Handlers. Este archivo se encarga de cargar la configuración, crear una instancia del `Handler` y ejecutarlo.

**Requisitos**

El archivo `index.php` requiere el siguiente archivo:

*   `vendor/autoload.php`: Este archivo es el cargador de autocarga de Composer. Se utiliza para cargar las clases del framework Handlers.

**Código**

El siguiente es el código del archivo `index.php`:

PHP

    <?php
    require __DIR__ . '/../../vendor/autoload.php';
    
    use HandlerCore\components\Handler;
    include('../config.php');
    
    if(!Handler::excec()){
    header("location:" . APP_DEFAULT_HANDLER);
    }
    #EOF



Este código hace lo siguiente:

1.  Importa el cargador de autocarga de Composer.
2.  Crea una instancia de la clase `Handler`.
3.  Incluye el archivo `config.php` para cargar la configuración.
4.  Llama al método `excec()` de la clase `Handler`. Este método se encarga de ejecutar la aplicación.
5.  Si el método `excec()` devuelve `false`, se redirige al controlador predeterminado.

**Explicación**

La línea `require __DIR__ . '/../../vendor/autoload.php';` importa el cargador de autocarga de Composer. Este archivo es necesario para cargar las clases del framework Handlers.

La línea `use HandlerCore\components\Handler;` importa la clase `Handler`. Esta clase es la encargada de ejecutar la aplicación.

La línea `include('../config.php');` incluye el archivo `config.php`. Este archivo contiene la configuración del framework Handlers.

La línea `if(!Handler::excec()){` llama al método `excec()` de la clase `Handler`. Este método se encarga de ejecutar la aplicación. Si el método `excec()` devuelve `false`, se redirige al controlador predeterminado.

La línea `header("location:" . APP_DEFAULT_HANDLER);` redirige al controlador predeterminado. El controlador predeterminado es el que se especifica en la variable `APP_DEFAULT_HANDLER` del archivo `config.php`.

**Otras consideraciones**

El archivo `index.php` puede ser personalizado para satisfacer las necesidades específicas de la aplicación. Por ejemplo, se puede agregar código para inicializar otros componentes del framework Handlers, o para realizar tareas adicionales antes de ejecutar la aplicación.

Para obtener más información sobre el archivo `index.php`, consulte la documentación de Handlers.

**Configuración de la conexión a la base de datos**

El framework Handlers utiliza la clase `SimpleDAO` para establecer la conexión o conexiones a la base de datos. La clase `SimpleDAO` proporciona métodos para acceder y manipular la base de datos.

**Configuración de la conexión**

La configuración de la conexión a la base de datos se establece en el archivo `config.php`. La siguiente es una muestra de la configuración de la conexión:


    // Estos valores no deben ser almacenados en la clase Environment por motivos de seguridad
    $APP_DB_HOST = "localhost";
    $APP_DB_DATABASE = "my_database";
    $APP_DB_USERNAME = "root";
    $APP_DB_PASSWORD = "";
    
    // Se recomienda guardar estos valores en un archivo externo y cargarlos en el archivo config.php


**Conexión por defecto**

La conexión por defecto es la que se utiliza cuando no se especifica ninguna conexión en las consultas a la base de datos. La conexión por defecto se establece en la variable `APP_DB_DEFAULT_CONNECTION` del archivo `config.php`.

**Conexiones adicionales**

Se pueden establecer conexiones adicionales a la base de datos. Para ello, se debe especificar el nombre de la conexión en la variable `APP_DB_CONNECTION_NAME` del archivo `config.php`.

**Ejemplo de conexión adicional**

El siguiente es un ejemplo de una conexión adicional a la base de datos:


    // Estos valores no deben ser almacenados en la clase Environment por motivos de seguridad
    $APP_DB_CONNECTION_NAME_2 = "db_2";
    $APP_DB_HOST_2 = "localhost";
    $APP_DB_DATABASE_2 = "my_other_database";
    $APP_DB_USERNAME_2 = "other_user";
    $APP_DB_PASSWORD_2 = "other_password";
    
    // Se recomienda guardar estos valores en un archivo externo y cargarlos en el archivo config.php




Esta configuración establece una conexión adicional llamada `db_2`. La conexión `db_2` tiene el siguiente host, nombre de base de datos, usuario y contraseña:

*   Host: `localhost`
*   Nombre de base de datos: `my_other_database`
*   Usuario: `other_user`
*   Contraseña: `other_password`

**Uso de las conexiones**

Las conexiones a la base de datos se pueden utilizar de forma estática o instanciada. Para utilizar una conexión de forma estática, se puede utilizar el método `connect()` de la clase `SimpleDAO`. El método `connect()` devuelve una instancia de la clase `Connection`.

El siguiente es un ejemplo de cómo utilizar una conexión de forma estática:



    // Se recomienda cargar los valores de conexion desde un archivo externo
    $connection = SimpleDAO::connect($APP_DB_HOST, $APP_DB_DATABASE, $APP_DB_USERNAME, $APP_DB_PASSWORD);



Este código establece una conexión a la base de datos utilizando los valores de configuración establecidos en el archivo `config.php`. La conexión se almacena en la variable `connection`.

Las conexiones también se pueden utilizar de forma instanciada. Para ello, se debe crear una instancia de la clase `Connection`. El siguiente es un ejemplo de cómo crear una instancia de la clase `Connection`:



    // Se recomienda cargar los valores de conexion desde un archivo externo
    $connection = new Connection($APP_DB_HOST, $APP_DB_DATABASE, $APP_DB_USERNAME, $APP_DB_PASSWORD);



Este código establece una conexión a la base de datos utilizando los valores de configuración establecidos en el archivo `config.php`. La conexión se almacena en la variable `connection`.

