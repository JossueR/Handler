<?php

namespace HandlerCore\components;

use HandlerCore\components\FormMakerFieldConf;

abstract class FormFieldCustom extends FormMakerFieldConf
{
    private $value;

    public function __construct($campo)
    {
        parent::__construct($campo);
        $this->setType(FormMaker::FIELD_TYPE_CUSTOM);
    }


    public function setType($tipo): void
    {
        $tipo = FormMaker::FIELD_TYPE_CUSTOM;
        parent::setType($tipo);
    }


    abstract function makeField(): string;

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }
}
