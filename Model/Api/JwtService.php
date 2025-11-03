<?php

declare(strict_types=1);

namespace Smartcore\InPostInternational\Model\Api;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Smartcore\InPostInternational\Model\ConfigProvider;
use stdClass;

class JwtService
{
    /**
     * JwtService constructor.
     *
     * @param JWT $jwt
     * @param JWK $jwk
     * @param ConfigProvider $configProvider
     */
    public function __construct(
        private readonly JWT $jwt,
        private readonly JWK $jwk,
        private readonly ConfigProvider   $configProvider,
    ) {
    }

    /**
     * Decode token
     *
     * @param string $token
     * @param array $keySet
     * @return stdClass
     */
    public function decode(string $token, array $keySet): stdClass
    {
        JWT::$leeway = $this->configProvider->getLeeway();

        return $this->jwt->decode($token, $keySet);
    }

    /**
     * Parse key set
     *
     * @param array $jwks
     * @return array
     */
    public function parseKeySet(array $jwks): array
    {
        return $this->jwk->parseKeySet($jwks);
    }
}
