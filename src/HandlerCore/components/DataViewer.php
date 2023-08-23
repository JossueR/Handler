<?php
loadClass(PATH_FRAMEWORK . "components/Handler.php");
loadClass(PATH_FRAMEWORK . "components/ButtonMaker.php");

/**
 *
 */
class DataViewer extends Handler {
    private $squema;
    private $title;
    private $dao;
    private $name;
    private $field_arr;
    public  $html = array();
    //arreglo con los nombre que se mostraran
    public  $legent=array();

    public  $fields=null;
    public $display_box;
    public $panel_class;
    private $row_data;

    /**
     * @param function ($field, $value, $row)
     * @return value
     */
    public $callbackShow;

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



    function __construct(AbstractBaseDAO $dao=null, $squema = null) {

        $this->display_box = true;

        if($dao){
            $this->dao = $dao;
            $this->row_data = $dao->get();
        }else{
            $this->row_data = array();
        }


        if($squema){
            $this->squema = $squema;
        }else{
            $this->squema = PATH_FRAMEWORK . "views/common/viewer.php";
        }

        $this->panel_class = "card-outline card-primary";
        $this->title=false;
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


        $this->display($this->squema, get_object_vars($this));
    }



}

