<?php
/**
*Create Date: 09/24/2012
\*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 102 $
*/
loadClass(PATH_FRAMEWORK . "models/dao/BookmarkDAO.php");
	
class Bookmark {
	static public $search_filter = "FILTER";
	static public $filter_fields = "FILTER_KEYS";
	static public $order_field = "FIELD";
	static public $order_type = "ASC";
	static public $page = "PAGE";
		
	private $invoker;
	private $dao;
	private $haveBookmark;
	private $bookmark;
	
	
	function __construct($invoker) {
		$this->invoker = $invoker;
		$this->dao = new BookmarkDAO();
		$this->haveBookmark = false;
		
		
	}
	
	/***
	 * Obtiene los bookmarks del invoquer relacionados al usuario en session
	 */
	public function getInvokerBookmark(){
		$this->dao->getMyBookmark($this->invoker);
		$b = $this->dao->get();

		//si encontro bookmark lo establece a true
		$this->haveBookmark = ($b != null);
		$this->bookmark = $b;
		return $b;
	}
	
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
	
	public function saveBookmark(){
		$need_save = false;
		
		//si no esta cargado el filtro de busqueda
		if(isset($_POST[self::$search_filter])){
			$this->bookmark["search"] = $_POST[self::$search_filter];
			$need_save = true;
		}else{
			$this->bookmark["search"] = "";
		}
		
		//si esta cargado la pagina actual
		if(isset($_POST[self::$page])){
			$this->bookmark["page"] = $_POST[self::$page];
			$need_save = true;
		}else{
			$this->bookmark["page"] = "";
		}
		
		//si esta cargado el campo de orden
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
			
		if($need_save){
			$this->bookmark["invoker"] = $this->invoker;
			$this->bookmark["create_user"] = $_SESSION['USER_NAME'];
			$this->dao->save($this->bookmark);
			
			$this->haveBookmark = true;
		}
		
		return $need_save;
	}
	
	public static function unloadBookmarks(){
	
		self::clean(self::$search_filter);
		self::clean(self::$filter_fields);
		self::clean(self::$page);
		self::clean(self::$order_field);
		self::clean(self::$order_type);
	}
	
	private static function clean($var){
		if(isset($_POST[$var] )){
			unset($_POST[$var] );
		}
	}
	
	public function getSearch(){
		$text = "";
		
		
		if($this->haveBookmark){
			$text = $this->bookmark["search"];
		}
		
		return $text;
	}
	
	public function getPage(){
		$text = 0;
		
		
		if($this->haveBookmark){
			$text = intval($this->bookmark["page"]);
		}
		
		return $text;
	}
	
	public function getOrderField(){
		$text = "";

		
		if($this->haveBookmark){
			$text = $this->bookmark["order_field"];
		}
		
		return $text;
	}
	
	public function getOrderType(){
		$text = "";

		
		if($this->haveBookmark){
			$text = $this->bookmark["order_type"];
		}
		
		return $text;
	}
	
	public static function setFilterFields($fields){
			
		if(is_array($fields)){
			
			$fields = implode(",", $fields);
		}
		
		Handler::setRequestAttr(self::$filter_fields, $fields,true);

	}
	
	public static function setOrderField($field, $is_asc =true){
			
		$order_type = ($is_asc)? 'A' : 'D';
		
		Handler::setRequestAttr(self::$order_field, $field,true);
		Handler::setRequestAttr(self::$order_type, $order_type,true);
	}
	
	public static function setOrderFieldMultiple($orders){
			
		
		
		Handler::setRequestAttr(self::$order_field, $orders,true);

	}

}
?>
