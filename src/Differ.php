<?php

namespace Differ\Differ;

use Funct\Collection;
use Differ\Parsers\Json;
use Differ\Parsers\Yaml;

const YAML_EXTENSIONS = ['yml', 'yaml'];

function genDiff(string $filePath1, string $filePath2): string
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

    $keys = array_unique(
        array_merge(
            array_keys(get_object_vars($fileObj1)),
            array_keys(get_object_vars($fileObj2))
        )
    );
    $sortedKeys = Collection\sortBy($keys, fn($key) => $key);

    $result = [];
    foreach ($sortedKeys as $key) {
        if (isset($fileObj1->$key) && !isset($fileObj2->$key)) {
            $value = toString($fileObj1->$key);
            $result[] = "  - {$key}: {$value}";
        } elseif (!isset($fileObj1->$key) && isset($fileObj2->$key)) {
            $value = toString($fileObj2->$key);
            $result[] = "  + {$key}: {$value}";
        } elseif ($fileObj1->$key === $fileObj2->$key) {
            $value = toString($fileObj1->$key);
            $result[] = "    {$key}: {$value}";
        } else {
            $value1 = toString($fileObj1->$key);
            $value2 = toString($fileObj2->$key);
            $result[] = "  - {$key}: {$value1}";
            $result[] = "  + {$key}: {$value2}";
        }
    }

    return "{\n" . implode("\n", $result) . "\n}";
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

function toString(mixed $value): string
{
    return trim(var_export($value, true), "'");
}
