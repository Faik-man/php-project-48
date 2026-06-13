<?php

namespace Differ\Formatters;

use Differ\Node;

class StylishFormatter implements FormatterInterface
{
    public static function format(array $tree): string
    {
        $iter = function (Node $node, array $acc, $depth = 1) use (&$iter): array {
            $k = $node->getPropertyName();
            $value = $node->getValue();
            $diffType = $node->getDiffType();
            $children = $node->getChildren();

            $spacesCount = $depth * 4;
            $newDepth = $depth + 1;

            $spaces = self::createSpaces($diffType, $spacesCount);
            if (empty($children)) {
                if ($diffType === Node::UPDATED) {
                    $newAcc = array_merge($acc, [
                        self::createSpaces(Node::REMOVED, $spacesCount) .
                        $k . ': ' . self::toString($value[0], $newDepth),
                        self::createSpaces(Node::ADDED, $spacesCount) .
                        $k . ': ' . self::toString($value[1], $newDepth)
                    ]);
                } else {
                    $newAcc = [...$acc, $spaces . $k . ': ' . self::toString($value, $newDepth)];
                }

                return $newAcc;
            }

            $newAcc = [...$acc, $spaces . $k . ': {'];

            $updatedChildren = array_reduce(
                $children,
                fn(array $innerAcc, Node $child) => $iter($child, $innerAcc, $depth + 1),
                $newAcc
            );

            return [...$updatedChildren, $spaces . "}"];
        };

        $result = array_reduce(
            $tree,
            fn(array $acc, Node $node): array => $iter($node, $acc),
            []
        );

        $result = implode("\n", $result);
        return "{\n" . $result . "\n}";
    }

    private static function createSpaces(string $diffType, int $spacesCount): string
    {
        return str_pad("{$diffType} ", $spacesCount, ' ', STR_PAD_LEFT);
    }

    private static function toString(mixed $value, int $depth = 1): string
    {
        if (!is_object($value)) {
            $result = trim(var_export($value, true), "'");
            return $value === null ? mb_strtolower($result) : $result;
        }

        $vars = get_object_vars($value);
        $result = array_map(
            function ($key) use ($vars, $depth): string {
                $value = $vars[$key];
                $spaces = self::createSpaces(' ', $depth * 4);

                return $spaces . $key . ': ' . self::toString($value, $depth + 1);
            },
            array_keys($vars)
        );

        return "{\n" . implode("\n", $result) . "\n" . self::createSpaces(' ', ($depth - 1) * 4) . '}';
    }
}
