<?php
declare(strict_types=1);

namespace DpDocument\Auth\Domain;

use DpDocument\Auth\Dto\TokenData;
use DpDocument\Auth\Entity\User;
use DpDocument\Auth\ORM\EntityManager;
use DpDocument\JWT\Parser\Parser;
use Psr\Log\LoggerInterface;

/**
 * Class TokenDataPersister
 *
 * @package DpDocument\Auth\Domain
 * DpDocument | Research & Development
 */
class TokenDataPersister
{
    /**
     * @var \DpDocument\Auth\ORM\EntityManager
     */
    private $em;
    /**
     * @var \DpDocument\JWT\Parser\Parser
     */
    private $parser;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * TokenDataPersister constructor.
     *
     * @param \DpDocument\Auth\ORM\EntityManager $em
     * @param \DpDocument\JWT\Parser\Parser      $parser
     * @param \Psr\Log\LoggerInterface             $logger
     */
    public function __construct(EntityManager $em, Parser $parser, LoggerInterface $logger)
    {
        $this->em     = $em;
        $this->parser = $parser;
        $this->logger = $logger;
    }

    /**
     * @param \DpDocument\Auth\Dto\TokenData $tokenData
     *
     * @return \DpDocument\Auth\Entity\User
     */
    public function persist(TokenData $tokenData): User
    {
        $repository = $this->em->getRepository(User::class);

        $accessToken = $this->parser->parse($tokenData->accessToken);

        $user = $repository->findByUsername($accessToken->user());

        if (null === $user) {
            $user = new User();
        }

        try {
            $user->setExpires(\DateTime::createFromFormat('U', (string)$accessToken->expires()->format('U')));
            $user->setUsername($accessToken->user());
            $user->setRoles($accessToken->roles());
            $user->setAccessToken($tokenData->accessToken);
            $user->setRefreshToken($tokenData->refreshToken);

            $this->em->persist($user);
            $this->em->flush();
        } catch (\Throwable $throwable) {
            $this->logger->error('Error when saving user: ' . $throwable->getMessage());
        }

        return $user;
    }
}
