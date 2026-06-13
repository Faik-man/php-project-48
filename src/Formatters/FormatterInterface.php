<?php

namespace Differ\Formatters;

interface FormatterInterface
{
    public const SPACES_COUNT = 4;
    public static function format(array $tree): string;
}
