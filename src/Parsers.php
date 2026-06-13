<?php

namespace Differ\Parsers;

use Differ\Parsers\JsonParser;
use Differ\Parsers\YamlParser;

const YAML_EXTENSIONS = ['yml', 'yaml'];

function getParser(string $fileExtension1, string $fileExtension2): ParserInterface
{
    if ($fileExtension1 === 'json' && $fileExtension2 === 'json') {
        return new JsonParser();
    } elseif (in_array($fileExtension1, YAML_EXTENSIONS) && in_array($fileExtension2, YAML_EXTENSIONS)) {
        return new YamlParser();
    } else {
        throw new \Exception('Not found implemented parsers for files!');
    }
}
