<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;
use function Differ\Differ\parseJson;

class DifferTest extends TestCase
{
    public function testParseValidJson(): void
    {
        $jsonContent = file_get_contents(__DIR__ . '/fixtures/file1.json');
        $this->assertNotFalse($jsonContent);

        $expected = new \stdClass();
        $expected->host = 'hexlet.io';
        $expected->timeout = 50;
        $expected->proxy = '123.234.53.22';
        $expected->follow = false;
        $data = parseJson($jsonContent);
        $this->assertEquals($expected, $data);

        $jsonContent1 = file_get_contents(__DIR__ . '/fixtures/file2.json');
        $this->assertNotFalse($jsonContent1);

        $expected1 = new \stdClass();
        $expected1->timeout = 20;
        $expected1->verbose = true;
        $expected1->host = 'hexlet.io';
        $data = parseJson($jsonContent1);
        $this->assertEquals($expected1, $data);
    }

    public function testParseInvalidJson(): void
    {
        $jsonContent = file_get_contents(__DIR__ . '/fixtures/corrupt_file1.json');
        $this->assertNotFalse($jsonContent);

        $this->expectException(\JsonException::class);
        $data = parseJson($jsonContent);
    }

    public function testGenDiff(): void
    {
        $filePath1 = __DIR__ . '/fixtures/file1.json';
        $filePath2 = __DIR__ . '/fixtures/file2.json';
        $result = genDiff($filePath1, $filePath2);
        $this->assertNotEmpty($result);

        $expected = <<<TEXT
        {
          - follow: false
            host: hexlet.io
          - proxy: 123.234.53.22
          - timeout: 50
          + timeout: 20
          + verbose: true
        }
        TEXT;

        $this->assertEquals($expected, $result);
    }

}
