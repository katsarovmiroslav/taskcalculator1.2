<?php 
require_once __DIR__ . '/../../src/config.php';

use Service\Data; 

class DataTest extends PHPUnit_Framework_TestCase
{ 
    public function testDataInput() {	
        $argv[0] = '';
        $argv[1] = 'tests/data/input.csv';
		
        $data = new Data();   
        $arrayData = $data->inputData($argv);  
        $this->assertTrue(13 == count($arrayData)); 
    }
}
