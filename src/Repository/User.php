<?php

namespace Repository;

use Entity\AbstractUser;
use Entity\LegalUser;
use Entity\NaturalUser;
use Persistence\PersistenceInterface;

class User
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
     * @param int $id
     * @return AbstractUser|null
     */
    public function search(int $id) :? AbstractUser
    {
        return $this->persistence->search('user', $id);
    }

    /**
     * @param int $id
     * @param string $userType
     * @return AbstractUser
     */
    public function create(int $id, string $userType) : AbstractUser
    {
        $user = null;

        switch ($userType) {
            case 'natural':
                $user = new NaturalUser();
                break;

            case 'legal':
                $user = new LegalUser();
                break;
        }

        $user->setId($id);

        $this->persistence->save('user', $user, $id);

        return $user;
    }

    /**
     * @param int $id
     * @param string $userType
     * @return AbstractUser
     */
    public function searchOrCreate(int $id, string $userType) : AbstractUser
    {
        $user = $this->search($id);

        if ($user !== null) {
            return $user;
        }

        return $this->create($id, $userType);
    }

    /**
     * @param int $id
     */
    public function remove(int $id) : void
    {
        $this->persistence->remove('user', $id);
    }
}
