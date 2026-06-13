<?php

namespace Differ\Formatters;

use Differ\Formatters\Plain;
use Differ\Formatters\Stylish;

function getFormatter(string $format): callable
{
    switch ($format) {
        case 'stylish':
            return fn($tree) => Stylish\format($tree);
        case 'plain':
            return fn($tree) => Plain\format($tree);
        default:
            throw new \Exception("Undefined formatter for {$format} format!");
    }
}
