<?php

namespace Differ\Formatters;

use Differ\Node;
use Funct\Collection;

class PlainFormatter implements FormatterInterface
{
    public static function format(array $tree): string
    {
        $result = array_map(
            fn(Node $node): array => self::iterateTree($node),
            $tree,
        );

        $flattenedResult = Collection\flattenAll($result);

        return implode("\n", array_filter($flattenedResult));
    }

    private static function iterateTree(Node $node, array $parents = []): array
    {
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
                    $oldValue = self::toString($value['oldValue']);
                    $newValue = self::toString($value['newValue']);
                    $status = "Property '%s' was updated. From {$oldValue} to {$newValue}";
                    break;
                default:
                    break;
            }

            return [sprintf($status, implode('.', $newParents))];
        }

        return array_map(
            fn(Node $child): array => self::iterateTree($child, $newParents),
            $node->getChildren()
        );
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
