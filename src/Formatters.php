<?php

namespace Differ\Formatters;

use Differ\Formatters\PlainFormatter;
use Differ\Formatters\StylishFormatter;
use Differ\Formatters\JsonFormatter;

function getFormatter(string $format): FormatterInterface
{
    switch ($format) {
        case 'stylish':
            return new StylishFormatter();
        case 'plain':
            return new PlainFormatter();
        case 'json':
            return new JsonFormatter();
        default:
            throw new \Exception("Undefined formatter for {$format} format!");
    }
}
