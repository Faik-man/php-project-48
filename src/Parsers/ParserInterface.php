<?php

namespace Differ\Parsers;

interface ParserInterface
{
    public static function parse(string $content): \stdClass;
}
