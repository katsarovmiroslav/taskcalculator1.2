<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Persistence\Memory;
use Repository\Discount;
use Repository\Operation;
use Repository\User;
use Service\Data;
use Service\Commission;
use Service\Exchange;
use Service\Rates;

$memory = new Memory();
$data = new Data(); 
$user = new User($memory);
$operation = new Operation($memory);
$discount = new Discount($memory);

$rates = new Rates();
$exchange = new Exchange($rates, DEFAULT_CURRENCY);
$commissionCalculator = new Commission(
    $operation,
    $discount,
    $exchange
);

$csvRowsData = $data->inputData($argv); 

// Set up and store operation and user entities.
if(is_array($csvRowsData)) {
    foreach ($csvRowsData as $index => $csvRow) {
        $userData = $user->searchOrCreate((int)$csvRow[1], $csvRow[2]);
        $createOperation = $operation->create(
            $index + 1,
            $csvRow[0],
            $csvRow[3],
            $csvRow[4],
            $csvRow[5],
            $userData,
            $discount
        );
    }
	$operations = $operation->getAll();
}

// Calculate commission for operations.
if(is_array($operations)) {
    foreach ($operations as $operation) {
        $commissionCalculator->calculate($operation);

        fwrite(STDOUT, $commissionCalculator->getFormattedCommission($operation) . PHP_EOL);
    }
}
