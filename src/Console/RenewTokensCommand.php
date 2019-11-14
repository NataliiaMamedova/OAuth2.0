<?php
declare(strict_types=1);

namespace DpDocument\Auth\Console;

use DpDocument\Auth\Domain\TokenDataPersister;
use DpDocument\Auth\Http\TokenLoader;
use DpDocument\Auth\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RenewTokensCommand
 *
 * @package DpDocument\Auth\Console
 * DpDocument | Research & Development
 */
class RenewTokensCommand extends Command
{
    /**
     * @var \DpDocument\Auth\Domain\TokenDataPersister
     */
    private $tokenDataPersister;
    /**
     * @var \DpDocument\Auth\Http\TokenLoader
     */
    private $tokenLoader;
    /**
     * @var \DpDocument\Auth\Repository\UserRepository
     */
    private $userRepository;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * RenewTokensCommand constructor.
     *
     * @param \DpDocument\Auth\Domain\TokenDataPersister $tokenDataPersister
     * @param \DpDocument\Auth\Http\TokenLoader          $tokenLoader
     * @param \DpDocument\Auth\Repository\UserRepository $userRepository
     * @param \Psr\Log\LoggerInterface                     $logger
     */
    public function __construct(
        TokenDataPersister $tokenDataPersister,
        TokenLoader $tokenLoader,
        UserRepository $userRepository,
        LoggerInterface $logger
    ) {
        parent::__construct();
        $this->tokenDataPersister = $tokenDataPersister;
        $this->tokenLoader        = $tokenLoader;
        $this->userRepository     = $userRepository;
        $this->logger             = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('token:renew')
            ->setDescription('Renew existing access tokens using refresh tokens');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $toTime = new \DateTime('+2 minutes');
        /** @var \DpDocument\Auth\Entity\User[] $users */
        $users = $this->userRepository->createQueryBuilder('u')
            ->where('u.expires <= :time')
            ->setParameter('time', $toTime)
            ->getQuery()
            ->getResult();

        $successRenews = 0;

        foreach ($users as $user) {
            if (null !== $user->getRefreshToken()) {
                $tokenData = $this->tokenLoader->refreshTokenDataBy($user->getRefreshToken());

                if (null === $tokenData) {
                    continue;
                }

                $ok = $this->tokenDataPersister->persist($tokenData);

                if (null !== $ok) {
                    $successRenews++;
                }
            }
        }

        $output->writeln('Success renews ' . $successRenews);
    }
}
