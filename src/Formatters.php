<?php

namespace Differ\Formatters;

use Differ\Formatters\Plain;
use Differ\Formatters\Stylish;
use Differ\Formatters\Json;

function getFormatter(string $format): callable
{
    switch ($format) {
        case 'stylish':
            return fn($tree) => Stylish\format($tree);
        case 'plain':
            return fn($tree) => Plain\format($tree);
        case 'json':
            return fn($tree) => Json\format($tree);
        default:
            throw new \Exception("Undefined formatter for {$format} format!");
    }
}
