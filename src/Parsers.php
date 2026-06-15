<?php

namespace Differ\Parsers;

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
