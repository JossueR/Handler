<?php

namespace HandlerCore\components;

use HandlerCore\components\FormMakerFieldConf;

abstract class FormFieldCustom extends FormMakerFieldConf
{
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
}
