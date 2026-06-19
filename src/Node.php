<?php

namespace Differ;

class Node implements \JsonSerializable
{
    private string $propertyName;
    private mixed $value;
    private string $diffType;
    private array $children;

    public const UNCHANGED = ' ';
    public const REMOVED = '-';
    public const ADDED = '+';
    public const UPDATED = '-+';

    public function __construct(string $propertyName, mixed $value, string $diffType, array $children = [])
    {
        $this->propertyName = $propertyName;
        $this->value = $value;
        $this->diffType = $diffType;
        $this->children = $children;
    }

    public function getChildren(): array
    {
        return $this->children;
    }

    public function getPropertyName(): mixed
    {
        return $this->propertyName;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getDiffType(): string
    {
        return $this->diffType;
    }

    public function isLeaf(): bool
    {
        return empty($this->children);
    }

    public function jsonSerialize(): array
    {
        $diffType = match ($this->diffType) {
            self::REMOVED   => 'removed',
            self::ADDED     => 'added',
            self::UNCHANGED => 'unchanged',
            self::UPDATED   => 'updated',
            default         => throw new \Exception("Not expected diffType: '{$this->diffType}'!")
        };
        $updatedChildren = array_map(
            fn(Node $item): array => $item->jsonSerialize(),
            $this->children
        );

        return [
            'propertyName' => $this->propertyName,
            'value'        => $this->value,
            'diffType'     => $diffType,
            'children'     => array_values($updatedChildren),
        ];
    }
}
