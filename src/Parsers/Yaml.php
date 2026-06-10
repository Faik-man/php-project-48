<?php

namespace Differ\Parsers\Yaml;

use Symfony\Component\Yaml\Yaml as SymfonyYaml;

function parse(string $content): \stdClass
{
    return SymfonyYaml::parse($content, SymfonyYaml::PARSE_OBJECT_FOR_MAP);
}
