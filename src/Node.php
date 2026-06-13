<?php

namespace Differ;

class Node
{
    private string $propertyName;
    private mixed $value;
    private string $diffType;
    private array $children;

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
}
