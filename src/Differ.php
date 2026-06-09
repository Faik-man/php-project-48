<?php

namespace Differ\Differ;

function parseJson(string $content): \stdClass
{
    return json_decode(json: $content, flags: JSON_THROW_ON_ERROR);
}
