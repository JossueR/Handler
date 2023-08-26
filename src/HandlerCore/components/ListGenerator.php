<?php
namespace HandlerCore\components;
use HandlerCore\Environment;
use HandlerCore\models\dao\AbstractBaseDAO;

use function HandlerCore\showMessage;

/**
 * Clase para generar un bloque HTML con una lista de parámetros extraídos de un registro de la base de datos.
 */
class ListGenerator extends Handler {
    /**
     * @var AbstractBaseDAO El DAO utilizado para acceder a la base de datos y obtener los registros.
     */
    private $dao;

    /**
     * @var string El campo que se mostrará en la lista para cada registro.
     */
    private $showField;

    /**
     * @var string El mensaje que se mostrará cuando no haya registros para mostrar.
     */
    public $msgNoRecord;

    /**
     * @var string El nombre del bloque HTML generado.
     */
    public $name = "";

    /**
     * @var string El enlace de cancelación que se mostrará en el bloque HTML.
     */
    public $cancelLink = "";

    /**
     * @var string La ruta del archivo de esquema para generar el bloque HTML de la lista.
     */
    public $squema_list;

    /**
     * Constructor de la clase ListGenerator.
     *
     * @param AbstractBaseDAO $dao El DAO utilizado para acceder a la base de datos y obtener los registros.
     */
	function __construct( AbstractBaseDAO $dao) {
        $this->dao = $dao;
        $this->usePrivatePathInView=false;
		$this->squema_list = Environment::getPath() .  "/views/common/list.php";
    }

    /**
     * Establece el campo que se mostrará en la lista para cada registro.
     *
     * @param string $field El nombre del campo que se mostrará.
     * @return void
     */
	function setShowField($field){
		$this->showField=$field;
	}

    /**
     * Muestra el bloque HTML de la lista generada.
     *
     * @return void
     */
	function show(){
		if(!$this->msgNoRecord){
			$this->msgNoRecord=showMessage("defaultNoRecord");
		}
		$this->display($this->squema_list, get_object_vars($this));
	}
}
