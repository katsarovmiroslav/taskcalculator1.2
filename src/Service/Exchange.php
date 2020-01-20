<?php

namespace Service;

use Exception\UndefinedException;

class Exchange
{
    /**
     * @var Rates
     */
    private $rates;

    /**
     * @var string
     */
    private $defaultCurrency;

    /**
     * @param Rates $rates
     * @param string $defaultCurrency
     */
    public function __construct(Rates $rates, string $defaultCurrency)
    {
        $this->rates = $rates;
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @param string $currency
     * @throws UndefinedException
     * @return float
     */
    public function getCurrencyRate(string $currency) : float
    {
        $rates = $this->rates->getRates();

        if (isset($rates[$currency])) {
            return $rates[$currency];
        }  else {
            echo sprintf('Currency "%s" is not found.', $currency); 
            exit;
			/*
			throw new UndefinedException(
				sprintf('Currency "%s" is not found.', $currency)
			); 
			*/
		}  
    }

    public function calculateRate($amount, $toCurrency, $fromCurrency = null) : float
    {
        if (!isset($fromCurrency)) {
            $fromCurrency = $this->defaultCurrency;
        }

        if ($this->rates->getBaseCurrency() !== $fromCurrency) {
            $amount = $amount / $this->getCurrencyRate($fromCurrency);
        }

        if ($toCurrency === $this->rates->getBaseCurrency()) {
            return $amount;
        }

        return $amount * $this->getCurrencyRate($toCurrency);
    }
}
