<?php

namespace HandlerCore\components;

/**
 * La interfaz ShowableInterface define un método para mostrar información.
 */
interface ShowableInterface
{
    /**
     * Muestra información al usuario.
     *
     * Esta función debe ser implementada por las clases que implementen esta interfaz.
     * Debería generar código HTML relevante al usuario para presentar datos de alguna manera.
     *
     * @return void
     */
    function show();
}

