<?php

namespace Repository;

use Entity\AbstractUser;
use Entity\DiscountEntity;
use Persistence\PersistenceInterface;

class Discount
{
    /**
     * @var PersistenceInterface
     */
    private $persistence;

    /**
     * @param PersistenceInterface $persistence
     */
    public function __construct(PersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    /**
     * @param int $userId
     * @param \DateTime $date
     * @return DiscountEntity|null
     */
    public function search(int $userId, \DateTime $date) :? DiscountEntity
    {
        $discounts = $this->persistence->searchAll('discount');

        /**
         * @var DiscountEntity $discount
         */
        foreach ($discounts as $discount) {
            if ($discount->getUser()->getId() === $userId && $discount->isInPeriod($date)) {
                return $discount;
            }
        }

        return null;
    }

    /**
     * @param AbstractUser $user
     * @param \DateTime $periodStart
     * @param \DateTime $periodEnd
     * @param int $amount
     */
    public function create(AbstractUser $user, \DateTime $periodStart, \DateTime $periodEnd, int $amount) : void
    {
        $this->persistence->save('discount', new DiscountEntity($user, $periodStart, $periodEnd, $amount));
    }
}
