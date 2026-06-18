<?php

namespace Differ\Differ;

use Differ\Node;

use function Differ\Formatters\getFormatter;
use function Differ\Parsers\getParser;
use function Funct\Collection\sortBy;

function genDiff(string $filePath1, string $filePath2, string $format = 'stylish'): string
{
    $fileData1 = getFileData($filePath1);
    $fileData2 = getFileData($filePath2);

    $result = buildDiff($fileData1, $fileData2);
    $formatter = getFormatter($format);
    return $formatter::format($result);
}

function buildDiff(array $fileData1, array $fileData2): array
{
    $parser1 = getParser($fileData1['extension']);
    $parser2 = getParser($fileData2['extension']);

    $fileObj1 = $parser1::parse($fileData1['content']);
    $fileObj2 = $parser2::parse($fileData2['content']);

    return iterateObjects($fileObj1, $fileObj2);
}

function iterateObjects(object $fileObj1, object $fileObj2): array
{
    $keys = array_unique(
        array_merge(
            array_keys(get_object_vars($fileObj1)),
            array_keys(get_object_vars($fileObj2))
        )
    );
    $sortedKeys = sortBy($keys, fn($key) => $key);

    $tree = array_map(
        function (string $key) use ($fileObj1, $fileObj2): Node {
            if (property_exists($fileObj1, $key) && !property_exists($fileObj2, $key)) {
                $value = $fileObj1->$key;
                return new Node($key, $value, Node::REMOVED);
            }

            if (!property_exists($fileObj1, $key) && property_exists($fileObj2, $key)) {
                $value = $fileObj2->$key;
                return new Node($key, $value, Node::ADDED);
            }

            if (is_object($fileObj1->$key) && is_object($fileObj2->$key)) {
                $children = iterateObjects($fileObj1->$key, $fileObj2->$key);
                return new Node($key, '', Node::UNCHANGED, $children);
            }

            if ($fileObj1->$key === $fileObj2->$key) {
                $value = $fileObj1->$key;
                return new Node($key, $value, Node::UNCHANGED);
            }

            $value1 = $fileObj1->$key;
            $value2 = $fileObj2->$key;
            return new Node($key, ['oldValue' => $value1, 'newValue' => $value2], Node::UPDATED);
        },
        $sortedKeys
    );

    return $tree;
}

function getFileData(string $filePath): array
{
    $pathInfo = pathinfo($filePath);
    $fileContent = getFileContent($filePath);
    return [
        'extension' => $pathInfo['extension'] ?? '',
        'content'   => $fileContent
    ];
}

function getFileContent(string $filePath): string
{
    if (!file_exists($filePath)) {
        throw new \Exception("Not found file by path: {$filePath}!");
    }

    $fileContent = file_get_contents($filePath);
    if ($fileContent === false) {
        throw new \Exception("Error of reading file: {$filePath}!");
    }

    return $fileContent;
}
