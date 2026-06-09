<?php

namespace Differ\Differ;

use Funct\Collection;

function genDiff(string $filePath1, string $filePath2): string
{
    $getFileContent = function (string $filePath): string {
        $normalizedFilePath = realpath($filePath);
        if ($normalizedFilePath === false) {
            throw new \Exception("Not found first file by path: {$filePath}!");
        }

        $fileContent = file_get_contents($normalizedFilePath);
        if ($fileContent === false) {
            throw new \Exception("Not found first file by path: {$normalizedFilePath}!");
        }

        return $fileContent;
    };

    $fileContent1 = $getFileContent($filePath1);
    $fileContent2 = $getFileContent($filePath2);

    $fileObj1 = parseJson($fileContent1);
    $fileObj2 = parseJson($fileContent2);

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

function toString(mixed $value): string
{
    return trim(var_export($value, true), "'");
}

function parseJson(string $content): \stdClass
{
    return json_decode(json: $content, flags: JSON_THROW_ON_ERROR);
}
