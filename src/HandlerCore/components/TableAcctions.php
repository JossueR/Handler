<?php
namespace HandlerCore\components;
/**
*Create Date: 09/24/2012
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 102 $
*/

/**
 * Clase que genera un arreglo de configuraciones de acciones para ser utilizadas con la clase TableGenerator.
 */
class TableAcctions {
	private array $allActions;

	const EDIT_ICON="icon-1";
	const SELECT_ICON="icon-5";

    /**
     * Constructor de la clase. Inicializa el arreglo de acciones.
     */
	function __construct() {
		$this->allActions = array();
	}

    /**
     * Agrega una acción al arreglo de acciones disponibles.
     *
     * @param string $text El texto que se mostrará para la acción.
     * @param string $action El identificador de la acción.
     * @param array|null $html Configuración HTML adicional para la acción (opcional).
     * @return void
     */
	public function addAction(string $text, string $action, $html= null): void
    {
		if(!$html){
			$html = array();
		}

		$i = count($this->allActions);

		$this->allActions[$i]["TEXT"] = $text;
		$this->allActions[$i]["ACTION"] = $action;
		$this->allActions[$i]["HTML"] = $html;
	}

    /**
     * Obtiene todas las acciones configuradas.
     *
     * @return array Arreglo de configuraciones de acciones.
     */
	public function getAllActions(): array
    {
		return $this->allActions;
	}

}
