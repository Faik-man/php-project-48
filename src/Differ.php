<?php

namespace Differ\Differ;

use Funct\Collection;
use Differ\Parsers\Json;
use Differ\Parsers\Yaml;
use Differ\Node;

use function Differ\Formatters\getFormatter;

const YAML_EXTENSIONS = ['yml', 'yaml'];

function genDiff(string $filePath1, string $filePath2, string $format = 'stylish'): string
{
    $result = buildDiff($filePath1, $filePath2);
    $funcationFormatter = getFormatter($format);
    return $funcationFormatter($result);
}

function buildDiff(string $filePath1, string $filePath2): array
{
    $normalizedFilePath1 = normalizePath($filePath1);
    $normalizedFilePath2 = normalizePath($filePath2);

    $fileExtension1 = getFileExtension($normalizedFilePath1);
    $fileExtension2 = getFileExtension($normalizedFilePath2);

    $fileContent1 = getFileContent($normalizedFilePath1);
    $fileContent2 = getFileContent($normalizedFilePath2);

    $functionParser = getParser($fileExtension1, $fileExtension2);
    $fileObj1 = $functionParser($fileContent1);
    $fileObj2 = $functionParser($fileContent2);

    $iter = function (object $fileObj1, object $fileObj2) use (&$iter) {
        $keys = array_unique(
            array_merge(
                array_keys(get_object_vars($fileObj1)),
                array_keys(get_object_vars($fileObj2))
            )
        );
        $sortedKeys = Collection\sortBy($keys, fn($key) => $key);

        $tree = [];
        foreach ($sortedKeys as $key) {
            if (property_exists($fileObj1, $key) && !property_exists($fileObj2, $key)) {
                $value = $fileObj1->$key;
                $tree = insertNode($tree, $key, $value, '-');
            } elseif (!property_exists($fileObj1, $key) && property_exists($fileObj2, $key)) {
                $value = $fileObj2->$key;
                $tree = insertNode($tree, $key, $value, '+');
            } elseif (is_object($fileObj1->$key) && is_object($fileObj2->$key)) {
                $children = $iter($fileObj1->$key, $fileObj2->$key);
                $tree = insertNode($tree, $key, '', ' ', $children);
            } elseif ($fileObj1->$key === $fileObj2->$key) {
                $value = $fileObj1->$key;
                $tree = insertNode($tree, $key, $value, ' ');
            } else {
                $value1 = $fileObj1->$key;
                $value2 = $fileObj2->$key;
                $tree = insertNode($tree, $key, [$value1, $value2], '-+');
            }
        }

        return $tree;
    };

    return $iter($fileObj1, $fileObj2);
}

function insertNode(array $tree, string $propertyName, mixed $value, string $diffType, array $children = []): array
{
    $tree[] = new Node($propertyName, $value, $diffType, $children);
    return $tree;
}

function getParser(string $fileExtension1, string $fileExtension2): callable
{
    if ($fileExtension1 === 'json' && $fileExtension2 === 'json') {
        return fn(string $content) => Json\Parse($content);
    } elseif (in_array($fileExtension1, YAML_EXTENSIONS) && in_array($fileExtension2, YAML_EXTENSIONS)) {
        return fn(string $content) => Yaml\parse($content);
    } else {
        throw new \Exception('Not found implemented parsers for files!');
    }
}

function getFileContent(string $filePath): string
{
    $fileContent = @file_get_contents($filePath);
    if ($fileContent === false) {
        throw new \Exception("Not found file by path: {$filePath}!");
    }

    return (string)$fileContent;
}

function getFileExtension(string $filePath): string
{
    $pathInfo = pathinfo($filePath);
    return $pathInfo['extension'] ?? '';
}

function normalizePath(string $filePath): string
{
    if (
        str_starts_with($filePath, '/') ||
        (ctype_alpha($filePath[0]) && str_starts_with(mb_substr($filePath, 1), ":\\"))
    ) {
        return $filePath;
    }

    $absFilePath = getcwd() . "/{$filePath}";

    $pathParts = explode(DIRECTORY_SEPARATOR, $absFilePath);
    $filteredPathParts = array_filter(
        $pathParts,
        fn(string $part) => $part !== '.'
    );

    $filteredPathParts = array_reduce(
        $filteredPathParts,
        function (array $acc, string $part): array {
            if ($part == '..') {
                array_pop($acc);
            } else {
                array_push($acc, $part);
            }

            return $acc;
        },
        []
    );

    return implode(DIRECTORY_SEPARATOR, $filteredPathParts);
}
