<?php
declare(strict_types=1);

namespace DpDocument\Auth\Security\Authenticator;

use DpDocument\Auth\Entity\User;
use DpDocument\JWT\Parser\Parser;
use DpDocument\JWT\Verifier\Verifier;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class JwtAuthenticator
 *
 * @package DpDocument\Auth\Security\Authenticator
 * DpDocument | Research & Development
 */
class JwtAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var \DpDocument\JWT\Parser\Parser
     */
    private $parser;
    /**
     * @var \DpDocument\JWT\Verifier\Verifier
     */
    private $verifier;

    /**
     * JwtAuthenticator constructor.
     *
     * @param \DpDocument\JWT\Parser\Parser     $parser
     * @param \DpDocument\JWT\Verifier\Verifier $verifier
     */
    public function __construct(Parser $parser, Verifier $verifier)
    {
        $this->parser   = $parser;
        $this->verifier = $verifier;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request                               $request
     * @param \Symfony\Component\Security\Core\Exception\AuthenticationException|null $authException
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        return new JsonResponse(['message' => $authException->getMessage()], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {
        return $request->headers->has('Authorization');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     * @throws \DpDocument\JWT\Exception\NotSupportedAlgorithmException
     */
    public function getCredentials(Request $request): array
    {
        $authorizationHeader = $request->headers->get('Authorization');
        [$tokenType, $jwt] = explode(' ', $authorizationHeader);

        $token = $this->parser->parse($jwt);

        if (null === $token || !$this->verifier->verify($token)) {
            throw new AuthenticationException('JWT token not valid');
        }

        if ($token->expires() < new \DateTimeImmutable()) {
            throw  new CredentialsExpiredException('JWT expired');
        }

        $username = $token->user();

        if (null === $username) {
            throw new AuthenticationCredentialsNotFoundException('Username not found');
        }

        $roles = $token->roles();

        return [
            'username' => $username,
            'roles'    => $roles,
            'expires'  => $token->expires()->format('U'),
            'token'    => $jwt
        ];
    }

    /**
     * @param mixed                                                       $credentials
     * @param \Symfony\Component\Security\Core\User\UserProviderInterface $userProvider
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider): UserInterface
    {
        $user = new User();
        $user->setUsername($credentials['username']);
        $user->setRoles($credentials['roles']);
        $user->setExpires(\DateTime::createFromFormat('U', $credentials['expires']));

        return $user;
    }

    /**
     * @param mixed                                               $credentials
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     *
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request                          $request
     * @param \Symfony\Component\Security\Core\Exception\AuthenticationException $exception
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        return new JsonResponse(['message' => $exception->getMessage()], Response::HTTP_FORBIDDEN);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request                            $request
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @param string                                                               $providerKey
     *
     * @return null|\Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return null;
    }

    /**
     * @return bool
     */
    public function supportsRememberMe(): bool
    {
        return false;
    }

}
