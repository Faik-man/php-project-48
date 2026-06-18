<?php

namespace Differ\Formatters;

use Differ\Node;

class JsonFormatter implements FormatterInterface
{
    public static function format(array $tree): string
    {
        $json = self::createJson($tree);

        $result = array_map(
            fn(string $propertyName): string => self::iterateProperty($propertyName, $json[$propertyName]),
            array_keys($json)
        );

        return sprintf("{\n%s\n}", implode(",\n", $result));
    }

    private static function iterateProperty(string $propertyName, mixed $propertyValue, int $depth = 1): string
    {
        $spaces = str_repeat(' ', $depth * self::SPACES_COUNT);
        if (!in_array(gettype($propertyValue), ['array', 'object'])) {
            $result = sprintf('%s%s: %s', $spaces, self::toString($propertyName), self::toString($propertyValue));
            return $result;
        }

        $innerProperties = is_object($propertyValue) ? get_object_vars($propertyValue) : $propertyValue;
        $updatedProperties = array_map(
            fn(string $innerPropertyName): string => (
                self::iterateProperty(
                    $innerPropertyName,
                    $innerProperties[$innerPropertyName],
                    $depth + 1
                )
            ),
            array_keys($innerProperties)
        );

        $result = [
            sprintf('%s%s: {', $spaces, self::toString($propertyName)),
            implode(",\n", $updatedProperties),
            sprintf("%s}", $spaces)
        ];

        return implode("\n", $result);
    }

    private static function createJson(array $tree): array
    {
        $json = array_map(
            fn(Node $node): array => self::iterateTree($node),
            $tree
        );

        return self::mergeRecursive($json);
    }

    private static function iterateTree(Node $node): array
    {
        $property = $node->getPropertyName();
        if ($node->isLeaf()) {
            $value = $node->getValue();
            $diffType = $node->getDiffType();
            switch ($diffType) {
                case Node::REMOVED:
                    $result = [
                        'removed' => [
                            $property => $value
                        ]
                    ];
                    break;
                case Node::ADDED:
                    $result = [
                        'added' => [
                            $property => $value
                        ]
                    ];
                    break;
                case Node::UPDATED:
                    $result = [
                        'updated' => [
                            $property => [
                                'old' => $value['oldValue'],
                                'new' => $value['newValue']
                            ]
                        ]
                    ];
                    break;
                default:
                    $result = [
                        $property => $value
                    ];
                    break;
            }

            return $result;
        }

        $updatedChildren = array_map(
            fn(Node $child): array => self::iterateTree($child),
            $node->getChildren()
        );

        $result = [
            $property => self::mergeRecursive($updatedChildren)
        ];

        return $result;
    }

    private static function mergeRecursive(array $nested): array
    {
        $result = array_reduce(
            $nested,
            fn(array $acc, array $item): array => array_merge_recursive($acc, $item),
            []
        );

        return $result;
    }

    private static function toString(mixed $value): string
    {
        if (is_string($value)) {
            return "\"{$value}\"";
        } elseif (is_null($value)) {
            return 'null';
        }

        return var_export($value, true);
    }
}
