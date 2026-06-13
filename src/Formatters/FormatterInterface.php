<?php

namespace Differ\Formatters;

interface FormatterInterface
{
    public static function format(array $tree): string;
}
