<?php

namespace Differ\Parsers;

use Differ\Parsers\JsonParser;
use Differ\Parsers\YamlParser;

function getParser(string $fileExtension): ParserInterface
{
    switch ($fileExtension) {
        case 'json':
            return new JsonParser();
        case 'yml':
        case 'yaml':
            return new YamlParser();
        default:
            throw new \Exception('Not found implemented parsers for files!');
    }
}
