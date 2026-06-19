<?php

namespace Differ\Formatters;

use Differ\Node;

class JsonFormatter implements FormatterInterface
{
    public static function format(array $tree): string
    {
        $json = json_encode(
            $tree,
            JSON_THROW_ON_ERROR |
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        );

        return $json;
    }
}
