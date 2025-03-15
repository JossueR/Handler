<?php
/**
*Create Date: 09/24/2012
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 102 $
*/
namespace HandlerCore\components;

use Couchbase\QueryResult;
use HandlerCore\Environment;
use HandlerCore\models\dao\BookmarkDAO;
use HandlerCore\models\dao\QueryParams;

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

	private string $invoker;
	private BookmarkDAO $dao;
	private bool $haveBookmark;
	private ?array $bookmark_data;

    /**
     *Para determinar si se cargara o no la configuración al request
     */
    private bool $useRequest = true;


    /**
     * Constructor de la clase Bookmark.
     *
     * @param $invoker string Objeto que invoca la clase Bookmark.
     */
	function __construct(string $invoker, bool $useRequest = true) {
		$this->invoker = $invoker;
		$this->dao = new BookmarkDAO();
		$this->haveBookmark = false;
        $this->useRequest = $useRequest;


	}

    public function setUseRequest(bool $useRequest): void
    {
        $this->useRequest = $useRequest;
    }



    /**
     * Obtiene los marcadores de preferencias de búsqueda relacionados con el objeto invocador y el usuario en sesión.
     *
     * @return array|null Un array con los marcadores de preferencias de búsqueda o null si no se encuentran.
     */
	public function getInvokerBookmark(): ?array
    {
		$this->dao->getMyBookmark($this->invoker);
		$b = $this->dao->get();

		//si encontró bookmark lo establece a true
		$this->haveBookmark = ($b != null);

        if($b){
            $this->bookmark_data = $b;
            return $this->bookmark_data;
        }else{
            return null;
        }

	}

    /**
     * Carga los marcadores de preferencias de búsqueda y los aplica a los filtros y campos de orden.
     *
     * @param string $filter_fields Filtros a cargar.
     */
	public function loadBookmark(string $filter_fields = ""): void
    {
		//guarda los bookmarks originales si se enviaron
		$new = $this->saveBookmark();

		//si no se enviaron nuevos bookmarks
		if(!$new){

			//si no esta cargado el bookmark
			//if(!$this->bookmark){
				//carga los bookmark
				$this->getInvokerBookmark();
			//}



			//si el invoker tiene bookmark
			if($this->haveBookmark && $this->useRequest){
                Handler::setRequestAttr(self::$search_filter, $this->bookmark_data["search"]);
                Handler::setRequestAttr(self::$filter_fields, $filter_fields);
                Handler::setRequestAttr(self::$page, $this->bookmark_data["page"]);

                $decode_order = json_decode($this->bookmark_data["order_field"]);
                if ($decode_order) {
                    Handler::setRequestAttr(self::$order_field, $decode_order);
                } else {
                    Handler::setRequestAttr(self::$order_field, $this->bookmark_data["order_field"]);
                    Handler::setRequestAttr(self::$order_type, $this->bookmark_data["order_type"]);
                }


			}
		}
	}

    function getQueryParams(): QueryParams
    {
        $params = new QueryParams();

        // Carga los parámetros desde los datos del bookmark, si están definidos
        if (!empty($this->bookmark_data)) {
            $params->setFilterString($this->bookmark_data['search'] ?? "");
            $params->setEnablePaging($this->bookmark_data['cant_by_page'] ?? Environment::$APP_DEFAULT_LIMIT_PER_PAGE, intval($this->bookmark_data['page'] ?? 0));

            if (!empty($this->bookmark_data['order_field'])) {
                $order_field = json_decode($this->bookmark_data['order_field'], true);
                if(is_array($order_field)){
                    foreach($order_field as $field => $asc){
                        $order_type_asc = (!$asc || $asc == "A");
                        $params->addOrderField($field, $order_type_asc);
                    }
                }else{

                    $order_type_asc = (!$this->bookmark_data["order_type"] || $this->bookmark_data["order_type"] == "A");
                    $params->addOrderField($this->bookmark_data['order_field'], $order_type_asc);
                }
            }
        }
        return $params;
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
    public function saveBookmark()
    {
        $need_save = false;

        // Verifica si el filtro de búsqueda está cargado en las variables de solicitud
        $search_filter = Handler::getRequestAttr(self::$search_filter);
        if (isset($search_filter)) {
            $this->bookmark_data["search"] = $search_filter;
            $need_save = true;
        } else {
            $this->bookmark_data["search"] = "";
        }

        // Verifica si la página actual está cargada en las variables de solicitud
        $page = Handler::getRequestAttr(self::$page);
        if (isset($page)) {
            $this->bookmark_data["page"] = $page;
            $this->bookmark_data["page_size"] = Handler::getRequestAttr("PAGE_SIZE") ?? Environment::$APP_DEFAULT_LIMIT_PER_PAGE;
            $need_save = true;
        } else {
            $this->bookmark_data["page"] = "";
            $this->bookmark_data["page_size"] = "";
        }


        // Verifica si el campo de orden está cargado en las variables de solicitud
        $order_field = Handler::getRequestAttr(self::$order_field);
        if (isset($order_field)) {
            if (is_array($order_field)) {
                $this->bookmark_data["order_field"] = json_encode($order_field);
            } else {
                $this->bookmark_data["order_field"] = $order_field;
            }

            $order_type = Handler::getRequestAttr(self::$order_type);
            if (isset($order_type)) {
                $this->bookmark_data["order_type"] = $order_type;
            } else {
                $this->bookmark_data["order_type"] = "";
            }

            $need_save = true;
        }

        // Si hay cambios en las preferencias, guarda los marcadores en la base de datos
        if ($need_save) {
            $this->bookmark_data["invoker"] = $this->invoker;
            $this->bookmark_data["create_user"] = Handler::getUsename();
            $this->dao->save($this->bookmark_data);

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
	public static function unloadBookmarks(): void
    {

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
	private static function clean($var): void
    {
		if(Handler::getRequestAttr($var)){
            Handler::removeRequestAttr($var);
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
	public function getSearch(): string
    {
		$text = "";


		if($this->haveBookmark){
			$text = $this->bookmark_data["search"];
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
	public function getPage(): int
    {
		$text = 0;


		if($this->haveBookmark){
			$text = intval($this->bookmark_data["page"]);
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
	public function getOrderField(): string
    {
		$text = "";


		if($this->haveBookmark){
			$text = $this->bookmark_data["order_field"];
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
	public function getOrderType(): string
    {
		$text = "";


		if($this->haveBookmark){
			$text = $this->bookmark_data["order_type"];
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
