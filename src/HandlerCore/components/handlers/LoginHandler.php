<?php

namespace HandlerCore\components\handlers;

use HandlerCore\components\DynamicSecurityAccess;
use HandlerCore\components\Handler;
use HandlerCore\components\UnsecureHandler;
use HandlerCore\Environment;
use HandlerCore\models\dao\ConfigVarDAO;
use HandlerCore\models\dao\ConnectionFromDAO;
use HandlerCore\models\dao\UserProfileDAO;
use HandlerCore\models\SimpleDAO;
use function HandlerCore\getRealIpAddr;


/**
 *
 */
abstract class LoginHandler extends Handler implements UnsecureHandler
{
    const KEY_WAREHOUSE_ID = "warehouse_id";
    const KEY_AVATAR_ID = "avatar_id";

    abstract function indexAction();

    /**
     * @param $user_id
     * @param bool $inSession
     * @throws \Exception
     */
    public static function loadPermissions($user_id, bool $inSession = true)
    {
        $sql = "select permission from group_permissions where group_id in
							(select group_id FROM group_users where user_id='" . $user_id . "')
							UNION
							( SELECT permission FROM user_permissions WHERE user_id='" . $user_id . "' )";
        $sumary = SimpleDAO::execQuery($sql);
        $permisos = SimpleDAO::getAll($sumary);
        $permissionList = [];
        foreach ($permisos as $value) {
            //echo $value["permission"];
            $permissionList[] = $value["permission"];
        }

        DynamicSecurityAccess::loadPermissions($permissionList, $inSession);
    }

    function accessByTokenAction()
    {
        $uname = $this->getRequestAttr("user");
        $token = $this->getRequestAttr("token");

        $conn = new ConnectionFromDAO();

        //busca un token valido
        $conn->getValidToken($uname);
        $conection_data = $conn->get();

        //si hay token
        if (!$token || $conection_data["token"] != $token || $token == "") {

            $valid = false;

            $user_data = array();
        } else {


            $valid = true;

            $sql = "select uid, count(uid) as valid, password, LDAP, nombre, apellidos 
					FROM users 
					where active=1 and username = '$uname'";

            $user_data = SimpleDAO::execAndFetch($sql);
        }


        $this->startAccess($uname, $user_data, $valid);
    }

    function loginAction()
    {
        $uname = $this->getRequestAttr("user");
        $pass = $this->getRequestAttr("pass");
        $valid = false;

        $sql = "select uid, count(uid) as valid, password, LDAP, nombre, apellidos FROM users where active=1 and username = '$uname'";

        $user_data = SimpleDAO::execAndFetch($sql);


        if ($user_data["valid"] == '1') {

            if ($user_data["LDAP"] == '1') {
                /* try {
                    $adldap = new adLDAP();
                }
                catch (adLDAPException $e) {
                    echo $e;
                    exit();
                }

                if ($adldap->authenticate($uname, $pass)){
                    $valid= true;
                }*/
            } else {
                if ($user_data["password"] == md5($pass)) {
                    $valid = true;
                }
            }


            $this->startAccess($uname, $user_data, $valid);


        } else {
            $this->windowReload("login?error=t");
        }


    }

    private function startAccess($uname, $user_data, $valid)
    {

        if ($valid) {
            //carga datos de session
            $_SESSION['USER_ID'] = $user_data["uid"];
            $_SESSION["usuario_nombre"] = $user_data["nombre"] . " " . $user_data["apellidos"];
            $_SESSION['USER_NAME'] = $uname;

            Handler::$SESSION['USER_ID'] = $user_data["uid"];
            Handler::$SESSION['USER_NAME'] = $uname;

            $userD = new UserProfileDAO();
            $userD->getByUsername($uname);
            $profile = $userD->get();
            $_SESSION[self::KEY_WAREHOUSE_ID] = $profile["warehouse_id"];
            $_SESSION[self::KEY_AVATAR_ID] = $profile["img_id"];


            self::loadPermissions($user_data["uid"]);

            self::updateAccessRecord();

            self::loadConf();

            $this->registerLogin($uname, "OK");

            $this->windowReload(Environment::$START_HANDLER);

            exit;
        } else {
            $this->registerLogin($uname, "BAD");
            $this->windowReload(Environment::$ACCESS_HANDLER . "?error=t");
        }
    }

    function logoutAction(): void
    {
        $this->registerLogout($this->getUsename());
        session_destroy();
        session_unset();

        $this->windowReload(Environment::$ACCESS_HANDLER);
    }

    public static function setWarehouse($warehouse_id): void
    {
        $_SESSION[self::KEY_WAREHOUSE_ID] = $warehouse_id;
    }

    public static function getWarehouse()
    {
        return $_SESSION[self::KEY_WAREHOUSE_ID];
    }

    public static function getAvatarID()
    {
        return $_SESSION[self::KEY_AVATAR_ID];
    }

    public static function setAvatarID($avatar_id): void
    {
        $_SESSION[self::KEY_AVATAR_ID] = $avatar_id;
    }

    public static function getUserID()
    {
        return $_SESSION['USER_ID'];
    }

    public static function updateAccessRecord(): void
    {
        #registra en session tiempo actual
        $_SESSION["LAST_RECORD"] = date("Y-m-d H:i:s");

        #registra en BD tiempo actual
        SimpleDAO::_update(
            "users",
            SimpleDAO::putQuoteAndNull(array("lastlogin" => $_SESSION["LAST_RECORD"])),
            SimpleDAO::putQuoteAndNull(array("uid" => $_SESSION["USER_ID"]))
        );
    }

    private function registerLogin($uname, $status): void
    {
        #registra en session tiempo actual


        #registra en BD tiempo actual
        SimpleDAO::_insert("record", SimpleDAO::putQuoteAndNull(array(
            "username" => $uname,
            "ip" => getRealIpAddr(),
            "Action" => "LOGIN",
            "status" => $status

        )));
    }

    private function registerLogout($uname): void
    {
        #registra en session tiempo actual


        #registra en BD tiempo actual
        SimpleDAO::_insert("record", SimpleDAO::putQuoteAndNull(array(
            "username" => $uname,
            "user_id" => self::getUserID(),
            "ip" => getRealIpAddr(),
            "Action" => "LOGOUT",
            "status" => "OK"

        )));
    }

    public static function loadConf(): void
    {
        $conf = new ConfigVarDAO();

        $p = $conf->getVar(ConfigVarDAO::VAR_PERMISSION_CHECK);
        //si esta y es Y, estara activado
        $_SESSION["CONF"][ConfigVarDAO::VAR_PERMISSION_CHECK] = ($p && $p == SimpleDAO::REG_ACTIVO_Y);

        $p = $conf->getVar(ConfigVarDAO::VAR_ENABLE_RECORD_SECURITY);
        //si esta y es Y, estara activado
        $_SESSION["CONF"][ConfigVarDAO::VAR_ENABLE_RECORD_SECURITY] = ($p && $p == SimpleDAO::REG_ACTIVO_Y);

        $p = $conf->getVar(ConfigVarDAO::VAR_ENABLE_DASH_SECURITY);
        //si esta y es Y, estara activado
        $_SESSION["CONF"][ConfigVarDAO::VAR_ENABLE_DASH_SECURITY] = ($p && $p == SimpleDAO::REG_ACTIVO_Y);

        $p = $conf->getVar(ConfigVarDAO::VAR_ENABLE_DASH_BUTTON_SECURITY);
        //si esta y es Y, estara activado
        $_SESSION["CONF"][ConfigVarDAO::VAR_ENABLE_DASH_BUTTON_SECURITY] = ($p && $p == SimpleDAO::REG_ACTIVO_Y);

        $p = $conf->getVar(ConfigVarDAO::VAR_ENABLE_HANDLER_ACTION_SECURITY);
        //si esta y es Y, estara activado
        $_SESSION["CONF"][ConfigVarDAO::VAR_ENABLE_HANDLER_ACTION_SECURITY] = ($p && $p == SimpleDAO::REG_ACTIVO_Y);
    }

    function logoAction()
    {


        $conf = new ConfigVarDAO();
        $logo = $conf->getVar(ConfigVarDAO::VAR_LOGO_FILENAME);

        if ($logo) {

            header('Content-Type: ' . mime_content_type($logo));
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            $f = file_get_contents($logo);

            echo $f;
        }
    }

}
