<?php
namespace HandlerCore\components;

use HandlerCore\Environment;
use HandlerCore\models\dao\AbstractBaseDAO;

/**
 * La clase DataViewer extiende la clase Handler y cumple con la interfaz ShowableInterface.
 * Esta clase se utiliza para generar un bloque HTML que muestra información en forma de tabla,
 * ya sea a partir de un objeto AbstractBaseDAO o un array.
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
     * Closure de visualización de valor para DataViewer.
     *
     * Esta propiedad permite configurar un cierre (closure) que se invoca por cada valor presentado
     * en la tabla generada por DataViewer. El cierre recibe tres parámetros: el campo (nombre del campo),
     * el valor actual y la fila actual. Puede utilizarse para modificar la visualización del valor antes
     * de mostrarlo en la tabla.
     *
     * @var Closure|null $callbackShow Un cierre que modifica la visualización de los valores.
     */
    public $callbackShow;

    private static string $generalSchema = "";

    /**
     * @var ButtonMaker|null
     */
    private $buttons;

    /**
     * Asigna un grupo de botones al DataViewer.
     *
     * Este método permite establecer un grupo de botones creado una instancia de la clase
     * ButtonMaker al DataViewer actual. Esto permite combinar y gestionar conjuntos de botones
     * en una sola entidad para su posterior visualización.
     *
     * @param ButtonMaker $buttons El grupo de botones que se va a asignar.
     * @return void
     */
    public function setButtons(ButtonMaker $buttons): void
    {
        $this->buttons = $buttons;
    }



    /**
     * Constructor de la clase DataViewer.
     *
     * @param AbstractBaseDAO|null $dao El objeto AbstractBaseDAO que proporciona los datos para la tabla.
     * @param string|null $schema El esquema que se utilizará para el bloque DataViewer.
     * @return void
     */
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

    /**
     * Establece el título del bloque DataViewer.
     *
     * @param string $title El título que se desea asignar al bloque.
     * @return void
     */
    function setTitle($title){
        $this->title = $title;
    }

    /**
     * Establece los datos del bloque DataViewer utilizando un array.
     *
     * Este método permite asignar un array de datos al bloque DataViewer para su posterior visualización
     * en forma de tabla. Los datos proporcionados se mostrarán en filas de la tabla, donde cada fila
     * representa un conjunto de valores.
     *
     * @param array $row El array de datos que se desea establecer.
     * @return void
     */
    function setArrayData($row){
        $this->row_data = $row;
    }

    /**
     * Habilita la opción para mostrar únicamente el contenido específico del bloque.
     *
     * Cuando esta opción está habilitada, se oculta el cuadro que rodea al contenido del bloque.
     * Esto permite mostrar el contenido sin bordes ni decoraciones.
     *
     * @return void
     */
    function OnlyShowContent()
    {
        $this->display_box = false;
    }



    /**
     * Muestra la tabla generada por DataViewer.
     *
     * Este método implementa el contrato de la interfaz ShowableInterface. Genera y muestra
     * una tabla HTML que presenta los nombres y valores de los datos proporcionados.
     *
     * @return void
     */
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

