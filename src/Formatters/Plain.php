<?php

namespace Differ\Formatters\Plain;

use Differ\Node;
use Funct\Collection;

function format(array $tree): string
{
    $iter = function (Node $node, array $parents = []) use (&$iter): array {
        $newParents = [...$parents, $node->getPropertyName()];
        $children = $node->getChildren();
        if (empty($children)) {
            $value = $node->getValue();

            $diffType = $node->getDiffType();
            $status = '';
            switch ($diffType) {
                case Node::REMOVED:
                    $status = "Property '%s' was removed";
                    break;
                case Node::ADDED:
                    $value = toString($value);
                    $status = "Property '%s' was added with value: {$value}";
                    break;
                case Node::UPDATED:
                    $value = [toString($value[0]), toString($value[1])];
                    $status = "Property '%s' was updated. From {$value[0]} to {$value[1]}";
                    break;
                default:
                    $status = '';
                    break;
            }
            return [sprintf($status, implode('.', $newParents))];
        }

        return array_map(
            fn(Node $child): array => $iter($child, $newParents),
            $children
        );
    };

    $result = array_map(
        fn(Node $node): array => $iter($node),
        $tree,
    );

    $flattenedResult = Collection\flattenAll($result);

    $result = array_filter($flattenedResult);

    return implode("\n", $result);
}

function toString(mixed $value): string
{
    if (is_object($value)) {
        return '[complex value]';
    } elseif (is_null($value)) {
        return 'null';
    }

    return var_export($value, true);
}
