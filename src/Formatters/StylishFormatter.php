<?php

namespace Differ\Formatters;

use Differ\Node;

class StylishFormatter implements FormatterInterface
{
    public static function format(array $tree): string
    {
        $iter = function (Node $node, array $acc, $depth = 1) use (&$iter): array {
            $propertyName = $node->getPropertyName();
            $value = $node->getValue();
            $diffType = $node->getDiffType();

            $spacesCount = $depth * self::SPACES_COUNT;
            $newDepth = $depth + 1;

            $spaces = self::createSpaces($diffType, $spacesCount);
            if ($node->isLeaf()) {
                if ($diffType === Node::UPDATED) {
                    $newAcc = array_merge($acc, [
                        self::createSpaces(Node::REMOVED, $spacesCount) .
                        "{$propertyName}: " . self::toString($value[0], $newDepth),
                        self::createSpaces(Node::ADDED, $spacesCount) .
                        "{$propertyName}: " . self::toString($value[1], $newDepth)
                    ]);
                } else {
                    $newAcc = [...$acc, $spaces . $propertyName . ': ' . self::toString($value, $newDepth)];
                }

                return $newAcc;
            }

            $newAcc = [...$acc, "{$spaces}{$propertyName}: {"];

            $updatedChildren = array_reduce(
                $node->getChildren(),
                fn(array $innerAcc, Node $child) => $iter($child, $innerAcc, $depth + 1),
                $newAcc
            );

            return [...$updatedChildren, $spaces . '}'];
        };

        $result = array_reduce(
            $tree,
            fn(array $acc, Node $node): array => $iter($node, $acc),
            []
        );

        $result = implode("\n", $result);
        return "{\n{$result}\n}";
    }

    private static function createSpaces(string $diffType, int $spacesCount): string
    {
        return str_pad("{$diffType} ", $spacesCount, ' ', STR_PAD_LEFT);
    }

    private static function toString(mixed $value, int $depth = 1): string
    {
        if (!is_object($value)) {
            $result = trim(var_export($value, true), "'");
            return $value === null ? 'null' : $result;
        }

        $properties = get_object_vars($value);
        $result = array_map(
            function (string $propertyName) use ($properties, $depth): string {
                $propertyValue = $properties[$propertyName];
                $spaces = str_repeat(' ', $depth * self::SPACES_COUNT);

                return $spaces . $propertyName . ': ' . self::toString($propertyValue, $depth + 1);
            },
            array_keys($properties)
        );

        $objectParts = [
            '{',
            implode("\n", $result),
            str_repeat(' ', self::SPACES_COUNT * ($depth - 1)) . '}'
        ];

        return implode("\n", $objectParts);
    }
}
