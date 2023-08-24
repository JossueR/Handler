<?php
namespace HandlerCore\components;

/**
 *Create Date: 09/24/2012
 * \*Author: Jossue O. Rodriguez C.   $LastChangedRevision: 149 $
 */
class ChartGenerator
{
    private $data;
    private $x_axis;
    private $y_axis;
    private $xSerieName;
    private $ySerieName;
    private $yName;
    private $type;
    private $name;
    private static $LAST_UNIC;

    public $dest;
    private $settings;

    const TYPE_BARS = "bars";
    const TYPE_PIE = "pie";
    const TYPE_AREA = "Area";

    const TYPE_DONUT = "Donut";
    const TYPE_LINE = "Line";
    const TYPE_BAR = "Bar";


    function __construct($data)
    {
        $this->data = $data;
        $this->y_axis = array();
        $this->xSerieName = "";
        $this->ySerieName = "";
        $this->yName = array();
        $this->settings = array();
        $this->type = self::TYPE_BARS;
    }

    public function getUnicName()
    {

        do {
            $sid = microtime(true);
            $sid = str_replace(".", "", $sid);
        } while ($sid == self::$LAST_UNIC);


        self::$LAST_UNIC = $sid;

        return $sid;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function xSerieName($name)
    {
        $this->xSerieName = $name;
    }


    public function ySerieName($name)
    {
        $this->ySerieName = $name;
    }

    public function setXAxis($axis, $xSerieName = "")
    {
        $this->x_axis = $axis;
        $this->xSerieName = $xSerieName;
    }

    public function setYAxis($axis, $yName = "")
    {
        $this->y_axis[] = $axis;
        $this->yName[] = $yName;
    }


    public function generateJSON($autoEcho = true)
    {
        if ($this->type == self::TYPE_BARS) {
            $d = $this->genBarsData();
        } else {
            $d = $this->genPieData();
        }


        $d['destStyle'] = array("class" => "flot-chart-content");


        $json = json_encode($d);
        if ($autoEcho) {
            $this->jsonHeaders();
            echo $json;
        }

        return $json;
    }

    public function settingsMorris($settings)
    {
        $this->settings = $settings;
    }


    public function generateMorrisJSON($autoEcho = true)
    {

        $raw = $this->settings;
        $raw["element"] = $this->dest;

        $raw["data"] = $this->data->fetchAll();

        $raw["xkey"] = $this->x_axis;

        $raw["ykeys"] = $this->y_axis;

        $raw["labels"] = $this->yName;


        $json = json_encode($raw);
        if ($autoEcho) {
            $this->jsonHeaders();
            echo $json;
        }

        return $json;
    }

    private function genBarsData()
    {
        $serie = 1;
        $d = array();
        foreach ($this->y_axis as $y_axis) {
            $i = 0;
            $point = array();
            $d['series']["d$serie"]["label"] = $this->yName[$serie - 1];

            $d['series']["d$serie"][$this->type] = array("show" => true);

            foreach ($this->data as $row) {
                $point = array();
                $point[] = $i;
                $point[] = floatval(str_replace(",", "", $row[$y_axis]));


                $d['series']["d$serie"]["data"][] = $point;
                $i++;
            }

            $serie++;
        }
        $d['tick_x'] = $this->generateXLabel();
        $d['xSerieName'] = $this->xSerieName;
        $d['ySerieName'] = $this->ySerieName;
        return $d;
    }

    private function genPieData($colors = null)
    {
        $serie = 1;
        $d = array();

        $i = 0;
        while ($row = $this->data->get()) {

            $d[] = array(
                "value" => $row[$this->y_axis[0]],
                "label" => $row[$this->x_axis],
                "color" => (isset($colors) && isset($colors[$i])) ? $colors[$i] : self::random_color()

            );


            $i++;
        }

        return json_encode($d);
    }


    private function generateXLabel()
    {


        $i = 0;
        $tick = array();

        foreach ($this->data as $row) {
            $point = array();
            $point[] = $i;
            $point[] = $row[$this->x_axis];

            $tick[] = $point;
            $i++;
        }

        return $tick;

    }

    private function jsonHeaders()
    {
        //header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        //header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Content-type: application/json");
    }

    public function generateMorrisChart($dest, $autoShow = true)
    {
        $this->dest = $dest;
        $json = $this->generateMorrisJSON(false);
        $script = "";

        $script .= "<script>";

        $script .= " Morris." . $this->type . "($json); ";

        $script .= "</script>";

        if ($autoShow) {
            echo $script;
        }

        return $script;
    }

    public function generateCHARTJSPieChart($dest, $options = null, $colors = null)
    {
        $this->dest = $dest;
        #var myPieChart = new Chart(ctx,);
        $json = json_encode(array(
                'segmentShowStroke' => 'true',
                'segmentStrokeColor' => "#fff",
                'segmentStrokeWidth' => '2',
                'animationSteps' => '100',
                'animationEasing' => "easeOutBounce",
                'animateRotate' => 'true',
                'animateScale' => 'false',
                'responsive' => 'true',
                'maintainAspectRatio' => 'true'
            )
        );

        if (!$this->name) {
            $this->name = $this->getUnicName();
        }


        $script = "";
        $script .= "<script>";
        $script .= ' var chart_' . $this->name . ' = new Chart( $("#' . $this->dest . '").get(0).getContext("2d")); ';
        $script .= ' chart_' . $this->name . '.Doughnut(' . $this->genPieData($colors) . ', ' . $json . ')';
        $script .= "</script>";

        return $script;
    }

    static function random_color_part()
    {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }

    static function random_color($HashtagPrefix = true)
    {
        $hash = ($HashtagPrefix) ? "#" : "";

        return $hash . self::random_color_part() . self::random_color_part() . self::random_color_part();
    }

}

