<?php

namespace Differ\Formatters;

use Differ\Node;
use Funct\Collection;

class PlainFormatter implements FormatterInterface
{
    public static function format(array $tree): string
    {
        $iter = function (Node $node, array $parents = []) use (&$iter): array {
            $newParents = [...$parents, $node->getPropertyName()];
            if ($node->isLeaf()) {
                $value = $node->getValue();

                $diffType = $node->getDiffType();
                $status = '';
                switch ($diffType) {
                    case Node::REMOVED:
                        $status = "Property '%s' was removed";
                        break;
                    case Node::ADDED:
                        $value = self::toString($value);
                        $status = "Property '%s' was added with value: {$value}";
                        break;
                    case Node::UPDATED:
                        $value = [self::toString($value[0]), self::toString($value[1])];
                        $status = "Property '%s' was updated. From {$value[0]} to {$value[1]}";
                        break;
                    default:
                        break;
                }

                return [sprintf($status, implode('.', $newParents))];
            }

            return array_map(
                fn(Node $child): array => $iter($child, $newParents),
                $node->getChildren()
            );
        };

        $result = array_map(
            fn(Node $node): array => $iter($node),
            $tree,
        );

        $flattenedResult = Collection\flattenAll($result);

        return implode("\n", array_filter($flattenedResult));
    }

    private static function toString(mixed $value): string
    {
        if (is_object($value)) {
            return '[complex value]';
        } elseif (is_null($value)) {
            return 'null';
        }

        return var_export($value, true);
    }
}
