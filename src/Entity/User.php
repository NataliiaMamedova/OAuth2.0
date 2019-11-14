<?php
declare(strict_types=1);

namespace DpDocument\Auth\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * Class User
 *
 * @package DpDocument\Auth\Entity
 * DpDocument | Research & Development
 *
 * @ORM\Entity(repositoryClass="DpDocument\Auth\Repository\UserRepository")
 * @ORM\Table(name="users")
 */
class User implements AdvancedUserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     *
     * @var string
     */
    private $username;

    /**
     * @ORM\Column(type="datetime")
     *
     * @var \DateTime
     */
    private $expires;

    /**
     * @ORM\Column(type="simple_array")
     *
     * @var iterable
     */
    private $roles;

    /**
     * @ORM\Column(type="text")
     *
     * @var string
     */
    private $accessToken;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    private $refreshToken;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return \DateTime
     */
    public function getExpires(): \DateTime
    {
        return $this->expires;
    }

    /**
     * @param \DateTime $expires
     */
    public function setExpires(\DateTime $expires): void
    {
        $this->expires = $expires;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return iterable|Role[]
     */
    public function getRoles(): iterable
    {
        return $this->roles;
    }

    /**
     * @param iterable $roles
     */
    public function setRoles(iterable $roles): void
    {
        $this->roles = [];

        foreach ($roles as $role) {
            if ($role instanceof Role) {
                $this->roles[] = $role->getRole();
            } else {
                $this->roles[] = $role;
            }
        }
    }

    /**
     * @return bool
     */
    public function isAccountNonExpired(): bool
    {
        return $this->expires > new \DateTime();
    }

    /**
     * @return bool
     */
    public function isAccountNonLocked(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isCredentialsNonExpired(): bool
    {
        return $this->expires > new \DateTime();
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * @return null|string
     */
    public function getPassword(): ?string
    {
        return null;
    }

    /**
     * @return null|string
     */
    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }
}
