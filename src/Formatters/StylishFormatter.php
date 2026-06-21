<?php

namespace Differ\Formatters;

use Differ\Node;

use function Funct\Collection\flattenAll;

class StylishFormatter implements FormatterInterface
{
    protected const FORMAT_STRING = '%s%s: %s';

    public static function format(array $tree): string
    {
        $result = array_map(
            fn(Node $node): array => self::iterateTree($node),
            $tree
        );

        $flattenedResult = flattenAll($result);
        $result = implode("\n", $flattenedResult);
        return "{\n{$result}\n}";
    }

    private static function iterateTree(Node $node, int $depth = 1): array
    {
        $propertyName = $node->getPropertyName();
        $value = $node->getValue();
        $diffType = $node->getDiffType();

        $spacesCount = $depth * self::SPACES_COUNT;
        $newDepth = $depth + 1;

        $spaces = self::createSpaces($diffType, $spacesCount);
        if ($node->isLeaf()) {
            if ($diffType === Node::UPDATED) {
                $oldValue = sprintf(
                    self::FORMAT_STRING,
                    self::createSpaces(Node::REMOVED, $spacesCount),
                    $propertyName,
                    self::toString($value['oldValue'], $newDepth)
                );
                $newValue = sprintf(
                    self::FORMAT_STRING,
                    self::createSpaces(Node::ADDED, $spacesCount),
                    $propertyName,
                    self::toString($value['newValue'], $newDepth)
                );
                $result = [
                    $oldValue,
                    $newValue
                ];
            } else {
                $result = [sprintf(self::FORMAT_STRING, $spaces, $propertyName, self::toString($value, $newDepth))];
            }

            return $result;
        }

        $updatedChildren = array_map(
            fn(Node $child) => self::iterateTree($child, $depth + 1),
            $node->getChildren()
        );

        return ["{$spaces}{$propertyName}: {", ...$updatedChildren, sprintf('%s}', $spaces)];
    }

    private static function createSpaces(string $diffType, int $spacesCount): string
    {
        return str_pad("{$diffType} ", $spacesCount, ' ', STR_PAD_LEFT);
    }

    private static function toString(mixed $value, int $depth = 1): string
    {
        if (!is_object($value)) {
            if (is_string($value)) {
                return $value;
            }

            if (is_null($value)) {
                return 'null';
            }

            if (is_numeric($value)) {
                return (string)$value;
            }

            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }

            throw new \Exception(sprintf("Not expected type: '%s'!", gettype($value)));
        }

        $properties = get_object_vars($value);
        $result = array_map(
            function (string $propertyName) use ($properties, $depth): string {
                $propertyValue = $properties[$propertyName];
                $spaces = str_repeat(' ', $depth * self::SPACES_COUNT);

                return sprintf(self::FORMAT_STRING, $spaces, $propertyName, self::toString($propertyValue, $depth + 1));
            },
            array_keys($properties)
        );

        $objectParts = [
            '{',
            implode("\n", $result),
            sprintf('%s}', str_repeat(' ', self::SPACES_COUNT * ($depth - 1)))
        ];

        return implode("\n", $objectParts);
    }
}
