<?php
declare(strict_types=1);

namespace DpDocument\Auth\Security;

use DpDocument\Auth\Entity\User;
use DpDocument\Auth\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserProvider
 *
 * @package DpDocument\Auth\Security
 * DpDocument | Research & Development
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var \DpDocument\Auth\Repository\UserRepository
     */
    private $repository;

    /**
     * UserProvider constructor.
     *
     * @param \DpDocument\Auth\Repository\UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $username
     *
     * @return \DpDocument\Auth\Entity\User
     */
    public function loadUserByUsername($username): User
    {
        $user = $this->repository->findByUsername($username);

        if (null === $user) {
            throw new UsernameNotFoundException('User not found');
        }

        return $user;
    }

    /**
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @return \DpDocument\Auth\Entity\User
     */
    public function refreshUser(UserInterface $user): User
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return $class === User::class;
    }

}
