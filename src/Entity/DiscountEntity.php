<?php

namespace Entity;

class DiscountEntity implements EntityInterface
{
    /**
     * @var AbstractUser
     */
    protected $user;

    /**
     * @var \DateTime
     */
    protected $periodStartDate;

    /**
     * @var \DateTime
     */
    protected $periodEndDate;

    /**
     * @var int
     */
    protected $amount;

    public function __construct(AbstractUser $user, \DateTime $periodStart, \DateTime $periodEnd, int $amount)
    {
        $this->user = $user;
        $this->periodStartDate = $periodStart;
        $this->periodEndDate = $periodEnd;
        $this->amount = $amount;
    }

    /**
     * @return AbstractUser
     */
    public function getUser(): AbstractUser
    {
        return $this->user;
    }

    /**
     * @param \DateTime $dateTime
     * @return bool
     */
    public function isInPeriod(\DateTime $dateTime) : bool
    {
        return $dateTime >= $this->periodStartDate && $dateTime <= $this->periodEndDate;
    }

    /**
     * @param int $amount
     * @return int
     */
    public function useDiscount(int $amount) : int
    {
        // If there is nothing to use, return original amount.
        if ($this->amount === 0) {
            return $amount;
        }

        // If discount is bigger, use all original amount and return 0 as unused amount.
        if ($this->amount >= $amount) {
            $this->amount -= $amount;

            return 0;
        }

        // Otherwise use discount partly and return what is left.
        $amount -= $this->amount;
        $this->amount = 0;

        return $amount;
    }
}
