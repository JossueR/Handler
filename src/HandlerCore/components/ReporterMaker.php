<?php

namespace HandlerCore\components;





use HandlerCore\models\dao\AbstractBaseDAO;
use HandlerCore\models\dao\ReportDAO;
use HandlerCore\models\dao\ReportFilterDAO;
use HandlerCore\models\dao\SubReportDAO;
use HandlerCore\models\SimpleDAO;
use function HandlerCore\showMessage;

/**
 *
 */
class ReporterMaker
{
    private $report_id;
    private $subreport_id;
    private $is_subreport;
    private $matrix;
    private $root;
    private $definition;
    private $defaults;
    private $operators = array(
        "LIKE" => "like",
        "NLIKE" => "not like",
        "EQ" => "=",
        "NEQ" => "<>",
        "EMPTY" => " is null",
        "NEMPTY" => "is not null",
        "GT" => ">",
        "GET" => ">=",
        "LT" => "<",
        "LET" => "<=",
        "SQL" => ""
    );

    const FILTER_TAG = "{FILTERS_EXPLICIT}";

    const TYPE_GROUP = "GROUP";
    const TYPE_FILTER = "FILTER";

    function __construct($report_id, $subreport = false)
    {

        $this->is_subreport = $subreport;

        if ($this->is_subreport) {
            $repDao = new  SubReportDAO();
            $repDao->getById(array("id" => $this->report_id));


            $report_data = $repDao->get();


            $this->subreport_id = $report_id;
            $this->report_id = $report_data["report_id"];
        } else {
            $this->report_id = $report_id;
        }

        $repDao = new  ReportDAO();
        $repDao->getById(array("id" => $this->report_id));
        $repDao->escaoeHTML_OFF();
        $report_data = $repDao->get();
        $repDao->escaoeHTML_ON();
        $this->definition = $report_data["definition"];
    }

    private function loadMatrix()
    {
        $this->defaults = array();
        $filterDao = new  ReportFilterDAO();

        $filterDao->escaoeHTML_OFF();
        if ($this->is_subreport) {
            $filterDao->getBySubReport($this->subreport_id);
        } else {
            $filterDao->getByReport($this->report_id);
        }
        $filterDao->escaoeHTML_ON();

        $this->matrix = array();
        while ($filter = $filterDao->get()) {
            $this->matrix[$filter["id"]] = $filter;

            if ($filter["root"] == SimpleDAO::REG_ACTIVO_Y) {
                $this->root = $filter["id"];
            }

            $this->defaults["F_" . $filter["id"]] = $filter["value"];
            $this->defaults["OP_" . $filter["id"]] = $filter["op"];
            $this->defaults["J_" . $filter["id"]] = $filter["join"];

        }


    }

    private function builtFilters($from, $html = false)
    {
        $raw = "";

        #si es grupo imprime apertura
        if ($this->matrix[$from]["type"] == self::TYPE_GROUP) {
            if ($html) {
                $raw .= "<blockquote>";
            } else {
                $raw .= " ( ";
            }
        }

        #si no es grupo
        if ($this->matrix[$from]["type"] == self::TYPE_FILTER) {
            #imprime campo op valor

            if ($html) {
                $raw .= "<div class='form-group input-group' >";
                $raw .= "<span class='input-group-addon' >";
                $raw .= ($this->matrix[$from]["label"]) ? $this->matrix[$from]["label"] : $this->matrix[$from]["field"];
                $raw .= "</span>";

                $raw .= $this->htmlOperators($from);


                $raw .= "<input class='form-control' type='text' id='F_" . $this->matrix[$from]["id"] . "' name='F_" . $this->matrix[$from]["id"] . "' value='" . $this->matrix[$from]["value"] . "' />";


            } else {
                $raw .= " " . $this->matrix[$from]["field"] . " {{OP_" . $this->matrix[$from]["id"] . "}} {F_" . $this->matrix[$from]["id"] . "} ";

            }

            #imprime conjuncion
            if ($html) {
                $raw .= $this->htmlConjuntion($from);

                $raw .= "</div>";
            } else {
                $raw .= " {J_" . $this->matrix[$from]["id"] . "} ";
            }

        } else {
            #si es grupo

            #si hijo no es vacio
            if ($this->matrix[$from]["child"]) {
                #construye interno
                $raw .= $this->builtFilters($this->matrix[$from]["child"], $html);
            }

            #si es grupo imprime cierre
            if ($html) {
                $raw .= "</blockquote>";
            } else {
                $raw .= " ) ";
            }
            #si existe otro hijo
            if ($this->matrix[$from]["sibling"]) {
                #imprime conjuncion
                if ($html) {
                    $raw .= $this->htmlConjuntion($from);
                } else {
                    $raw .= " {J_" . $this->matrix[$from]["id"] . "} ";
                }
            }
        }


        #si siguiente no es vacio
        if ($this->matrix[$from]["sibling"]) {
            #construye siguiente
            $raw .= $this->builtFilters($this->matrix[$from]["sibling"], $html);
        }

        return $raw;
    }

    private function htmlConjuntion($from)
    {
        $raw = "";

        $raw .= "<span>";
        $raw .= "<select class='form-control' id='J_" . $this->matrix[$from]["id"] . "' name='J_" . $this->matrix[$from]["id"] . "' >";
        $conj = array("", "AND", "OR");
        foreach ($conj as $value) {
            $def = ($this->matrix[$from]["join"] == $value) ? "selected" : "";
            $raw .= "<option value='$value' $def>$value</option>";
        }
        $raw .= "</select>";
        $raw .= "</span>";

        return $raw;
    }

    private function htmlOperators($from)
    {
        $raw = "";

        $raw .= "<span>";
        $raw .= "<select class='form-control' id='OP_" . $this->matrix[$from]["id"] . "' name='OP_" . $this->matrix[$from]["id"] . "' >";
        $conj = array(
            "LIKE" => showMessage("contiene"),
            "NLIKE" => showMessage("no_contiene"),
            "EQ" => showMessage("equals"),
            "NEQ" => showMessage("no_equals"),
            "EMPTY" => showMessage("empty"),
            "NEMPTY" => showMessage("no_empty"),
            "GT" => showMessage("gtetter_than"),
            "GET" => showMessage("gtetter_equals_than"),
            "LT" => showMessage("less_than"),
            "LET" => showMessage("less_equals_than"),
            "SQL" => showMessage("SQL"),
        );
        foreach ($conj as $key => $value) {
            $def = ($this->matrix[$from]["op"] == $key) ? "selected" : "";
            $raw .= "<option value='$key' $def>$value</option>";
        }
        $raw .= "</select>";
        $raw .= "</span>";

        return $raw;
    }

    private function getDataArray()
    {
        $search_array = $_POST;

        $data = array();

        foreach ($this->defaults as $key => $value) {
            if (isset($search_array[$key]) && $search_array[$key] !== "") {
                $data[$key] = $search_array[$key];
            } else {
                $data[$key] = $value;
            }
        }

        foreach ($this->matrix as $key => $value) {

            switch ($data["OP_" . $key]) {
                case "EMPTY":
                case "NEMPTY":
                    $data["F_" . $key] = "";
                    break;

                case "SQL":
                    $data["F_" . $key] = $data["F_" . $key];
                    break;

                default:
                    $data["F_" . $key] = "'" . SimpleDAO::escape($data["F_" . $key]) . "'";
            }


        }

        return $data;

    }

    public function getSQL()
    {
        #carga matriz definicion de filtros
        $this->loadMatrix();

        $filtros = "";

        #si hay filtros que cargar

        if (count($this->matrix) > 0) {

            #construye los filtros en sql
            $filtros = $this->builtFilters($this->root);

            #incrustra los parametros enviados por post
            $filtros = $this->embedParams($filtros, $this->getDataArray());

            #incrustra los operadores al query
            $filtros = $this->embedParams($filtros, $this->operators);


        }

        #incluye los filtros al query
        $sql = str_replace(self::FILTER_TAG, $filtros, $this->definition);

        return $sql;
    }

    public function getHTML()
    {
        $this->loadMatrix();

        $filtros = $this->builtFilters($this->root, true);


        return $filtros;
    }

    static public function embedParams($tag, $data_array)
    {

        $pattern = "/\{([\w]+)\}/";

        preg_match_all($pattern, $tag, $matches, PREG_OFFSET_CAPTURE);

        for ($i = 0; $i < count($matches[0]); $i++) {
            $foundKey = $matches[1][$i][0];

            if (!isset($data_array[$foundKey]) || $data_array[$foundKey] === null) {
                $replaceWith = "{" . $foundKey . "}";
            } else {
                $replaceWith = $data_array[$foundKey];
            }

            $tag = str_replace("{" . $foundKey . "}", $replaceWith, $tag);
        }

        return $tag;
    }

    /**
     * @param $sql
     * @param bool $autoconfigurable
     * @param bool $autoExec
     * @return AbstractBaseDAO
     */
    public static function getDAOFromSQL($sql, $autoconfigurable = false, $autoExec= true): AbstractBaseDAO
    {
        $dao = new AbstractBaseDAO("","","","","");



        if($autoconfigurable){
            $dao->autoconfigurable=SimpleDAO::IS_AUTOCONFIGURABLE;
        }

        if($autoExec){
            try{
                $dao->find($sql);
            }catch (Exception $e){

            }

        }



        return $dao;
    }

    public function getDAO($autoconfigurable = true, $autoExec = true)
    {

        $dao = new AbstractBaseDAO("", "", "", "", "");

        if ($autoconfigurable) {
            $dao->autoconfigurable = SimpleDAO::IS_AUTOCONFIGURABLE;
        }

        if ($autoExec) {
            $sql = $this->getSQL();
            $dao->find($sql);
        }


        return $dao;
    }

    public function getIsSubReport()
    {
        return $this->is_subreport;
    }

    public function getID()
    {
        return ($this->is_subreport) ? $this->subreport_id : $this->report_id;
    }
}

