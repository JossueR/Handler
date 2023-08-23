<?php

namespace HandlerCore\models\dao;


/**
 *
 */
class UserProfileDAO extends AbstractBaseDAO
{

    function __construct()
    {
        parent::__construct("user_profile", array("username"));
    }

    function getPrototype()
    {
        $prototype = array(


            'warehouse_id' => null,
            'default_homepage' => null,
            'default_homepage_params' => null,
            'img_id' => null,
        );

        return $prototype;
    }


    function getDBMap()
    {
        $prototype = array(
            'username' => 'username',
            'warehouse_id' => 'warehouse_id',
            'img_id' => 'img_id',
            'default_homepage' => 'default_homepage',
            'default_homepage_params' => 'default_homepage_params',
        );

        return $prototype;
    }

    function getBaseSelec()
    {
        $sql = "SELECT `user_profile`.`username`,
					    `user_profile`.`warehouse_id`,
					    `user_profile`.`img_id`,
					    `user_profile`.`default_homepage`,
					    `user_profile`.`default_homepage_params`,
					    `user_profile`.`create_date`,
					    `user_profile`.`create_user`,
					    `user_profile`.`update_date`,
					    `user_profile`.`update_user`
					FROM .`user_profile`
					WHERE ";

        return $sql;
    }


    function getByUsername($username)
    {
        $searchArray["username"] = $username;
        $searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
        $where = self::getSQLFilter($searchArray);

        $sql = $this->getBaseSelec() . $where;


        $this->find($sql);
    }

    function getActives()
    {
        $searchArray["username"] = self::$SQL_TAG . " <> ''";
        $searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
        $where = self::getSQLFilter($searchArray);

        $sql = $this->getBaseSelec() . $where;


        $this->find($sql);
    }


    function &insert($searchArray)
    {
        $defaul["create_date"] = self::$SQL_TAG . "now()";
        $defaul["create_user"] = self::getDataVar("USER_NAME");
        $defaul = parent::putQuoteAndNull($defaul);

        $searchArray = array_merge($searchArray, $defaul);


        return parent::insert($searchArray);

    }

    function &update($searchArray, $condicion)
    {
        $defaul["update_date"] = self::$SQL_TAG . "now()";
        $defaul["update_user"] = self::getDataVar("USER_NAME");
        $defaul = parent::putQuoteAndNull($defaul);

        $searchArray = array_merge($searchArray, $defaul);
        return parent::update($searchArray, $condicion);
    }

}
