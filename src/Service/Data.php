<?php

namespace Service;

use Exception\UndefinedException;

class Data
{
    /**
     * @param array $argv 
     */
    public function inputData(array $argv) : array
    {   
        if(isset($argv[1])) {
			$csvRowsData = array_map('str_getcsv', file($argv[1])); 
			if($this->hasEmptyField($csvRowsData)) {
				 echo 'Incorrect input data';
				 exit;
			} 
            return $csvRowsData; 
        }
    }

	public function hasEmptyField(array $csvRowsData)
    { 
        foreach ($csvRowsData as $key => $row) {
            for ($i = 0; $i <= 5; $i++) {
                // Make sure that the key exists, isn't null or an empty string
                if (!isset($row[$i]) || $row[$i] === '') { 
                    return true;
                }
            }
	    }
        return false;
    } 
}