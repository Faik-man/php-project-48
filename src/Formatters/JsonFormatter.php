<?php

namespace Differ\Formatters;

use Differ\Node;

class JsonFormatter implements FormatterInterface
{
    public static function format(array $tree): string
    {
        $json = self::createJsonObject($tree);

        $iter = function (string $propertyName, mixed $propertyValue, int $depth = 1) use (&$iter): string {
            $spaces = str_repeat(' ', $depth * 4);
            if (!is_object($propertyValue)) {
                $result = $spaces . self::toString($propertyName) . ': ' . self::toString($propertyValue);
                return $result;
            }

            $innerProperties = get_object_vars($propertyValue);
            $updatedProperties = array_map(
                fn(string $innerPropertyName): string => (
                    $iter(
                        $innerPropertyName,
                        $innerProperties[$innerPropertyName],
                        $depth + 1
                    )
                ),
                array_keys($innerProperties)
            );

            $str = $spaces . self::toString($propertyName) . ": {\n" .
                implode(",\n", $updatedProperties) . "\n" .
                $spaces . "}";

            return $str;
        };

        $properties = get_object_vars($json);
        $result = array_map(
            fn(string $propertyName): string => $iter($propertyName, $properties[$propertyName]),
            array_keys($properties)
        );

        return "{\n" . implode(",\n", $result) . "\n}";
    }

    private static function createJsonObject(array $tree): object
    {
        $iter = function (Node $node, object $acc) use (&$iter): object {
            $property = $node->getPropertyName();
            $children = $node->getChildren();
            if (empty($children)) {
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
                        $propertyValue->old = $value[0];
                        $propertyValue->new = $value[1];
                        $acc->updated->$property = $propertyValue;
                        break;
                    default:
                        $acc->$property = $value;
                        break;
                }

                return $acc;
            }

            $acc->$property = array_reduce(
                $children,
                fn(object $acc, Node $child) => $iter($child, $acc),
                new \stdClass()
            );

            return $acc;
        };

        $json = array_reduce(
            $tree,
            fn(object $acc, Node $node) => $iter($node, $acc),
            new \stdClass()
        );

        return $json;
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
