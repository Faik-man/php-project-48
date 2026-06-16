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

        return "{\n" . implode(",\n", $result) . "\n}";
    }

    private static function iterateProperty(string $propertyName, mixed $propertyValue, int $depth = 1): string
    {
        $spaces = str_repeat(' ', $depth * self::SPACES_COUNT);
        if (!in_array(gettype($propertyValue), ['array', 'object'])) {
            $result = $spaces . self::toString($propertyName) . ': ' . self::toString($propertyValue);
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

        $begin = $spaces . self::toString($propertyName) . ": {\n";
        $end = $spaces . '}';

        $result = $begin . implode(",\n", $updatedProperties) . "\n" . $end;

        return $result;
    }

    private static function createJson(array $tree): array
    {
        $json = array_reduce(
            $tree,
            fn(array $acc, Node $node): array => self::iterateTree($node, $acc),
            []
        );

        return $json;
    }

    private static function iterateTree(Node $node, array $acc): array
    {
        $property = $node->getPropertyName();
        if ($node->isLeaf()) {
            $value = $node->getValue();
            $diffType = $node->getDiffType();
            switch ($diffType) {
                case Node::REMOVED:
                    $acc['removed'][$property] = $value;
                    break;
                case Node::ADDED:
                    $acc['added'][$property] = $value;
                    break;
                case Node::UPDATED:
                    $acc['updated'][$property] = [
                        'old' => $value['oldValue'],
                        'new' => $value['newValue'],
                    ];
                    break;
                default:
                    $acc[$property] = $value;
                    break;
            }

            return $acc;
        }

        $acc[$property] = array_reduce(
            $node->getChildren(),
            fn(array $acc, Node $child): array => self::iterateTree($child, $acc),
            []
        );

        return $acc;
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
