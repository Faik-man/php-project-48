<?php

namespace App\JsonParser;

function parse(string $content): \stdClass
{
    return json_decode(json: $content, flags: JSON_THROW_ON_ERROR);
}
