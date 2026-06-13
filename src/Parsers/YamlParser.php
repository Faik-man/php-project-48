<?php

namespace Differ\Parsers;

use Symfony\Component\Yaml\Yaml as SymfonyYaml;

class YamlParser implements ParserInterface
{
    public static function parse(string $content): \stdClass
    {
        return SymfonyYaml::parse($content, SymfonyYaml::PARSE_OBJECT_FOR_MAP);
    }
}
