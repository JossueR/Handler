<?php
namespace HandlerCore\components;

use HandlerCore\Environment;
use HandlerCore\models\dao\AbstractBaseDAO;

/**
 *
 */
class DataViewer extends Handler implements ShowableInterface{
    private $schema;
    private $title;
    private $dao;
    private $name;
    private $field_arr;
    public  $html = array();

    //arreglo con los nombres que se mostraran
    public  $legent=array();

    public  $fields=null;
    public $display_box;
    public $panel_class;
    private $row_data;

    /**
     * @param string function ($field, $value, $row)
     * @return string value
     */
    public $callbackShow;

    private static string $generalSchema = "";

    /**
     * @var ButtonMaker|null
     */
    private $buttons;

    /**
     * @param ButtonMaker $buttons
     */
    public function setButtons(ButtonMaker $buttons): void
    {
        $this->buttons = $buttons;
    }



    function __construct(AbstractBaseDAO $dao=null, $schema = null) {

        $this->display_box = true;

        if($dao){
            $this->dao = $dao;
            $this->row_data = $dao->get();
        }else{
            $this->row_data = array();
        }


        if($schema){
            $this->schema = $schema;
        }else if(self::$generalSchema != ""){
            $this->schema = self::$generalSchema;
        }else{
            $this->usePrivatePathInView=false;
            $this->schema = Environment::getPath() .  "/views/common/viewer.php";
        }

        $this->panel_class = "card-outline card-primary";
        $this->title=false;
    }

    /**
     * @param string $generalSchema
     */
    public static function setGeneralSchema(string $generalSchema): void
    {
        self::$generalSchema = $generalSchema;
    }

    function setTitle($title){
        $this->title = $title;
    }

    function setArrayData($row){
        $this->row_data = $row;
    }

    function OnlyShowContent()
    {
        $this->display_box = false;
    }



    function show(){
        //si no se definieron los datos a mostrar, entonces muestra todos
        if($this->fields){
            $this->field_arr = explode(",", $this->fields);
            if(count($this->field_arr) < 1){
                $this->field_arr = $this->dao->getFields();
            }
        }else{
            if($this->dao){
                $this->field_arr = $this->dao->getFields();
            }else{
                $this->field_arr = array_keys($this->row_data);
            }

        }

        //para cada dato a mostrar, obtiene el


        $this->display($this->schema, get_object_vars($this));
    }



}

