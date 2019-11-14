<?php
declare(strict_types=1);

namespace DpDocument\Auth\Helper;

use DpDocument\Auth\Exception\JsonParseException;

/**
 * Class JsonParser
 *
 * @package DpDocument\Auth\Http
 * DpDocument | Research & Development
 */
class JsonParser
{
    /**
     * @param string $json
     *
     * @return array
     * @throws \DpDocument\Auth\Exception\JsonParseException
     */
    public static function parse(string $json): array
    {
        $jsonData = \json_decode($json, true);

        $error = \json_last_error();

        if (JSON_ERROR_NONE !== $error) {
            throw new JsonParseException(\json_last_error_msg());
        }

        return $jsonData;
    }
}
