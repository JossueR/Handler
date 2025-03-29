<?php

namespace HandlerCore\components;

trait ErrorTrait
{
    private array $errors = [];

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError($msg): void
    {
        //echo $msg . "<br>";
        $this->errors[] = $msg;
    }

    public function addErrors($erros): void
    {
        if(is_array($erros)) {
            $this->errors = array_merge($this->errors, $erros);
        }
    }

    public function haveErrors(): bool
    {
        return count($this->errors) > 0;
    }
}