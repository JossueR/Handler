<?php

namespace HandlerCore\Tests;

use HandlerCore\components\FormMaker;
use HandlerCore\components\ReporterMaker;


class ReportMakerTest extends BaseTestCase
{
    public function testGetDataArray(){
        $rep = new ReporterMaker("R0010");
        $rep->setDataArray([
            "params" => [
                "startDate" => "2025-01-01",
                "endDate" => "2025-01-31",
            ],
            "status_name" => "DELIVERED"
        ]);
        $data = $rep->getDataArray();
        print_r($data);
        $this->assertIsArray($data);
    }



    public function testOptionalParams(){
        $rep = new ReporterMaker("R0009");
        $dao = $rep->getDAO(true);
        echo $dao->getSumary()->sql;
        $this->assertIsObject($dao);
    }

    public function testOptionalWithDataParams(){
        $rep = new ReporterMaker("R0010");
        $rep->setDataArray([
            "params" => [
                "startDate" => "2025-01-01",
                "endDate" => "2025-01-31",
                "status_name" => ["DELIVERED","ENTREGADO"],
                "shipping_id" => ["g1"],
                "rate_type" => "t1",
            ],

        ]);
        $dao = $rep->getDAO(true);
        echo $dao->getSumary()->sql;
        $this->assertIsObject($dao);
    }

    public function testSqlBuilderChained(){
        $rep = new ReporterMaker("R0010");
        $rep->setDataArray([
            "params" => [
                "startDate" => "2025-01-01",
                "endDate" => "2025-01-31",


            ],

        ]);
        $sql = $rep->getSQL();
        echo $sql;
        $this->assertIsSTRING($sql);
    }

    public function testFilterForm(){
        $id = "R0009";
        $rMaker = new ReporterMaker($id);

        $form = new FormMaker(null,null,"");

        $form->name = "filtersFrm" ;
        $form->action = "Reporter";
        $form->actionDO = "show";
        $form->resultID = "tabla_" . "d";

        $form->setVar("report_id", $id);



        $form = $rMaker->getFormFilter($form, []);
        var_dump($form);
        $this->assertIsObject($form);
    }
}