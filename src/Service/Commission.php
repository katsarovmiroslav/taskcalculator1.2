<?php

namespace Service;

use Entity\AbstractOperation;
use Entity\DiscountEntity;
use Repository\Discount;
use Repository\Operation;
use Service\Exchange;

class Commission
{ 
    /**
     * @var Operation
     */
    protected $operation;

    /**
     * @var Discount
     */
    protected $discount;

    /**
     * @var Exchange
     */
    protected $exchange;

    /**
     * @var int
     */
    private $commission;

    /**
     * @param Operation $operation
     * @param Discount $discount
     * @param Exchange $exchange
     */
    public function __construct(
        Operation $operation,
        Discount $discount,
        Exchange $exchange
    ) {
        $this->discount = $discount;
        $this->operation = $operation;
        $this->exchange = $exchange;
    }

    /**
     * @param AbstractOperation $operation
     */
    public function calculate(AbstractOperation $operation) : void
    {
        $this->cashIn($operation);
        $this->cashOut($operation);
    }

    /**
     * @param AbstractOperation $operation
     * @return string
     */
    public function getFormattedCommission(AbstractOperation $operation) : string
    { 
		$commission = ceil(number_format((float)$this->commission, 2, '.', '')) / 100;
		if($commission >= 1000) {
            return number_format(
            ceil($commission),
            $operation->getAmountPrecise(),
            '.',
            ''
		    );
		} else { 
            return number_format(
            $commission,
            $operation->getAmountPrecise(),
            '.',
             ''
		    );
		}
    }

    /**
     * @param AbstractOperation $operation
     */
    protected function cashIn(AbstractOperation $operation) : void
    {
        if (!$operation->isCashInOperation()) {
            return;
        }

        $commission = $operation->getAmount() / 100 * OPERATION_CASH_IN_COMMISSION_PERCENTAGE;
        $commissionInEur = $this->exchange->calculateRate($commission, DEFAULT_CURRENCY);

        if ($commissionInEur > MAXIMUM_CASH_IN_COMMISSION_AMOUNT) {
            $commission = $this->exchange->calculateRate(
                MAXIMUM_CASH_IN_COMMISSION_AMOUNT,
                $operation->getCurrency()
            );
        }

        $this->commission = $commission;
    }

    /**
     * @param AbstractOperation $operation
     */
    protected function cashOut(AbstractOperation $operation) : void
    {
        if (!$operation->isCashOutOperation()) {
            return;
        }

        $this->cashOutLegalUser($operation);
        $this->cashOutNaturalUser($operation);
    }

    /**
     * @param AbstractOperation $operation
     */
    protected function cashOutLegalUser(AbstractOperation $operation) : void
    {
        if (!($operation->getUser())->isLegalUser()) {
            return;
        }

        $commission = $operation->getAmount() / 100 * OPERATION_CASH_OUT_COMMISSION_PERCENTAGE;
        $commissionInEur = $this->exchange->calculateRate($commission, DEFAULT_CURRENCY);

        if ($commissionInEur <= MINIMUM_CASH_OUT_COMMISSION_AMOUNT) {
            $commission = $this->exchange->calculateRate(
                MINIMUM_CASH_OUT_COMMISSION_AMOUNT,
                $operation->getCurrency()
            );
        }

        $this->commission = $commission;
    }

    /**
     * @param AbstractOperation $operation
     */
    protected function cashOutNaturalUser(AbstractOperation $operation) : void
    {
        if (!($operation->getUser())->isNaturalUser()) {
            return;
        }

        $weekOperationsCounter = $this->operation->getWeekOperationsCounter(
            $operation->getDate(),
            $operation->getUser()->getId(),
            $operation->getId()
        );

        $this->checkDiscount($weekOperationsCounter, $operation);
        $this->checkRegularCommission($weekOperationsCounter, $operation);
    }

    /**
     * Improved ceil() alternative with precision support.
     *
     * @param $value
     * @param int $precision
     * @return float
     */
    private function ceiling($value, int $precision = 0) : float
    { 
        return ceil($value * pow(10, $precision)) / pow(10, $precision);
    }

    /**
     * @param int $weekOperationsCounter
     * @param AbstractOperation $operation
     */
    private function checkDiscount(int $weekOperationsCounter, AbstractOperation $operation) : void
    {
        if ($weekOperationsCounter > WEEKLY_OPERATION_LIMIT_FOR_DISCOUNT) {
            return;
        }

        $discount = $this->discount->search(
            $operation->getUser()->getId(),
            $operation->getDate()
        );

        $this->checkUserHasDiscount($discount, $operation);
        $this->checkUserHasNotDiscount($discount, $operation);
    }

    /**
     * @param int $weekOperationsCounter
     * @param AbstractOperation $operation
     */
    private function checkRegularCommission(int $weekOperationsCounter, AbstractOperation $operation) : void
    {
        if ($weekOperationsCounter <= WEEKLY_OPERATION_LIMIT_FOR_DISCOUNT) {
            return;
        }

        $commission = $this->exchange->calculateRate(
            $operation->getAmount() / 100,
            $operation->getCurrency()
        );

        $this->commission = $commission * OPERATION_CASH_OUT_COMMISSION_PERCENTAGE;
    }

    /**
     * @param DiscountEntity $discount
     * @param AbstractOperation $operation
     */
    private function checkUserHasDiscount(DiscountEntity $discount, AbstractOperation $operation) : void
    {
        if (is_null($discount)) {
            return;
        }
		 
        $convertedAmountFloat = $this->exchange->calculateRate(
            $operation->getAmount() / 100,
            DEFAULT_CURRENCY,
            $operation->getCurrency()
        );
		
        $convertedAmountInt = $this->ceiling($convertedAmountFloat, 2) * 100;
        $unusedAmount = $discount->useDiscount($convertedAmountInt);
 
        if ($unusedAmount === 0) {
            $this->commission = 0;
        } else {
			 
            $commission = $this->exchange->calculateRate(
                $unusedAmount / 100,
                $operation->getCurrency(),
                DEFAULT_CURRENCY
            );
			
            $this->commission = $commission * OPERATION_CASH_OUT_COMMISSION_PERCENTAGE;
        }
    }

    /**
     * @param DiscountEntity $discount
     * @param AbstractOperation $operation
     */
    private function checkUserHasNotDiscount(DiscountEntity $discount, AbstractOperation $operation) : void
    {
        if (!is_null($discount)) {
            return;
        }

        $this->commission = $this->exchange->calculateRate(
            $operation->getAmount() / 100,
            $operation->getCurrency(),
            DEFAULT_CURRENCY
        ) * OPERATION_CASH_OUT_COMMISSION_PERCENTAGE;
    }
}
