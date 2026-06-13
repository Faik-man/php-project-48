<?php

namespace Differ\Parsers;

class JsonParser implements ParserInterface
{
    public static function parse(string $content): \stdClass
    {
        return json_decode(json: $content, flags: JSON_THROW_ON_ERROR);
    }
}
