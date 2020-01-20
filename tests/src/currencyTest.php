<?php 
require_once __DIR__ . '/../../src/config.php';

use Service\Exchange;
use Service\Rates;

class CurrencyTest extends PHPUnit_Framework_TestCase
{ 
    public function testCurrencySupport() {	
        $rates = new Rates(); 
        $testExchange =new Exchange($rates, DEFAULT_CURRENCY);
        $currency = $testExchange->getCurrencyRate('EUR');
		
        $this->assertTrue($currency > 0); 
    }
}
