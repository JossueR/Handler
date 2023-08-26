<?php
/**
*Create Date: 09/24/2012
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 102 $
*/
namespace HandlerCore\components;

use HandlerCore\models\dao\BookmarkDAO;

/**
 * La clase Bookmark se encarga de registrar y cargar marcadores de preferencias de búsqueda.
 *
 * Esta clase permite a los usuarios guardar y cargar sus preferencias de búsqueda para diferentes filtros, campos
 * de orden, tipos de orden y páginas. Los marcadores de preferencias de búsqueda pueden ser útiles para guardar y
 * reutilizar configuraciones de búsqueda específicas en una aplicación.
 */
class Bookmark {
    /**
     * @var string // Variables públicas para identificar las claves de los marcadores de preferencias
     */
	static public $search_filter = "FILTER";
	static public $filter_fields = "FILTER_KEYS";
	static public $order_field = "FIELD";
	static public $order_type = "ASC";
	static public $page = "PAGE";

	private $invoker;
	private $dao;
	private $haveBookmark;
	private $bookmark;


    /**
     * Constructor de la clase Bookmark.
     *
     * @param $invoker string Objeto que invoca la clase Bookmark.
     */
	function __construct($invoker) {
		$this->invoker = $invoker;
		$this->dao = new BookmarkDAO();
		$this->haveBookmark = false;


	}

    /**
     * Obtiene los marcadores de preferencias de búsqueda relacionados con el objeto invocador y el usuario en sesión.
     *
     * @return array|null Un array con los marcadores de preferencias de búsqueda o null si no se encuentran.
     */
	public function getInvokerBookmark(){
		$this->dao->getMyBookmark($this->invoker);
		$b = $this->dao->get();

		//si encontro bookmark lo establece a true
		$this->haveBookmark = ($b != null);
		$this->bookmark = $b;
		return $b;
	}

    /**
     * Carga los marcadores de preferencias de búsqueda y los aplica a los filtros y campos de orden.
     *
     * @param string $filter_fields Filtros a cargar.
     */
	public function loadBookmark($filter_fields = ""){
		//guarda los bookmarks originales si se enviaron
		$new = $this->saveBookmark();

		//si no se enviaron nuevos bookmarks
		if(!$new){

			//si no ests cargado el bookmark
			//if(!$this->bookmark){
				//carga los bookmark
				$this->getInvokerBookmark();
			//}



			//si el invoker tiene bookmark
			if($this->haveBookmark){
				$_POST[self::$search_filter] = $this->bookmark["search"];
				$_POST[self::$filter_fields] = $filter_fields;
				$_POST[self::$page] = $this->bookmark["page"];

				$decode_order = json_decode($this->bookmark["order_field"]);
				if($decode_order){
					$_POST[self::$order_field] = $decode_order;
				}else{
					$_POST[self::$order_field] = $this->bookmark["order_field"];
					$_POST[self::$order_type] = $this->bookmark["order_type"];
				}


			}
		}
	}

    /**
     * Guarda los marcadores de preferencias de búsqueda en la base de datos si hay cambios detectados.
     *
     * Este método se encarga de analizar los datos de preferencias de búsqueda proporcionados a través de las variables POST.
     * Luego, actualiza los marcadores de preferencias almacenados en la base de datos si hay cambios detectados en las preferencias.
     * Los marcadores de preferencias pueden incluir información sobre filtros de búsqueda, campos de orden, tipo de orden y página actual.
     *
     * @return bool Indica si se realizaron cambios y se guardaron los marcadores de preferencias (true) o no (false).
     */
	public function saveBookmark(){
		$need_save = false;

        // Verifica si el filtro de búsqueda está cargado en las variables POST
		if(isset($_POST[self::$search_filter])){
			$this->bookmark["search"] = $_POST[self::$search_filter];
			$need_save = true;
		}else{
			$this->bookmark["search"] = "";
		}

        // Verifica si la página actual está cargada en las variables POST
		if(isset($_POST[self::$page])){
			$this->bookmark["page"] = $_POST[self::$page];
			$need_save = true;
		}else{
			$this->bookmark["page"] = "";
		}

        // Verifica si el campo de orden está cargado en las variables POST
		if(isset($_POST[self::$order_field])){


			if(is_array($_POST[self::$order_field])){
				$this->bookmark["order_field"] = json_encode($_POST[self::$order_field]);
			}else{
				$this->bookmark["order_field"] = $_POST[self::$order_field];
			}


			if(isset($_POST[self::$order_type])){
				$this->bookmark["order_type"] = $_POST[self::$order_type];
			}else{
				$this->bookmark["order_type"] = "";
			}

			$need_save = true;
		}

        // Si hay cambios en las preferencias, guarda los marcadores en la base de datos
		if($need_save){
			$this->bookmark["invoker"] = $this->invoker;
			$this->bookmark["create_user"] = $_SESSION['USER_NAME'];
			$this->dao->save($this->bookmark);

			$this->haveBookmark = true;
		}

		return $need_save;
	}

    /**
     * Elimina las variables de preferencias de búsqueda de las variables POST, restableciéndolas a su estado inicial.
     *
     * Este método se utiliza para limpiar y restablecer las variables de preferencias de búsqueda en las variables POST.
     * Se eliminan las variables relacionadas con el filtro de búsqueda, los campos de filtro, la página actual,
     * el campo de orden y el tipo de orden. Esto permite iniciar una nueva búsqueda o restablecer las preferencias
     * de búsqueda a los valores predeterminados.
     */
	public static function unloadBookmarks(){

		self::clean(self::$search_filter);
		self::clean(self::$filter_fields);
		self::clean(self::$page);
		self::clean(self::$order_field);
		self::clean(self::$order_type);
	}

    /**
     * Limpia la variable especificada en las variables POST.
     *
     * Este método se utiliza internamente para eliminar una variable específica de las variables POST.
     * Si la variable está definida en las variables POST, se elimina para restablecer su valor.
     *
     * @param string $var El nombre de la variable a limpiar en las variables POST.
     */
	private static function clean($var){
		if(isset($_POST[$var] )){
			unset($_POST[$var] );
		}
	}

    /**
     * Obtiene el valor del filtro de búsqueda almacenado en el marcador de preferencias de búsqueda.
     *
     * Este método recupera y devuelve el valor del filtro de búsqueda almacenado en el marcador de preferencias de búsqueda.
     * Si no se ha establecido un valor de filtro, se devuelve una cadena vacía.
     *
     * @return string El valor del filtro de búsqueda o una cadena vacía si no se ha establecido.
     */
	public function getSearch(){
		$text = "";


		if($this->haveBookmark){
			$text = $this->bookmark["search"];
		}

		return $text;
	}

    /**
     * Obtiene el número de página almacenado en el marcador de preferencias de búsqueda.
     *
     * Este método recupera y devuelve el número de página almacenado en el marcador de preferencias de búsqueda.
     * Si no se ha establecido un número de página, se devuelve el valor 0.
     *
     * @return int El número de página almacenado o 0 si no se ha establecido.
     */
	public function getPage(){
		$text = 0;


		if($this->haveBookmark){
			$text = intval($this->bookmark["page"]);
		}

		return $text;
	}

    /**
     * Obtiene el campo de orden almacenado en el marcador de preferencias de búsqueda.
     *
     * Este método recupera y devuelve el campo de orden almacenado en el marcador de preferencias de búsqueda.
     * Si no se ha establecido un campo de orden, se devuelve una cadena vacía.
     *
     * @return string El campo de orden almacenado o una cadena vacía si no se ha establecido.
     */
	public function getOrderField(){
		$text = "";


		if($this->haveBookmark){
			$text = $this->bookmark["order_field"];
		}

		return $text;
	}

    /**
     * Obtiene el tipo de orden almacenado en el marcador de preferencias de búsqueda.
     *
     * Este método recupera y devuelve el tipo de orden almacenado en el marcador de preferencias de búsqueda.
     * Si no se ha establecido un tipo de orden, se devuelve una cadena vacía.
     *
     * @return string El tipo de orden almacenado o una cadena vacía si no se ha establecido.
     */
	public function getOrderType(){
		$text = "";


		if($this->haveBookmark){
			$text = $this->bookmark["order_type"];
		}

		return $text;
	}

    /**
     * Establece los campos de filtro en el marcador de preferencias de búsqueda.
     *
     * Este método permite establecer los campos de filtro en el marcador de preferencias de búsqueda.
     * Si los campos son un arreglo, se convierten en una cadena separada por comas y se almacenan en el marcador.
     *
     * @param array|string $fields Los campos de filtro a establecer en el marcador de preferencias.
     * @return void
     */
	public static function setFilterFields($fields){

		if(is_array($fields)){

			$fields = implode(",", $fields);
		}

		Handler::setRequestAttr(self::$filter_fields, $fields,true);

	}

    /**
     * Establece el campo de orden en el marcador de preferencias de búsqueda.
     *
     * Este método permite establecer el campo de orden en el marcador de preferencias de búsqueda.
     * También se puede especificar si el orden es ascendente (A) o descendente (D).
     *
     * @param string $field El campo de orden a establecer en el marcador de preferencias.
     * @param bool $is_asc Si es verdadero, el orden es ascendente (A), de lo contrario, es descendente (D).
     * @return void
     */
	public static function setOrderField($field, $is_asc =true){

		$order_type = ($is_asc)? 'A' : 'D';

		Handler::setRequestAttr(self::$order_field, $field,true);
		Handler::setRequestAttr(self::$order_type, $order_type,true);
	}

    /**
     * Establece múltiples campos de orden en el marcador de preferencias de búsqueda.
     *
     * Este método permite establecer múltiples campos de orden en el marcador de preferencias de búsqueda.
     * Los campos de orden se pasan como un arreglo y se almacenan en el marcador.
     *
     * @param array $orders Los campos de orden a establecer en el marcador de preferencias.
     * @return void
     */
	public static function setOrderFieldMultiple($orders){
		Handler::setRequestAttr(self::$order_field, $orders,true);

	}

}
