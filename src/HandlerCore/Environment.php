<?php

namespace HandlerCore;

class Environment
{
    public static string $APP_DATE_FORMAT = "";
    public static string $DB_DATE_FORMAT = "";
    public static string $PATH_PRIVATE = "";
    public static bool $APP_ENABLE_BD_FUNCTION=false;
    public static string $APP_CONTENT_BODY="";
    public static int $APP_DEFAULT_LIMIT_PER_PAGE=15;
    public static string $PATH_ROOT="";
    public static string $APP_LANG="es";
    public static string $PATH_HANDLERS="";
    public static string $APP_CONTENT_TITLE="";
    public static string $APP_HIDDEN_CONTENT="";
    public static string $NAMESPACE_HANDLERS="";
    public static string $NAMESPACE_MODELS="";
    public static string $ACCESS_PERMISSION="";
    public static string $CONFIG_VAR_REPORT_TAG="configvar";
    public static string $DB_DISPLAY_DATE_FORMAT="";
}
