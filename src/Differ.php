<?php

namespace Differ\Differ;

use Funct\Collection;
use Differ\Node;

use function Differ\Formatters\getFormatter;
use function Differ\Parsers\getParser;

function genDiff(string $filePath1, string $filePath2, string $format = 'stylish'): string
{
    $result = buildDiff($filePath1, $filePath2);
    $formatter = getFormatter($format);
    return $formatter::format($result);
}

function buildDiff(string $filePath1, string $filePath2): array
{
    $normalizedFilePath1 = normalizePath($filePath1);
    $normalizedFilePath2 = normalizePath($filePath2);

    $fileExtension1 = getFileExtension($normalizedFilePath1);
    $fileExtension2 = getFileExtension($normalizedFilePath2);

    $fileContent1 = getFileContent($normalizedFilePath1);
    $fileContent2 = getFileContent($normalizedFilePath2);

    $parser = getParser($fileExtension1, $fileExtension2);
    $fileObj1 = $parser::parse($fileContent1);
    $fileObj2 = $parser::parse($fileContent2);

    $iter = function (object $fileObj1, object $fileObj2) use (&$iter): array {
        $keys = array_unique(
            array_merge(
                array_keys(get_object_vars($fileObj1)),
                array_keys(get_object_vars($fileObj2))
            )
        );
        $sortedKeys = Collection\sortBy($keys, fn($key) => $key);

        $tree = array_reduce(
            $sortedKeys,
            function (array $acc, string $key) use ($iter, $fileObj1, $fileObj2): array {
                if (property_exists($fileObj1, $key) && !property_exists($fileObj2, $key)) {
                    $value = $fileObj1->$key;
                    return [...$acc, new Node($key, $value, Node::REMOVED)];
                } elseif (!property_exists($fileObj1, $key) && property_exists($fileObj2, $key)) {
                    $value = $fileObj2->$key;
                    return [...$acc, new Node($key, $value, Node::ADDED)];
                } elseif (is_object($fileObj1->$key) && is_object($fileObj2->$key)) {
                    $children = $iter($fileObj1->$key, $fileObj2->$key);
                    return [...$acc, new Node($key, '', Node::UNCHANGED, $children)];
                } elseif ($fileObj1->$key === $fileObj2->$key) {
                    $value = $fileObj1->$key;
                    return [...$acc, new Node($key, $value, Node::UNCHANGED)];
                } else {
                    $value1 = $fileObj1->$key;
                    $value2 = $fileObj2->$key;
                    return [...$acc, new Node($key, [$value1, $value2], Node::UPDATED)];
                }
            },
            []
        );
        return $tree;
    };

    return $iter($fileObj1, $fileObj2);
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
