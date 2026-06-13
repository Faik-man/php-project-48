<?php

namespace Differ\Parsers;

use Differ\Parsers\JsonParser;
use Differ\Parsers\YamlParser;

const YAML_EXTENSIONS = ['yml', 'yaml'];

function getParser(string $fileExtension): ParserInterface
{
    if ($fileExtension === 'json') {
        return new JsonParser();
    } elseif (in_array($fileExtension, YAML_EXTENSIONS)) {
        return new YamlParser();
    } else {
        throw new \Exception('Not found implemented parsers for files!');
    }
}
