<?php

namespace Differ\Formatters\Stylish;

use Differ\Node;

function format(array $tree): string
{
    $iter = function (array $tree, int $depth = 1) use (&$iter): array {
        $result = [];
        foreach ($tree as $node) {
            $k = $node->getPropertyName();
            $value = $node->getValue();
            $diffType = $node->getDiffType();
            $children = $node->getChildren();

            $spacesCount = $depth * 4;
            $newDepth = $depth + 1;

            $spaces = createSpaces($diffType, $spacesCount);
            if (empty($children)) {
                if ($diffType === Node::UPDATED) {
                    $result = array_merge($result, [
                        createSpaces(Node::REMOVED, $spacesCount) . $k . ': ' . toString($value[0], $newDepth),
                        createSpaces(Node::ADDED, $spacesCount) . $k . ': ' . toString($value[1], $newDepth)
                    ]);
                } else {
                    $result[] = $spaces . $k . ': ' . toString($value, $newDepth);
                }
            } else {
                $result = [...$result, $spaces . $k . ': {', ...$iter($children, $newDepth), $spaces . '}'];
            }
        }

        return $result;
    };

    $result = $iter($tree);

    $result = implode("\n", $result);
    return "{\n" . $result . "\n}";
}

function createSpaces(string $diffType, int $spacesCount): string
{
    return str_pad("{$diffType} ", $spacesCount, ' ', STR_PAD_LEFT);
}

function toString(mixed $value, int $depth = 1): string
{
    if (!is_object($value)) {
        $result = trim(var_export($value, true), "'");
        return $value === null ? mb_strtolower($result) : $result;
    }

    $vars = get_object_vars($value);
    $result = array_map(
        function ($key) use ($vars, $depth): string {
            $value = $vars[$key];
            $spaces = createSpaces(' ', $depth * 4);

            return $spaces . $key . ': ' . toString($value, $depth + 1);
        },
        array_keys($vars)
    );

    return "{\n" . implode("\n", $result) . "\n" . createSpaces(' ', ($depth - 1) * 4) . '}';
}
