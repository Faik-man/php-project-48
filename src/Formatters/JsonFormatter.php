<?php

namespace Differ\Formatters;

use Differ\Node;

class JsonFormatter implements FormatterInterface
{
    public static function format(array $tree): string
    {
        $json = self::createJsonObject($tree);

        $properties = get_object_vars($json);
        $result = array_map(
            fn(string $propertyName): string => self::iterateProperty($propertyName, $properties[$propertyName]),
            array_keys($properties)
        );

        return "{\n" . implode(",\n", $result) . "\n}";
    }

    private static function iterateProperty(string $propertyName, mixed $propertyValue, int $depth = 1): string
    {
        $spaces = str_repeat(' ', $depth * self::SPACES_COUNT);
        if (!is_object($propertyValue)) {
            $result = $spaces . self::toString($propertyName) . ': ' . self::toString($propertyValue);
            return $result;
        }

        $innerProperties = get_object_vars($propertyValue);
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

    private static function createJsonObject(array $tree): object
    {
        $json = array_reduce(
            $tree,
            fn(object $acc, Node $node) => self::iterateTree($node, $acc),
            new \stdClass()
        );

        return $json;
    }

    private static function iterateTree(Node $node, object $acc): object
    {
        $property = $node->getPropertyName();
        if ($node->isLeaf()) {
            $value = $node->getValue();
            $diffType = $node->getDiffType();
            switch ($diffType) {
                case Node::REMOVED:
                    /** @phpstan-ignore-next-line */
                    $acc->removed ??= new \stdClass();
                    $acc->removed->$property = $value;
                    break;
                case Node::ADDED:
                    /** @phpstan-ignore-next-line */
                    $acc->added ??= new \stdClass();
                    $acc->added->$property = $value;
                    break;
                case Node::UPDATED:
                    /** @phpstan-ignore-next-line */
                    $acc->updated ??= new \stdClass();
                    $propertyValue = new \stdClass();
                    $propertyValue->old = $value['oldValue'];
                    $propertyValue->new = $value['newValue'];
                    $acc->updated->$property = $propertyValue;
                    break;
                default:
                    $acc->$property = $value;
                    break;
            }

            return $acc;
        }

        $acc->$property = array_reduce(
            $node->getChildren(),
            fn(object $acc, Node $child) => self::iterateTree($child, $acc),
            new \stdClass()
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
