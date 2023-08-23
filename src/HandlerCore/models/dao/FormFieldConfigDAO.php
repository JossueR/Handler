<?php

namespace HandlerCore\models\dao;




/**
 *
 */
class FormFieldConfigDAO extends AbstractBaseDAO
{
    const SOURCE_TYPE_DAO = "DAO";
    const SOURCE_TYPE_ARRAY = "ARRAY";
    const SOURCE_TYPE_SQL = "SQL";

    function __construct()
    {
        parent::__construct("form_field_config", array("id"));
    }

    function getPrototype()
    {
        $prototype = array(

            'form_name' => null,
            'field_name' => null,

            'type' => null,
            'html_attrs' => null,
            'label' => null,
            'source_type' => null,
            'source_dao' => null,
            'source_method' => null,
            'source_id' => null,
            'source_name' => null,
            'required' => null,
            'required_clausure' => null,
            'post_script' => null
        );

        return $prototype;
    }


    function getDBMap()
    {
        $prototype = array(
            'id' => 'id',
            'form_name' => 'form_name',
            'field_name' => 'field_name',
            'active' => 'active',
            'type' => 'type',
            'html_attrs' => 'html_attrs',
            'label' => 'label',
            'source_type' => 'source_type',
            'source_dao' => 'source_dao',
            'source_method' => 'source_method',
            'source_id' => 'source_id',
            'source_name' => 'source_name',
            'required' => 'required',
            'required_clausure' => 'required_clausure',
            'post_script' => 'post_script'


        );

        return $prototype;
    }

    function getBaseSelec()
    {
        $sql = "SELECT `form_field_config`.`id`,
					    `form_field_config`.`form_name`,
					    `form_field_config`.`field_name`,
					    `form_field_config`.`active`,
					    `form_field_config`.`type`,
					    `form_field_config`.`html_attrs`,
					    `form_field_config`.`label`,
					    `form_field_config`.`source_type`,
					    `form_field_config`.`source_dao`,
					    `form_field_config`.`source_method`,
					    `form_field_config`.`source_id`,
					    `form_field_config`.`source_name`,
					    `form_field_config`.`required`,
					    `form_field_config`.`required_clausure`,
					    `form_field_config`.`post_script`,
					    `form_field_config`.`create_user`,
					    `form_field_config`.`create_date`,
					    `form_field_config`.`update_user`,
					    `form_field_config`.`update_date`
					FROM `form_field_config`
					WHERE ";

        return $sql;
    }


    function getActives()
    {
        $searchArray["form_field_config.active"] = self::REG_ACTIVO_TX;
        $searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
        $where = self::getSQLFilter($searchArray);

        $sql = $this->getBaseSelec() . $where;


        $this->find($sql);
    }


    function getByName($name)
    {
        $searchArray["form_field_config.active"] = self::REG_ACTIVO_TX;
        $searchArray["form_field_config.form_name"] = $name;
        $searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
        $where = self::getSQLFilter($searchArray);

        $sql = $this->getBaseSelec() . $where;


        $this->find($sql);
    }

    function getByFieldName($form_name, $field_name)
    {
        $searchArray["form_field_config.active"] = self::REG_ACTIVO_TX;
        $searchArray["form_field_config.form_name"] = $form_name;
        $searchArray["form_field_config.field_name"] = $field_name;
        $searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
        $where = self::getSQLFilter($searchArray);

        $sql = $this->getBaseSelec() . $where;


        $this->find($sql);
    }


    function getInactives()
    {
        $searchArray["form_field_config.active"] = self::REG_DESACTIVADO_TX;
        $searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
        $where = self::getSQLFilter($searchArray);

        $sql = $this->getBaseSelec() . $where;


        $this->find($sql);
    }


    function &insert($searchArray)
    {
        $defaul["create_date"] = self::$SQL_TAG . "now()";
        $defaul["create_user"] = $_SESSION['USER_NAME'];
        $defaul["active"] = self::REG_ACTIVO_TX;
        $defaul = parent::putQuoteAndNull($defaul);

        $searchArray = array_merge($searchArray, $defaul);


        return parent::insert($searchArray);

    }

    function &update($searchArray, $condicion)
    {
        $defaul["update_date"] = self::$SQL_TAG . "now()";
        $defaul["update_user"] = $_SESSION['USER_NAME'];
        $defaul = parent::putQuoteAndNull($defaul);

        $searchArray = array_merge($searchArray, $defaul);
        return parent::update($searchArray, $condicion);
    }

    function validateForm($form_name, $proto)
    {
        $status = true;

        $this->getByName($form_name);

        //para cada campo
        while ($field_data = $this->get()) {

            if ($field_data["required"] != self::REG_ACTIVO_Y) {
                continue;
            } else {
                $field_name = $field_data["field_name"];
                // si el campo es requerido
                if ($proto[$field_name] == "") {
                    $status = false;

                    $this->errors[$field_name] = "required";

                }
            }
        }

        return $status;
    }

}
