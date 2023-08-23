<?php
namespace HandlerCore\models\dao;
	/**
	 *
	 */
	class ConfigVarDAO extends AbstractBaseDAO {
		const VAR_PERMISSION_CHECK="PERMISSION_CHECK";
		const VAR_AUTO_AN_RACK_LV = "AUTO_AN_RACK_LV";
		const VAR_AUTO_WAREHOUSE="AUTO_WAREHOUSE";
		const VAR_ENABLE_AUTO_CUSTOMER="ENABLE_AUTO_CUSTOMER";


		const VAR_DEFAULT_CUSTOMER  ="DEFAULT_CUSTOMER";

		const VAR_CONF_PORTAL_BASE_URL="CONF_PORTAL_BASE_URL";
		const VAR_QUOTE_EXPIRATION_DAYS = "QUOTE_EXPIRATION_DAYS";
		const VAR_QUOTE_WEB_ENABLE_RESERV ="QUOTE_WEB_ENABLE_RESERV";
		const VAR_QUOTE_AUTO_ENABLE_RESERV ="QUOTE_AUTO_ENABLE_RESERV";

		const VAR_ENABLE_RECORD_SECURITY  ="ENABLE_RECORD_SECURITY";
		const VAR_ENABLE_DASH_SECURITY  ="ENABLE_DASH_SECURITY";
		const VAR_ENABLE_DASH_BUTTON_SECURITY  ="ENABLE_DASH_BUTTON_SECURITY";
		const VAR_ENABLE_HANDLER_ACTION_SECURITY  ="ENABLE_HANDLER_ACTION_SECURITY";




		const VAR_LOGO_FILENAME="LOGO_FILENAME";

		private static $cashe_vars;

		function __construct() {
			parent::__construct("config_vars", array("var"));
		}

		function getPrototype(){
			$prototype = array(
				'var'=>null,
				'val'=>null
			);

			return $prototype;
		}


		function getDBMap(){
			$prototype = array(
				'var'=>'var',
				'val'=>'val'
			);

			return $prototype;
		}

		function getBaseSelec(){
			$sql = "SELECT `config_vars`.`var`,
					    `config_vars`.`val`
					    
					FROM `config_vars`
					WHERE ";

			return $sql;
		}


		function getActives(){
			$searchArray["config_vars.var"] = self::$SQL_TAG . " is not null";
			$searchArray = self::putQuoteAndNull($searchArray, !self::REMOVE_TAG);
			$where = self::getSQLFilter($searchArray);

			$sql = $this->getBaseSelec() . $where;


			$this->find($sql);
		}

		public function getVar($var, $force_reload=false)
		{
			if(self::$cashe_vars == null){
				self::$cashe_vars = array();
			}

			//si no esta en cache o si se fuerza la recarga
			if(!isset(self::$cashe_vars[$var]) || $force_reload){

				$this->getById(array("var"=>$var));
				$row = $this->get();

				if(isset($row["var"])){
					self::$cashe_vars[$var] = $row["val"];
				}

			}else{
				//var_dump("cashe: $var");
				$row["var"] = $var;
				$row["val"] = self::$cashe_vars[$var];
			}



			return (isset($row["var"]))?  $row["val"]: null;
		}

		public function setVar($var,$val)
		{
			$prototype = array();
			$prototype["var"]= $var;
			$prototype["val"]= $val;

			return $this->save($prototype);
		}

	}

?>
