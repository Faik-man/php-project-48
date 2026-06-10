<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;
use function Differ\Differ\normalizePath;
use function Differ\Differ\getParser;
use function Differ\Differ\getFileContent;
use Differ\Parsers\Json;
use Differ\Parsers\Yaml;

class DifferTest extends TestCase
{
    public function testNormalizeFilePath(): void
    {
        $normalizedFilePath1 = normalizePath(__DIR__ . '/fixtures/file1.json');

        chdir(__DIR__);
        $normalizedFilePath2 = normalizePath('./fixtures/file1.json');

        chdir(__DIR__ . '/../src');
        $normalizedFilePath3 = normalizePath('../tests/fixtures/file1.json');

        $expected = __DIR__ . '/fixtures/file1.json';
        $this->assertEquals($expected, $normalizedFilePath1);
        $this->assertEquals($expected, $normalizedFilePath2);
        $this->assertEquals($expected, $normalizedFilePath3);
    }

    public function testParseValidJson(): void
    {
        $jsonContent = file_get_contents(__DIR__ . '/fixtures/file1.json');
        $this->assertNotFalse($jsonContent);

        $expected = new \stdClass();
        $expected->host = 'hexlet.io';
        $expected->timeout = 50;
        $expected->proxy = '123.234.53.22';
        $expected->follow = false;
        $data = Json\parse($jsonContent);
        $this->assertEquals($expected, $data);

        $jsonContent1 = file_get_contents(__DIR__ . '/fixtures/file2.json');
        $this->assertNotFalse($jsonContent1);

        $expected1 = new \stdClass();
        $expected1->timeout = 20;
        $expected1->verbose = true;
        $expected1->host = 'hexlet.io';
        $data = Json\parse($jsonContent1);
        $this->assertEquals($expected1, $data);
    }

    public function testParseInvalidJson(): void
    {
        $jsonContent = file_get_contents(__DIR__ . '/fixtures/corrupt_file1.json');
        $this->assertNotFalse($jsonContent);

        $this->expectException(\JsonException::class);
        Json\parse($jsonContent);
    }

    public function testGenDiffJson(): void
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

    public function testParseValidYaml(): void
    {
        $yamlContent = file_get_contents(__DIR__ . '/fixtures/file1.yml');
        $this->assertNotFalse($yamlContent);

        $expected = new \stdClass();
        $expected->host = 'hexlet.io';
        $expected->timeout = 50;
        $expected->proxy = '123.234.53.22';
        $expected->follow = false;
        $data = Yaml\parse($yamlContent);
        $this->assertEquals($expected, $data);

        $yamlContent1 = file_get_contents(__DIR__ . '/fixtures/file2.yml');
        $this->assertNotFalse($yamlContent1);

        $expected1 = new \stdClass();
        $expected1->timeout = 20;
        $expected1->verbose = true;
        $expected1->host = 'hexlet.io';
        $data = Yaml\parse($yamlContent1);
        $this->assertEquals($expected1, $data);
    }

    public function testParseInvalidYaml(): void
    {
        $jsonContent = file_get_contents(__DIR__ . '/fixtures/corrupt_file1.yml');
        $this->assertNotFalse($jsonContent);

        $this->expectException(\Exception::class);
        Yaml\parse($jsonContent);
    }

    public function testGenDiffYaml(): void
    {
        $filePath1 = __DIR__ . '/fixtures/file1.yml';
        $filePath2 = __DIR__ . '/fixtures/file2.yml';
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

    public function testGetUndefinedParser(): void
    {
        $this->expectException(\Exception::class);
        getParser('xml', 'xml');
    }

    public function testGetContentOfNotExistsFile(): void
    {
        $this->expectException(\Exception::class);
        getFileContent(__DIR__ . '/fixtures/random_file.json');
    }
}
