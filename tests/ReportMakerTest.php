<?php

namespace HandlerCore\Tests;

use HandlerCore\components\ReporterMaker;


class ReportMakerTest extends BaseTestCase
{
    public function testOptionalParams(){
        $rep = new ReporterMaker("R0010");
        $dao = $rep->getDAO(true);
        $this->assertIsObject($dao);
    }
}