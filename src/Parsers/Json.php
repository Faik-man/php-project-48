<?php

namespace Differ\Parsers\Json;

function parse(string $content): \stdClass
{
    return json_decode(json: $content, flags: JSON_THROW_ON_ERROR);
}
