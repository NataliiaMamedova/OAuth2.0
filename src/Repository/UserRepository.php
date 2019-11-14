<?php
declare(strict_types=1);

namespace DpDocument\Auth\Repository;

use DpDocument\Auth\Entity\User;
use DpDocument\Auth\ORM\EntityManager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Class UserRepository
 *
 * @package DpDocument\Auth\Repository
 * DpDocument | Research & Development
 */
class UserRepository extends EntityRepository implements ServiceEntityRepositoryInterface
{
    /**
     * UserRepository constructor.
     *
     * @param \DpDocument\Auth\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, $em->getClassMetadata(User::class));
    }

    /**
     * @param string $username
     *
     * @return \DpDocument\Auth\Entity\User|null
     */
    public function findByUsername(string $username): ?User
    {
        /** @var User $user */
        $user = $this->findOneBy(['username' => $username]);

        return $user;
    }
}
