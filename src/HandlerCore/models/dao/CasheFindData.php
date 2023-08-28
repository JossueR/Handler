<?php

namespace HandlerCore\models\dao;

/**
 * La clase CasheFindData representa un elemento en la caché para datos encontrados.
 */
class CasheFindData
{
    /** @var mixed Identificador único del elemento en la caché. */
    private $id;

    /** @var mixed Resumen o información descriptiva del elemento en la caché. */
    private $sumary;

    /**
     * Constructor de la clase CasheFindData.
     *
     * @param mixed $id Identificador único del elemento en la caché.
     * @param mixed $summary Resumen o información descriptiva del elemento en la caché.
     */
    function __construct($id, $summary)
    {
        $this->id = $id;
        $this->sumary = $summary;
    }

    /**
     * Obtiene el resumen del elemento en la caché.
     *
     * @return mixed Resumen o información descriptiva del elemento.
     */
    function getSummary()
    {
        return $this->sumary;
    }

    /**
     * Obtiene el identificador único del elemento en la caché.
     *
     * @return mixed Identificador único del elemento.
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * Compara el identificador del elemento con otro identificador o array de identificadores.
     *
     * @param mixed|array $id Identificador o array de identificadores a comparar.
     * @return bool Devuelve true si el identificador coincide con el proporcionado, de lo contrario devuelve false.
     */
    function equals($id)
    {
        $status = false;


        if (is_array($id) && is_array($this->id)) {

            if ($id == $this->id) {
                $status = true;
            }
        }

        return $status;
    }
}
