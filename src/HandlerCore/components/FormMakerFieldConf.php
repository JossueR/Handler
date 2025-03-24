<?php

namespace HandlerCore\components;

use HandlerCore\models\dao\AbstractBaseDAO;

/**
 * Clase FormMakerFieldConf que genera arreglos de configuración para campos de FormMaker.
 */
class FormMakerFieldConf
{
    /**
     * @var string $campo Nombre único del campo.
     */
    private $campo;

    /**
     * @var string $label Etiqueta del campo.
     */
    private $label;

    /**
     * @var string $tipo Tipo del campo.
     */
    private $tipo;

    /**
     * @var AbstractBaseDAO|array $source Fuente de datos del campo, utilizado en campos de selección (selects).
     */
    private $source;

    /**
     * @var string $action Acción que se llamará al ejecutar una búsqueda.
     */
    private $action;

    /**
     * @var array $params Parámetros de la acción que se llamará al ejecutar una búsqueda.
     */
    private $params;

    /**
     * @var string $showAction Acción que se llamará al mostrar un resultado.
     */
    private $showAction;

    /**
     * @var array $showParams Parámetros de la acción que se llamará al mostrar un resultado.
     */
    private $showParams;

    /**
     * @var array $html Arreglo de atributos que se pasarán como atributos del tag HTML generado.
     */
    private $html;

    /**
     * @var string $wraper Nombre del ID del tag HTML que se generará para envolver el campo generado.
     */
    private $wraper;

    /**
     * @var bool $required Indica si el campo es requerido.
     */
    private $required;

    /**
     * Constructor de la clase FormMakerFieldConf.
     *
     * @param string $campo Nombre único del campo.
     */
    public function __construct($campo)
    {
        $this->campo = $campo;
    }

    /**
     * Establece la etiqueta del campo.
     *
     * @param string $label Etiqueta del campo.
     * @return FormMakerFieldConf La instancia actual de FormMakerFieldConf.
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Establece el tipo del campo.
     *
     * @param string $tipo Tipo del campo.
     * @return FormMakerFieldConf La instancia actual de FormMakerFieldConf.
     */
    public function setType($tipo)
    {
        $this->tipo = $tipo;
        return $this;
    }

    /**
     * Establece la fuente de datos del campo.
     *
     * @param AbstractBaseDAO|array $source Fuente de datos del campo.
     * @return FormMakerFieldConf La instancia actual de FormMakerFieldConf.
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Establece la acción que se llamará al ejecutar una búsqueda.
     *
     * @param string $action Acción que se llamará al ejecutar una búsqueda.
     * @return FormMakerFieldConf La instancia actual de FormMakerFieldConf.
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * Establece los parámetros de la acción que se llamará al ejecutar una búsqueda.
     *
     * @param array $params Parámetros de la acción de búsqueda.
     * @return FormMakerFieldConf La instancia actual de FormMakerFieldConf.
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * Establece la acción que se llamará al mostrar un resultado.
     *
     * @param string $showAction Acción que se llamará al mostrar un resultado.
     * @return FormMakerFieldConf La instancia actual de FormMakerFieldConf.
     */
    public function setShowAction($showAction)
    {
        $this->showAction = $showAction;
        return $this;
    }

    /**
     * Establece los parámetros de la acción que se llamará al mostrar un resultado.
     *
     * @param array $showParams Parámetros de la acción de visualización.
     * @return FormMakerFieldConf La instancia actual de FormMakerFieldConf.
     */
    public function setShowParams($showParams)
    {
        $this->showParams = $showParams;
        return $this;
    }

    /**
     * Establece atributos HTML para el campo.
     *
     * @param array $html Arreglo de atributos HTML.
     * @return FormMakerFieldConf La instancia actual de FormMakerFieldConf.
     */
    public function setHtml($html)
    {
        $this->html = $html;
        return $this;
    }

    /**
     * Establece el ID del tag HTML que envolverá el campo generado.
     *
     * @param string $wraper Nombre del ID del tag HTML envolvente.
     * @return FormMakerFieldConf La instancia actual de FormMakerFieldConf.
     */
    public function setWraper($wraper)
    {
        $this->wraper = $wraper;
        return $this;
    }

    /**
     * Establece si el campo es requerido.
     *
     * @param bool $required Indica si el campo es requerido.
     * @return FormMakerFieldConf La instancia actual de FormMakerFieldConf.
     */
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Genera el arreglo de configuración con los parámetros configurados para el campo.
     *
     * @return array Arreglo de configuración para el campo.
     */
    public function build(): array
    {
        $conf = array(
            "campo" => $this->campo
        );

        if (isset($this->label)) {
            $conf["label"] = $this->label;
        }

        if (isset($this->tipo)) {
            $conf["tipo"] = $this->tipo;
        }

        if (isset($this->source)) {
            $conf["source"] = $this->source;
        }

        if (isset($this->action)) {
            $conf["action"] = $this->action;
        }

        if (isset($this->params)) {
            $conf["params"] = $this->params;
        }

        if (isset($this->showAction)) {
            $conf["showAction"] = $this->showAction;
        }

        if (isset($this->showParams)) {
            $conf["showParams"] = $this->showParams;
        }

        if (isset($this->html)) {
            $conf["html"] = $this->html;
        }

        if (isset($this->wraper)) {
            $conf["wrapper"] = $this->wraper;
        }

        if (isset($this->required)) {
            $conf["required"] = $this->required;
        }

        return $conf;
    }

    public function getCampo(): string
    {
        return $this->campo;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function getSource(): AbstractBaseDAO|array
    {
        return $this->source;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getShowAction(): string
    {
        return $this->showAction;
    }

    public function getShowParams(): array
    {
        return $this->showParams;
    }

    public function getHtml(): array
    {
        return $this->html;
    }

    public function getWraper(): string
    {
        return $this->wraper;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }


}
