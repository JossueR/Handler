<?php

namespace HandlerCore;

use ReflectionClass;

/**
 * Clase Environment para configuración del framework.
 *
 * Esta clase contiene variables de configuración que se utilizan en el framework para diferentes propósitos,
 * como formatos de fecha, rutas, nombres de espacios, permisos, etc. Estas variables se utilizan para ajustar el
 * comportamiento y la apariencia del framework de acuerdo a las necesidades del proyecto.
 */
class Environment
{
    /**
     * @var string Formato de fecha de la aplicación
     */
    public static string $APP_DATE_FORMAT = "";

    /**
     * @var string Formato de fecha de la base de datos
     */
    public static string $DB_DATE_FORMAT = "";

    /**
     * @var string Ruta a la carpeta privada
     */
    public static string $PATH_PRIVATE = "";

    /**
     * @var bool Habilitar función de secuenciales de base de datos
     */
    public static bool $APP_ENABLE_BD_FUNCTION=false;

    /**
     * @var string Contenedor del contenido principal
     */
    public static string $APP_CONTENT_BODY="";

    /**
     * @var int Límite predeterminado por página
     */
    public static int $APP_DEFAULT_LIMIT_PER_PAGE=15;

    /**
     * @var string Ruta raíz de la aplicación
     */
    public static string $PATH_ROOT="";

    /**
     * @var string Idioma predeterminado de la aplicación
     */
    public static string $APP_LANG="es";

    /**
     * @var string Ruta a los controladores
     */
    public static string $PATH_HANDLERS="";

    /**
     * @var string Elemento para mostrar el título del contenido
     */
    public static string $APP_CONTENT_TITLE="";

    /**
     * @var string Contenido oculto
     */
    public static string $APP_HIDDEN_CONTENT="";

    /**
     * @var string Espacio de nombres para los controladores
     */
    public static string $NAMESPACE_HANDLERS="";

    /**
     * @var string Espacio de nombres para los modelos
     */
    public static string $NAMESPACE_MODELS="";

    /**
     * @var string Permiso de acceso principal para el area privada
     */
    public static string $ACCESS_PERMISSION="";

    /**
     * @var string Etiqueta de reportes para mapear las variables de configuración
     */
    public static string $CONFIG_VAR_REPORT_TAG="configvar";

    /**
     * @var string Formato de fecha para visualización de base de datos
     */
    public static string $DB_DISPLAY_DATE_FORMAT="";

    /**
     * @var string Controlador de acceso
     */
    public static string $ACCESS_HANDLER="login";

    /**
     * @var string Controlador de inicio
     */
    public static string $START_HANDLER="home";

    /**
     * @var string Ruta para la carga de archivos
     */
    public static string $PATH_UPLOAD="";

    /**
     * Obtiene la ruta de la clase Environment.
     *
     * Este método utiliza reflexión para obtener la ruta en el sistema de archivos donde se encuentra la clase
     * Environment.
     *
     * @return string La ruta de la clase Environment en el sistema de archivos.
     */
    public static function getPath(): string
    {
        $reflection = new ReflectionClass(Environment::class);
        return dirname($reflection->getFileName());
    }
}
