<?php
declare(strict_types=1);

namespace DpDocument\Auth\Http;

use DpDocument\Auth\Dto\TokenData;
use DpDocument\Auth\Helper\JsonParser;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class TokenLoader
 *
 * @package DpDocument\Auth\Http
 * DpDocument | Research & Development
 */
class TokenLoader
{
    /**
     * @var string
     */
    private $tokenUri;
    /**
     * @var string
     */
    private $clientId;
    /**
     * @var string
     */
    private $clientSecret;
    /**
     * @var \GuzzleHttp\Client
     */
    private $httpClient;
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * TokenLoader constructor.
     *
     * @param string                                     $tokenUri
     * @param string                                     $clientId
     * @param string                                     $clientSecret
     * @param \GuzzleHttp\Client                         $httpClient
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Psr\Log\LoggerInterface                   $logger
     */
    public function __construct(
        string $tokenUri,
        string $clientId,
        string $clientSecret,
        Client $httpClient,
        RouterInterface $router,
        LoggerInterface $logger
    ) {
        $this->tokenUri     = $tokenUri;
        $this->clientId     = $clientId;
        $this->clientSecret = $clientSecret;
        $this->httpClient   = $httpClient;
        $this->router       = $router;
        $this->logger       = $logger;
    }

    /**
     * @param string $code
     *
     * @return array|null
     */
    public function getTokenDataBy(string $code): ?TokenData
    {
        $response = $this->httpClient->request('post', $this->tokenUri, [
            'auth'        => [$this->clientId, $this->clientSecret],
            'form_params' => [
                'grant_type'   => 'authorization_code',
                'code'         => $code,
                'redirect_uri' => $this->router->generate('auth', [], RouterInterface::ABSOLUTE_URL)
            ]
        ]);

        if (200 === $response->getStatusCode()) {
            try {
                $tokenResponseBody = $response->getBody()->getContents();

                [
                    'access_token'  => $accessToken,
                    'expires_in'    => $expiresIn,
                    'token_type'    => $type,
                    'refresh_token' => $refreshToken
                ] = JsonParser::parse($tokenResponseBody);

                return new TokenData($accessToken, $expiresIn, $type, $refreshToken);
            } catch (\Throwable $throwable) {
                $this->logger->warning('Unable to extract token data: ' . $throwable->getMessage());
            }
        }

        $this->logger->warning('Unable to get token data: ' . $response->getReasonPhrase());

        return null;
    }

    /**
     * @param string $refreshToken
     *
     * @return array|null
     */
    public function refreshTokenDataBy(string $refreshToken): ?TokenData
    {
        $response = $this->httpClient->request('post', $this->tokenUri, [
            'auth'        => [$this->clientId, $this->clientSecret],
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $refreshToken
            ]
        ]);

        if (200 === $response->getStatusCode()) {
            try {
                $tokenResponseBody = $response->getBody()->getContents();

                [
                    'access_token'  => $accessToken,
                    'expires_in'    => $expiresIn,
                    'token_type'    => $type,
                    'refresh_token' => $refreshToken
                ] = JsonParser::parse($tokenResponseBody);

                return new TokenData($accessToken, $expiresIn, $type, $refreshToken);
            } catch (\Throwable $throwable) {
                $this->logger->warning('Unable to extract token data: ' . $throwable->getMessage());
            }
        }

        $this->logger->warning('Unable to get token data: ' . $response->getReasonPhrase());

        return null;
    }
}
