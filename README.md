Handlers: Framework MVC para Desarrollo Rápido de Aplicaciones Web
==================================================================

Handlers es un framework MVC (Modelo-Vista-Controlador) desarrollado en PHP que tiene como objetivo facilitar y agilizar el proceso de creación de aplicaciones web. A través de diversas características y componentes, Handlers proporciona herramientas para el acceso a bases de datos, gestión de permisos, generación de elementos visuales y manejo de controladores.

Es un microframework de PHP ligero pero potente. Está diseñado para ser fácil de aprender y usar, pero también ofrece una amplia gama de características para crear aplicaciones web robustas.

Características Principales:
---------------------------

1.  **Capa de Acceso y Manipulación de la Base de Datos:** Handlers incluye una capa que simplifica la interacción con la base de datos. Esto permite a los desarrolladores realizar operaciones como consultas, inserciones, actualizaciones y eliminaciones de datos de manera más eficiente y estructurada.

2.  **Esquema de Permisos, Usuarios y Roles:** El framework incluye un sistema de gestión de permisos y roles que puede ser activado o desactivado a través de la configuración en la base de datos. Esto proporciona un control granular sobre quién puede acceder a qué recursos dentro de la aplicación.

3.  **Mapeo Sencillo de Controladores y Métodos:** Handlers ofrece una forma simple de mapear controladores a métodos de clase. Esto permite organizar el código de manera más comprensible y facilita el mantenimiento a medida que la aplicación crece.

4.  **Generación de Elementos Visuales:** Handlers simplifica la creación de elementos visuales en HTML, CSS y JavaScript. Los bloques de visualización incluyen generadores de formularios, botones, tablas, reportes y contenedores que pueden albergar otros bloques. Esto acelera el proceso de diseño y maquetación de la interfaz de usuario.

5.  **Capa de Controladores:** Handlers ofrece una capa de controladores que pueden ser utilizados para crear tanto APIs como sistemas web. Los controladores pueden ser seguros o no seguros, dependiendo de si requieren autorización. Esto proporciona flexibilidad en la creación de diferentes tipos de aplicaciones.

6.  **Instalación con Composer:** El framework se puede instalar fácilmente a través de Composer, un administrador de dependencias de PHP. La instalación se realiza mediante el comando `composer require jossuer/handler`.




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

* * *

# Controladores y vistas en Handlers

Los controladores y vistas son los elementos fundamentales para la construcción de aplicaciones web en Handlers. Los controladores son responsables de procesar las solicitudes del usuario y generar la respuesta, mientras que las vistas son responsables de mostrar la respuesta al usuario.

Los controladores y las APIs son componentes fundamentales para la construcción de aplicaciones web en Handlers.

**Controladores**

Los controladores deben extender de la clase `Handler` y tener el sufijo `Handler`. Por ejemplo, un controlador llamado `MiControlador` debería llamarse `MiControladorHandler`.

Los controladores tienen acciones, que son métodos con el sufijo `Action`. Las acciones son las que se encargan de procesar las solicitudes del usuario.

Por defecto, el controlador mostrará la acción `indexAction()` si existe. Si no existe, no mostrará nada.

El siguiente es un ejemplo de un controlador simple:

PHP

    class MiControladorHandler extends Handler
    {
        public function indexAction()
        {
            echo "Este es el controlador MiControlador";
        }
    }



Este controlador tiene una sola acción llamada `indexAction()`. Cuando se accede al controlador a través de la URL `miurlejemplo.com/MiControlador`, se mostrará el mensaje "Este es el controlador MiControlador".

**Vistas**

Las vistas son simples archivos PHP que se cargan usando el método `display()` del controlador.

El método `display()` toma tres parámetros:

*   **$script:** La ruta al script de la vista.
*   **$args:** Un array con los argumentos que se pasarán a la vista.
*   **$autoshoy:** Un booleano que indica si la vista se debe mostrar automáticamente o devolver como cadena.

El siguiente es un ejemplo de una vista simple:



    <!DOCTYPE html>
    <html lang="es">
    <head>
        <title>Mi vista</title>
    </head>
    <body>
        <h1>Este es el título de la vista</h1>
    </body>
    </html>



Esta vista se puede cargar en un controlador usando el siguiente código:



    class MiControladorHandler extends Handler
    {
        public function indexAction()
        {
            $this->display("views/mi_vista.php");
        }
    }



Este código cargará la vista `views/mi_vista.php` en el controlador `MiControlador`.

**Ejemplo de funcionamiento**

Para ilustrar el funcionamiento de los controladores y vistas, consideremos el siguiente ejemplo:



    class MiControladorHandler extends Handler
    {
        public function indexAction()
        {
            $this->display("views/mi_vista.php", array(
                "titulo" => "Este es el título de la vista"
            ));
        }
    
        public function usuariosAction()
        {
            echo "Esta es la acción usuarios";
        }
    }



Este controlador tiene dos acciones: `indexAction()` y `usuariosAction()`.

La acción `indexAction()` muestra una vista llamada `views/mi_vista.php` con el título "Este es el título de la vista".

La acción `usuariosAction()` muestra el mensaje "Esta es la acción usuarios".

Para acceder a la acción `indexAction()`, se puede usar la siguiente URL:

miurlejemplo.com/MiControlador

Para acceder a la acción `usuariosAction()`, se puede usar la siguiente URL:

miurlejemplo.com/MiControlador?do=usuarios

En este último caso, el parámetro `do` se utiliza para especificar la acción que se desea ejecutar.

**Comportamiento de los controladores**

Los controladores se implementan como clases que extienden de la clase `Handler`. Los controladores tienen acciones, que son métodos con el sufijo `Action`. Las acciones son las que se encargan de procesar las solicitudes del usuario.

Por defecto, todos los controladores son seguros, lo que significa que requieren autenticación previa con un token de acceso. Los únicos controladores que no requieren de acceso son los que están marcados con la interfaz `UnsecureHandler` y los controladores de APIs.

**Controladores de APIs**

Los controladores de APIs extienden de la clase `ResponseHandler`. La clase `ResponseHandler` proporciona funcionalidad adicional para la gestión de respuestas y errores.

Los controladores de APIs seguros extienden de la clase `SecureResponseHandler`. La clase `SecureResponseHandler` proporciona funcionalidad adicional para la autenticación y seguridad.

**Cómo construir APIs**

Para construir una API en Handlers, se debe crear un controlador que extienda de la clase `ResponseHandler` o `SecureResponseHandler`.

El controlador debe tener acciones que implementen las operaciones que se desean exponer a través de la API.

Las acciones deben devolver un objeto JSON con el siguiente formato:

JSON

    {
        "status": "success",
        "status_code": 200,
        "data": [...]
    }




El campo `status` debe indicar el estado de la respuesta. Los valores posibles son `success` y `error`.

El campo `status_code` debe indicar el código de estado HTTP de la respuesta.

El campo `data` debe contener los datos de la respuesta.

**Ejemplo de API**

El siguiente es un ejemplo de un controlador de API que proporciona información sobre el estado de un pedido:

PHP

    class OrderHandler extends ResponseHandler {
    
        public function getOrderStatusAction()
        {
            $tracking = $this->getRequestAttr("tracking");
    
            $this->setVar("tk", $tracking);
    
            $publish_data = $this->getPublishData($tracking);
    
            if ($publish_data) {
                $this->setVar("order", $publish_data);
            } else {
                $this->addError("No se encontró la guía");
            }
    
            $this->toJSON();
        }
    }



Este controlador tiene una sola acción llamada `getOrderStatusAction()`. Esta acción recibe el número de seguimiento del pedido como parámetro.

La acción utiliza la función `getPublishData()` para obtener los datos de publicación del pedido. Si los datos de publicación están disponibles, la acción establece la variable `order` con los datos del pedido.

Si los datos de publicación no están disponibles, la acción agrega un error a la respuesta.

La respuesta de la acción tiene el siguiente formato:

JSON

    {
        "status": "success",
        "status_code": 200,
        "data": {
            "id": 1234,
            "status": "en tránsito",
            ...
        }
    }




En este caso, el campo `data` contiene los datos del pedido, que incluyen el ID del pedido, el estado del pedido y otros datos.

* * *

# AbstractBaseDAO y AutoImplementedDAO: Acceso a datos en Handlers

Las clases AbstractBaseDAO y AutoImplementedDAO proporcionan una base para la creación de objetos de acceso a datos (DAO) que interactúan con tablas de bases de datos.

**AbstractBaseDAO**

La clase AbstractBaseDAO proporciona una implementación básica de los métodos y propiedades necesarios para realizar operaciones de acceso a datos. Estos métodos incluyen:

*   `insert()`: Inserta un nuevo registro en la tabla de la base de datos.
*   `update()`: Actualiza registros en la tabla de la base de datos.
*   `delete()`: Elimina registros de la tabla de la base de datos.
*   `exist()`: Verifica si existe un registro en la tabla de la base de datos.
*   `get()`: Obtiene el siguiente registro del resultado de la última consulta realizada.
*   `fetchAll()`: Obtiene todos los registros del resultado de la última consulta realizada.
*   `getBy()`: Realiza una consulta para obtener registros en base a un arreglo de condiciones.
*   `getById()`: Realiza una consulta para obtener un registro por su identificador.
*   `getFilledPrototype()`: Obtiene un prototipo lleno con datos recuperados de la última fila consultada.

**AutoImplementedDAO**

La clase AutoImplementedDAO extiende la clase AbstractBaseDAO y proporciona funcionalidades automáticas para interactuar con una tabla de base de datos. Estas funcionalidades incluyen:

*   Carga automática de la configuración de campos de la tabla de la base de datos.
*   Generación automática de consultas SQL para operaciones de acceso a datos.

**Cómo crear un DAO**

Para crear un DAO, es necesario extender la clase AbstractBaseDAO o AutoImplementedDAO.

Si se extiende la clase AbstractBaseDAO, es necesario implementar los métodos `getPrototype()` y `getDBMap()`. Estos métodos proporcionan información sobre los campos de la tabla de la base de datos que se está utilizando.

Si se extiende la clase AutoImplementedDAO, no es necesario implementar ningún método adicional. La clase AutoImplementedDAO cargará automáticamente la configuración de campos de la tabla de la base de datos y generará consultas SQL automáticamente.

**Ejemplo**

El siguiente ejemplo muestra cómo crear un DAO para la tabla `package_type`:

PHP

    class PackageTypeDAO extends AutoImplementedDAO {
    
    	function __construct() {
    		parent::__construct("package_type", array("id"));
    	}
    
    }



Este DAO utilizará los campos `id`, `name` y `calc_type` de la tabla `package_type`.

**Notas**

*   La clase AbstractBaseDAO proporciona una implementación básica de los métodos y propiedades necesarios para realizar operaciones de acceso a datos. Sin embargo, es posible que sea necesario implementar métodos adicionales para personalizar el comportamiento del DAO.
*   La clase AutoImplementedDAO proporciona funcionalidades automáticas para interactuar con una tabla de base de datos. Sin embargo, es posible que sea necesario implementar métodos adicionales para personalizar el comportamiento del DAO.

**Generación de filtros de consultas a través de arreglos de configuración**

En Handlers, se puede generar filtros de consultas a través de arreglos de configuración. Estos arreglos contienen los campos y valores a filtrar.

Para generar un filtro de consulta, se utiliza el método `getSQLFilter()` de la clase `SimpleDAO`. Este método recibe un arreglo de configuración y un operador de unión para combinar múltiples condiciones.

El siguiente ejemplo ilustra cómo generar un filtro de consulta para obtener todas las órdenes de trabajo activas:

PHP

    class WorkOrderDAO extends AbstractDAO {
    
        function __construct() {
            parent::__construct("work_order", array("id"));
        }
    
        function getActives(){
            $searchArray = array(
                "t1.active" => self::REG_ACTIVO_TX
            );
    
            $where = SimpleDAO::getSQLFilter($searchArray);
    
            $sql = $this->getBaseQuery() . $where;
    
            $this->find($sql);
    
        }
    
    }




En este ejemplo, el DAO extiende de la clase `AbstractDAO`, que a su vez extiende de la clase `SimpleDAO`. Esto significa que el DAO tiene acceso al método `getSQLFilter()` de la clase `SimpleDAO`.

El método `getActives()` del DAO llama al método `getSQLFilter()` para generar un filtro de consulta para obtener todas las órdenes de trabajo activas. El arreglo de configuración `searchArray` contiene un solo elemento: `t1.active`, que especifica que la orden de trabajo debe estar activa.

El método `getSQLFilter()` de la clase `SimpleDAO` devuelve una cadena de filtro SQL. En este caso, la cadena de filtro es:

SQL

    WHERE t1.active = '1'




El operador de unión predeterminado es `AND`. Se puede cambiar este operador pasando un valor diferente al segundo parámetro del método `getSQLFilter()`.

El siguiente ejemplo ilustra cómo generar un filtro de consulta para obtener todas las órdenes de trabajo activas para un conductor específico:

PHP

    class WorkOrderDAO extends AbstractDAO {
    
        function __construct() {
            parent::__construct("work_order", array("id"));
        }
    
        function getByStatus($status, $username=null)
        {
            $searchArray = array(
                "t1.active" => self::REG_ACTIVO_TX,
                "t1.work_order_status_id" => $status
            );
    
            if($username){
                $searchArray["t1.driver"] = $username;
            }
    
            $where = SimpleDAO::getSQLFilter($searchArray);
    
            $sql = $this->getBaseQuery() . $where;
    
            $this->find($sql);
        }
    
    }




En este ejemplo, el método `getByStatus()` del DAO llama al método `getSQLFilter()` para generar un filtro de consulta para obtener todas las órdenes de trabajo activas para un conductor específico. El arreglo de configuración `searchArray` contiene dos elementos: `t1.active` y `t1.work_order_status_id`. El primer elemento especifica que la orden de trabajo debe estar activa, y el segundo elemento especifica que la orden de trabajo debe estar asignada al estado de orden de trabajo especificado.

Si el usuario proporciona un nombre de usuario, el método `getByStatus()` agrega un tercer elemento al arreglo de configuración `searchArray`: `t1.driver`. Este elemento especifica que la orden de trabajo debe estar asignada al conductor especificado.

La cadena de filtro SQL generada es:

SQL

    WHERE t1.active = '1' AND t1.work_order_status_id = 1




Si se proporciona el nombre de usuario, la cadena de filtro SQL se convierte en:

SQL

    WHERE t1.active = '1' AND t1.work_order_status_id = 1 AND t1.driver = 'username'


**Generadores**

El framework Handlers viene con algunos generadores útiles que agilizan la creación de aplicaciones web. Los principales son:

*   **Formkaker** para crear formularios
*   **ButtonMaker** para generar grupos de botones
*   **DashViewer** para generar HTML formateado que muestra información en un bloque
*   **DataViewer** para generar un bloque HTML que muestra información en forma de tabla
*   **TableGenerator** para generar tablas HTML filtrables, ordenables y paginables basadas en el último query ejecutado en un objeto AbstractDAO.
*   **WrapperViewer** para generar contenedores que pueden tener otros objetos mostrables.

**Cómo usar el generador FormMaker y FormMakerFieldConf**

El generador FormMaker y la clase FormMakerFieldConf se utilizan para generar formularios HTML dinámicos y personalizables.

**Para usar FormMaker, primero debe crear una instancia de la clase FormMaker:**

PHP

    $form = new FormMaker();




Una vez creada la instancia, puede comenzar a definir los campos del formulario. Para ello, puede utilizar el método `defineField()`. El método `defineField()` recibe un arreglo de configuración que define las propiedades del campo.

**Las propiedades del campo son las siguientes:**

*   **campo:** El nombre único del campo.
*   **label:** La etiqueta del campo.
    **tipo:** El tipo de campo. Los tipos de campos disponibles son:
    *   `FormMakerFieldConf::FIELD_TYPE_TEXT`: Campo de texto.
    *   `FormMakerFieldConf::FIELD_TYPE_HIDDEN`: Campo oculto.
    *   `FormMakerFieldConf::FIELD_TYPE_LABEL`: Etiqueta de texto.
    *   `FormMakerFieldConf::FIELD_TYPE_PASSWORD`: Campo de contraseña.
    *   `FormMakerFieldConf::FIELD_TYPE_TEXTAREA`: Área de texto.
    *   `FormMakerFieldConf::FIELD_TYPE_RADIO`: Botones de opción.
    *   `FormMakerFieldConf::FIELD_TYPE_CHECK`: Casilla de verificación.
    *   `FormMakerFieldConf::FIELD_TYPE_SELECT`: Menú desplegable.
    *   `FormMakerFieldConf::FIELD_TYPE_SELECT_I18N`: Menú desplegable internacionalizado.
    *   `FormMakerFieldConf::FIELD_TYPE_SELECT_ARRAY`: Menú desplegable desde array.
    *   `FormMakerFieldConf::FIELD_TYPE_DIV`: Contenedor div.
    *   `FormMakerFieldConf::FIELD_TYPE_SEARCH_SELECT`: Menú desplegable con búsqueda.
    *   `FormMakerFieldConf::FIELD_TYPE_MULTIPLE_SELECT`: Menú desplegable de selección múltiple.
    *   `FormMakerFieldConf::FIELD_TYPE_DATE`: Campo de selección de fecha.
    *   `FormMakerFieldConf::FIELD_TYPE_DATETIME`: Campo de selección de fecha y hora.
    *   `FormMakerFieldConf::FIELD_TYPE_EMAIL`: Campo de entrada de correo electrónico.
    *   `FormMakerFieldConf::FIELD_TYPE_FILE`: Campo de carga de archivos.
    *   `FormMakerFieldConf::FIELD_TYPE_TIME`: Campo de selección de hora.
    *   `FormMakerFieldConf::FIELD_TYPE_CHECK_ARRAY`: Conjunto de casillas de verificación.
    *   `FormMakerFieldConf::FIELD_TYPE_TEXT_SEARCH`: Campo de texto con búsqueda.
*   **source:** La fuente de datos para campos de selección. Puede ser un objeto AbstractBaseDAO o un arreglo.
*   **action:** La acción que se llamará al ejecutar una búsqueda en un campo de selección.
*   **params:** Los parámetros de la acción de búsqueda.
*   **showAction:** La acción que se llamará al mostrar un resultado en un campo de selección.
*   **showParams:** Los parámetros de la acción de visualización.
*   **html:** Un arreglo de atributos HTML para el campo.
*   **wraper:** El nombre del ID del tag HTML que envolverá al campo generado.
*   **required:** Indica si el campo es requerido.

**FormMaker genera por defecto formularios que se enviarán por ajax con el método post.** Además, deben tener los siguientes atributos configurados para su correcto funcionamiento:

*   **$form->name:** Nombre único del formulario. Será el ID del tag HTML form.
*   **$form->action:** Es la acción que será llamada al momento de enviar el formulario.
*   **$form->actionDO:** Es el método al que se le enviarán los datos.

**FormMaker también generará un formulario que tenga todos los campos definidos en el atributo prototype. Este es un arreglo asociativo de claves que representan el ID de los campos y su valor por defecto.** FormMaker generará un input text para cada clave del arreglo de configuración prototype, a menos que se defina otra configuración usande el método defineField.

**Por ejemplo, el siguiente código define un formulario con dos campos, uno de texto y otro de selección:**

PHP

    $form = new FormMaker();
    
    $form->prototype = [
        "username" => "",
        "customer_id" => ""
    ];
    
    $form->defineField(array(
        "campo"=>'username',
        "tipo" =>FormMaker::FIELD_TYPE_TEXT
    ));
    
    $form->defineField(array(
        "campo"=>'customer_id',
        "tipo" =>FormMaker::FIELD_TYPE_SELECT,
        "source"=>[]
    ));
    
    $form->show();




**Este código genera el siguiente formulario:**

HTML

    <form action="" method="post" id="form-example">
        <label for="username">Nombre de usuario</label>
        <input type="text" id="username" name="username" value="">
        <label for="customer_id">Cliente</label>
        <input type="text" id="customer_id" name="customer_id" value="">
        <input type="submit" value="Enviar">
    </form>






**El atributo `action`  indica a que Handler controlador se le enviara los datos**

PHP

    $form->action = "Customer";

**El atributo `actionDO`  indica a que método exacto del Handler anterior, se le enviaran los datos recolectados**

PHP

    $form->actionDO = "storeUser";


**Cómo usar el Generador ButtonMaker**

La clase ButtonMaker se utiliza para generar grupos de botones de acuerdo a un esquema específico. Puede configurarse con botones individuales o múltiples botones.

**Para usar ButtonMaker, primero debe crear una instancia de la clase:**

PHP

    $btn = new ButtonMaker("excel");


**El primer parámetro es el nombre del grupo de botones.** El segundo parámetro es opcional y puede utilizarse para establecer la referencia del objeto que invocó a ButtonMaker.

**Una vez creada la instancia, puede comenzar a agregar botones al grupo.** Para ello, puede utilizar el método `addButton()`. El método `addButton()` recibe dos parámetros:

*   **El nombre del botón.**
*   **La configuración del botón.**

La configuración del botón es un arreglo que contiene los siguientes campos:

*   **icon:** El icono que se mostrará en el botón.
*   **label:** La etiqueta del botón.
*   **link:** El enlace del botón.
*   **type:** El tipo de botón.

**Por ejemplo, el siguiente código agrega un botón al grupo:**

PHP

    $btn->addButton("k_excel", array(
        "icon" => "fa-plus-circle",
        "label" => "Exportar a Excel",
        "link" => "window.open('$url' + $('#year').val(), '_blank');",
        "type" => "btn-xs btn-success"
    ));



**Para mostrar el grupo de botones, puede utilizar el método `show()`.** El método `show()` muestra el grupo de botones utilizando el esquema configurado.

**El siguiente código muestra el grupo de botones:**

PHP

    $btn->show();



**Además de los métodos `addButton()` y `show()`, la clase ButtonMaker también ofrece los siguientes métodos:**

*   \*\*addManyButtons()\`: Agrega múltiples botones al grupo de botones con la configuración proporcionada.
*   \*\*showInGroup()\`: Indica que el grupo de botones se mostrará dentro de un grupo mayor.
*   \*\*addPostScript()\`: Agrega un script de JavaScript para ejecutarse después de mostrar los botones.
*   \*\*putPostScripts()\`: Imprime los scripts de JavaScript agregados posteriormente.
*   \*\*setParamsData()\`: Establece los datos de parámetros para el grupo de botones.
*   \*\*setName()\`: Establece el nombre del grupo de botones.
*   \*\*setShowLabel()\`: Establece si se mostrarán las etiquetas en los botones.

**Ejemplo de uso**

El siguiente código muestra un ejemplo de uso de ButtonMaker:

PHP

    $btn = new ButtonMaker("excel");
    
    $btn->addButton("k_excel", array(
        "icon" => "fa-plus-circle",
        "label" => "Exportar a Excel",
        "link" => "window.open('$url' + $('#year').val(), '_blank');",
        "type" => "btn-xs btn-success"
    ));
    
    $btn->addButton("k_pdf", array(
        "icon" => "fa-file-pdf",
        "label" => "Exportar a PDF",
        "link" => "window.open('$url' + $('#year').val() + '.pdf', '_blank');",
        "type" => "btn-xs btn-info"
    ));
    
    $btn->show();



Este código genera el siguiente grupo de botones:

HTML

    <div class="btn-group">
        <button type="button" class="btn btn-xs btn-success">
            <i class="fa fa-plus-circle"></i> Exportar a Excel
        </button>
        <button type="button" class="btn btn-xs btn-info">
            <i class="fa fa-file-pdf"></i> Exportar a PDF
        </button>
    </div>


**Cómo usar el generador DashViewer**

La clase DashViewer se utiliza para mostrar información en un bloque con opciones de configuración y personalización, incluyendo la posibilidad de mostrar contenido específico y ejecutar scripts después de mostrar el contenido.

**Para usar DashViewer, primero debe crear una instancia de la clase:**

PHP

    $viewer = new DashViewer();




**El primer parámetro es opcional y puede utilizarse para establecer la referencia del objeto que invocó a DashViewer.**

**Una vez creada la instancia, puede comenzar a configurar el bloque.** Para ello, puede utilizar los siguientes métodos:

*   **setTitle($title)**: Establece el título del bloque DashViewer.
*   **setGeneralSchema(string $generalSchema)**: Establece el esquema general para todos los bloques DashViewer.
*   **loadName():** Carga el nombre del bloque si aún no se ha establecido.
*   **addPostScript($script, $have\_script\_tag=false)**: Agrega un script js para ejecutar después del contenido principal del bloque.
*   **OnlyShowContent():** Habilita la opción para mostrar únicamente el contenido específico del bloque.
*   **setVar($name, $value)**: Establece una variable para el bloque DashViewer.

**Para mostrar el contenido del bloque, puede utilizar el método `show()`.** El método `show()` muestra el contenido del bloque DashViewer según las condiciones de seguridad y configuración.

**El siguiente código muestra un ejemplo de uso de DashViewer:**

PHP

    $viewer = new DashViewer();
    $viewer->setTitle("Mi bloque");
    $viewer->addPostScript('$(document).ready(function() { console.log("Hola!"); });');
    $viewer->setVar("f", $this->getView("my-view"));
    $viewer->show();




Este código genera el siguiente bloque DashViewer:

HTML

    <div class="card">
        <div class="card-header">
            Mi bloque
        </div>
        <div class="card-body">
            <p>Este es el contenido de la vista.</p>
        </div>
        <div class="card-footer">
            <script>
                $(document).ready(function() {
                    console.log("Hola!");
                });
            </script>
        </div>
    </div>




**El contenido del bloque puede ser cualquier objeto que implemente la interfaz ShowableInterface.** Por ejemplo, el siguiente código muestra un ejemplo de uso de DashViewer con una vista:

PHP

    $viewer = new DashViewer();
    $viewer->setTitle("Mi bloque");
    $viewer->addPostScript('$(document).ready(function() { console.log("Hola!"); });');
    $viewer->setVar("f", $this->getView("my-view"));
    $viewer->show();




Este código genera el mismo bloque DashViewer que el anterior.

**Para mostrar solo el contenido específico del bloque, puede utilizar el método `OnlyShowContent()`.** El método `OnlyShowContent()` habilita la opción para mostrar únicamente el contenido específico del bloque.

**El siguiente código muestra un ejemplo de uso de `OnlyShowContent()`:**

PHP

    $viewer = new DashViewer();
    $viewer->setTitle("Mi bloque");
    $viewer->addPostScript('$(document).ready(function() { console.log("Hola!"); });');
    $viewer->setVar("f", $this->getView("my-view"));
    $viewer->OnlyShowContent();
    $viewer->show();




Este código genera el siguiente bloque DashViewer:

HTML

    <div class="card-body">
            <p>Este es el contenido de la vista.</p>
        </div>


**Cómo usar el generador DataViewer**

La clase DataViewer se utiliza para generar un bloque HTML que muestra información en forma de tabla, ya sea a partir de un objeto AbstractBaseDAO o un array asociativo.

**Para usar DataViewer, primero debe crear una instancia de la clase:**

PHP

    $viewer = new DataViewer();




**El primer parámetro es opcional y puede utilizarse para establecer el objeto AbstractBaseDAO que proporcionará los datos para la tabla.**

**Una vez creada la instancia, puede comenzar a configurar el bloque.** Para ello, puede utilizar los siguientes métodos:

*   **setTitle($title)**: Establece el título del bloque DataViewer.
*   **setGeneralSchema(string $generalSchema)**: Establece el esquema general para todos los bloques DataViewer.
*   **setArrayData($row)**: Establece los datos del bloque DataViewer utilizando un array.
*   **setButtons(ButtonMaker $buttons)**: Asigna un grupo de botones al DataViewer.
*   **OnlyShowContent():** Habilita la opción para mostrar solo el contenido específico del bloque.

**Para mostrar la tabla, puede utilizar el método `show()`.** El método `show()` muestra la tabla generada por DataViewer, presentando las claves y los valores de los datos proporcionados en filas.

**El siguiente código muestra un ejemplo de uso de DataViewer:**

PHP

    $viewer = new DataViewer();
    $viewer->setTitle("Mi tabla");
    $viewer->setButtons(new ButtonMaker("excel"));
    $viewer->setArrayData([
        "nombre" => "Juan Pérez",
        "apellido" => "García",
        "edad" => 30
    ]);
    $viewer->show();




Este código genera el siguiente bloque DataViewer:

HTML

    <table class="table table-bordered">
        <tbody>
            <tr>
                <td>nombre</td>
                <td>Juan Pérez</td>
            </tr>
            <tr>
                <td>apellido</td>
                <td>García</td>
            </tr>
            <tr>
                <td>edad</td>
                <td>30</td>
            </tr>
        </tbody>
    </table>

**Cómo usar el generador WrapperViewer**

La clase WrapperViewer genera un envoltorio visual para bloques que implementen la interfaz ShowableInterface o sean vistas.

**Para usar WrapperViewer, primero debe crear una instancia de la clase:**

PHP

    $viewer = new WrapperViewer();




**El primer parámetro es opcional y puede utilizarse para establecer la ruta de la vista a utilizar como esquema del envoltorio.**

**Una vez creada la instancia, puede comenzar a configurar el envoltorio.** Para ello, puede utilizar los siguientes métodos:\*\*

*   **setTitle($title)**: Establece el título del envoltorio.
*   **setGeneralSchema(string $generalSchema)**: Establece la ruta del esquema de envoltorio general.
*   **add(string|ShowableInterface $action, $type=null)**: Agrega contenido al envoltorio.
*   \*\*show(): Muestra el envoltorio con su contenido.

**Para agregar contenido al envoltorio, puede utilizar el método `add()`.** El método `add()` acepta dos parámetros:

*   **El primer parámetro es el contenido a agregar.** Puede ser una cadena de texto, un objeto que implementa la interfaz ShowableInterface o una ruta de vista.
*   **El segundo parámetro es el tipo de contenido.** Es opcional y puede utilizarse para especificar el tipo de contenido que se está agregando. Los valores posibles son `TYPE_RAW`, `TYPE_OBJ` y `TYPE_PATH`.

**El siguiente código muestra un ejemplo de uso de WrapperViewer:**

PHP

    $viewer = new WrapperViewer();
    $viewer->setTitle("Mi envoltorio");
    
    // Agrega contenido de texto
    $viewer->add("Este es un contenido de texto.");
    
    // Agrega contenido de una vista
    $viewer->add("index.php", WrapperViewer::TYPE_PATH);
    
    // Agrega contenido de un objeto
    $viewer->add(new MyObject(), WrapperViewer::TYPE_OBJ);
    
    $viewer->show();




Este código genera el siguiente envoltorio visual:

HTML

    <div class="wrapper-viewer">
        <h3>Mi envoltorio</h3>
        <p>Este es un contenido de texto.</p>
        <div class="wrapper-content">
            <p>Contenido de la vista index.php</p>
        </div>
        <div class="wrapper-content">
            <p>Contenido del objeto MyObject</p>
        </div>
    </div>




**WrapperViewer también ofrece una serie de métodos para personalizar la visualización del envoltorio.** Por ejemplo, el siguiente código muestra un ejemplo de uso de los métodos `setClass()` y `setGeneralSchema()`:

PHP

    $viewer = new WrapperViewer();
    $viewer->setTitle("Mi envoltorio");
    $viewer->setClass("my-custom-class");
    $viewer->setGeneralSchema("/views/wrappers/default.php");
    
    // Agrega contenido de texto
    $viewer->add("Este es un contenido de texto.");
    
    // Agrega contenido de una vista
    $viewer->add("index.php", WrapperViewer::TYPE_PATH);
    
    // Agrega contenido de un objeto
    $viewer->add(new MyObject(), WrapperViewer::TYPE_OBJ);
    
    $viewer->show();




Este código genera el siguiente envoltorio visual:

HTML

    <div class="wrapper-viewer my-custom-class">
        <h3>Mi envoltorio</h3>
        <p>Este es un contenido de texto.</p>
        <div class="wrapper-content">
            <p>Contenido de la vista index.php</p>
        </div>
        <div class="wrapper-content">
            <p>Contenido del objeto MyObject</p>
        </div>
    </div>



**Cómo usar el generador TableGenerator**

La clase TableGenerator es un generador de tablas HTML que permite mostrar datos de un objeto AbstractDAO de forma filtrable, ordenable y paginable.

**Para usar TableGenerator, primero debe crear una instancia de la clase:**

PHP

    $tabla = new TableGenerator($dao, __METHOD__);




**El primer parámetro es el objeto DAO que proporciona los datos para la tabla.**

**El segundo parámetro es el invocador que originó la tabla.**

**Una vez creada la instancia, puede comenzar a configurar la tabla.** Para ello, puede utilizar los siguientes métodos:\*\*

*   **reloadScript(string $name)**: Establece el nombre del script que se llamará cuando se requiera actualizar la tabla.
*   **reloadDo(string $name)**: Establece el método que se llamará al momento de actualizar la tabla.
*   **setName(string $name)**: Establece el nombre de la instancia de tabla creada.
*   **html(array $data)**: Establece los atributos HTML de la tabla.
*   **fields(string $fields)**: Establece los campos que se mostrarán en la tabla.
*   **actions(array $actions)**: Asigna las acciones que se mostrarán en la tabla.
*   **rowClausure(callable $closure)**: Establece una función que se ejecutará para cada fila generada en la tabla.
*   **colClausure(callable $closure)**: Establece una función que se ejecutará para cada columna generada en la tabla.
*   **totalsClausure(callable $closure)**: Establece una función que se ejecutará para acumular los totales de las columnas.

**El siguiente código muestra un ejemplo de uso de TableGenerator:**

PHP

    $dao = new InvoiceDAO();
    $dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE; $dao->disableExecFind();
    
    $dao->getActives();
    
    TableGenerator::defaultOrder('id', false);
    
    
    $tabla = new TableGenerator($dao, __METHOD__);
    $tabla->reloadScript = $this->getHandlerName(); //El nombre del controlador que se llamara cuando se requiere actualizar la tabla
    $tabla->reloadDo = 'list'; //metodo que se llama al momento de actualizar la tabla
    $tabla->setName($this->getRequestAttr('objName')); //nombre de la instancia de tabla creada, si es una actualizacion, se utiliza el ultimo nombre generado
    $tabla->html = array(
    	'class' => 'table table-striped'
    );
    
    $tabla->fields="id,fiscal_id,customer_name,tax,total,payment_total,balance";
    
    //crea las acciones
    $actions = new TableAcctions();
    
    $actions->addAction("", Handler::asyncLoad(Handler::$handler, APP_CONTENT_BODY, array(
    					'id'=>'#id#',
    					'do'=>'inactivate'
    				),true, true, showMessage("confirm_inactivate", array("field" => "#id#"))),
    				array('class'=>'fa fa-trash-alt  fa-lg fa-fw'));
    
    //asocia las acciones
    $tabla->actions=$actions->getAllActions();
    
    $tabla->rowClausure = function($row){
    	$result = array();
    
    
    	switch ($row["status"]) {
    
    
    		case InvoiceDAO::STATUS_CANCELED:
    			$result["style"] = "background: #f79e87";
    		break;
    	}
    
    
    	return $result;
    };
    
    $tabla->colClausure = function($row, $field, $isTotal){
    
    	switch ($field) {
    		case 'id':
    			$text = "<i class=\"fa fa-chevron-circle-right fa-fw\" aria-hidden=\"true\"></i> {$row[$field]}";
    
    			$data = $this->make_link($text,
    				Handler::asyncLoad("ShippingOrder", APP_CONTENT_BODY, array(
    					"do"=>"dash",
    					"id"=>$row[$field]
    				),true),false);
    		break;
    
    		default:
    			$data = $row[$field];
    	}
    
    	return array("data"



* * *

# Capa de seguridad

La capa de seguridad de **Handler** es una capa intermedia entre la capa de datos y los controladores. Se encarga de verificar que los usuarios tengan los permisos necesarios para acceder a los datos y acciones.

**Permisos**

Los permisos en **Handler** son simples id y descripciones que indican una acción. Por ejemplo, el permiso "ver\_facturas" permite a los usuarios ver las facturas.

**Usuarios, grupos y permisos**

En **Handler** existen usuarios, grupos y permisos. Un usuario puede tener muchos permisos, un grupo puede también contener muchos permisos y un usuario puede estar en muchos grupos.

**Accesos de seguridad**

Los accesos de seguridad en **Handler** son dinámicos y configurables. Cada objeto **Showable** que indique quien fue el que lo invocó, genera una regla de acceso que es autoaprendida por el sistema a medida que se va utilizando. Cada regla de acceso puede contener un permiso.

**Verificación de acceso**

Antes de mostrar un objeto **Showable**, se verifica las reglas de acceso del objeto desde el invocador, y si tiene alguna regla con un permiso requerido, verifica si el usuario tiene el permiso antes de mostrarlo.

**DynamicSecurityAccess**

La clase **DynamicSecurityAccess** se encarga de esto internamente en los objetos **Showable** y los handlers.

**Acción al denegar acceso**

La clase **DynamicSecurityAccess** tiene el siguiente método que se puede configurar para ejecutar una acción al momento de que se deniega un acceso:

PHP

    /**
         * Clausura que se ejecuta cuando se deniega un permiso.
         *
         * Esta propiedad permite configurar una clausura (closure) que se ejecutará
         * cuando un permiso es denegado por las reglas de acceso. La clausura recibe el
         * permiso denegado y puede utilizarse para definir una acción específica que se
         * realizará en caso de acceso no autorizado.
         *
         * @var Closure|null $onPermissionDenny Permiso denegado clausura.
         */
        public static ?Closure $onPermissionDenny;




Este método se puede utilizar para ejecutar una acción personalizada en caso de que un usuario no tenga el permiso necesario para acceder a un objeto **Showable**.

**Ejemplo de uso**

El siguiente ejemplo muestra cómo utilizar la capa de seguridad para verificar que un usuario tenga el permiso "ver\_facturas" para ver un objeto **Showable** que representa una factura:

PHP

    $dao = new InvoiceDAO();
    $invoice = $dao->findById(1);
    
    $showable = new InvoiceShowable($invoice);
    
    
    
    // El usuario tiene el permiso, por lo que se muestra el objeto.
    $showable->show();




En este ejemplo, la implementation de show debe verificar que se cumplan los accesos de seguridad.

