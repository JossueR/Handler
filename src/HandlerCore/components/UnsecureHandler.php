<?php

namespace HandlerCore\components;

/**
 * Interfaz para identificar controladores que no requieren autenticación.
 *
 * Esta interfaz se utiliza para marcar los controladores que no necesitan un proceso de autenticación
 * antes de ser ejecutados. Al implementar esta interfaz, se indica que el controlador en cuestión
 * está diseñado para ser accesible sin requerir un inicio de sesión previo.
 */
interface UnsecureHandler
{

}
