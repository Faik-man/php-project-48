<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;
use function Differ\Differ\buildDiff;
use function Differ\Differ\normalizePath;
use function Differ\Parsers\getParser;
use function Differ\Differ\getFileContent;
use function Differ\Formatters\getFormatter;
use Differ\Parsers\JsonParser;
use Differ\Parsers\YamlParser;

class DifferTest extends TestCase
{
    private string $path = __DIR__ . '/fixtures/';

    private function getFilePath(string $name): string
    {
        return $this->path . $name;
    }

    public function testNormalizeFilePath(): void
    {
        $normalizedFilePath1 = normalizePath($this->getFilePath('file1.json'));

        chdir(__DIR__);
        $normalizedFilePath2 = normalizePath('./fixtures/file1.json');

        chdir(__DIR__ . '/../src');
        $normalizedFilePath3 = normalizePath('../tests/fixtures/file1.json');

        $expected = $this->getFilePath('file1.json');
        $this->assertEquals($expected, $normalizedFilePath1);
        $this->assertEquals($expected, $normalizedFilePath2);
        $this->assertEquals($expected, $normalizedFilePath3);
    }

    public function testParseValidJson(): void
    {
        $jsonContent =  file_get_contents($this->getFilePath('file1.json'));
        $this->assertNotFalse($jsonContent);

        $expected = new \stdClass();
        $expected->host = 'hexlet.io';
        $expected->timeout = 50;
        $expected->proxy = '123.234.53.22';
        $expected->follow = false;
        $data = JsonParser::parse($jsonContent);
        $this->assertEquals($expected, $data);

        $jsonContent1 = file_get_contents($this->getFilePath('file2.json'));
        $this->assertNotFalse($jsonContent1);

        $expected1 = new \stdClass();
        $expected1->timeout = 20;
        $expected1->verbose = true;
        $expected1->host = 'hexlet.io';
        $data = JsonParser::parse($jsonContent1);
        $this->assertEquals($expected1, $data);
    }

    public function testParseValidNestedJson(): void
    {
        $jsonContent = file_get_contents($this->getFilePath('nested_file1.json'));
        $this->assertNotFalse($jsonContent);

        $doge = new \stdClass();
        $doge->wow = '';

        $setting6 = new \stdClass();
        $setting6->key = 'value';
        $setting6->doge = $doge;

        $common = new \stdClass();
        $common->setting1 = 'Value 1';
        $common->setting2 = 200;
        $common->setting3 = true;
        $common->setting6 = $setting6;

        $data = JsonParser::parse($jsonContent);
        $this->assertEquals($common, $data->common);
    }

    public function testParseInvalidJson(): void
    {
        $jsonContent = file_get_contents($this->getFilePath('corrupt_file1.json'));
        $this->assertNotFalse($jsonContent);

        $this->expectException(\JsonException::class);
        JsonParser::parse($jsonContent);
    }

    public function testGenDiffJsonFormatStylish(): void
    {
        $filePath1 = $this->getFilePath('nested_file1.json');
        $filePath2 = $this->getFilePath('nested_file2.json');

        $formatResult = genDiff($filePath1, $filePath2);

        $expected = file_get_contents($this->getFilePath('stylish2.txt'));

        $this->assertEquals($expected, $formatResult);
    }

    public function testGenDiffJsonFormatPlain(): void
    {
        $filePath1 = $this->getFilePath('nested_file1.json');
        $filePath2 = $this->getFilePath('nested_file2.json');

        $result = genDiff($filePath1, $filePath2, 'plain');

        $expected = file_get_contents($this->getFilePath('plain1.txt'));

        $this->assertEquals($expected, $result);
    }

    public function testGenDiffJsonFormatJson(): void
    {
        $filePath1 = $this->getFilePath('nested_file1.json');
        $filePath2 = $this->getFilePath('nested_file2.json');

        $actual = genDiff($filePath1, $filePath2, 'json');

        $expected = file_get_contents($this->getFilePath('nested_json1.txt'));
        $this->assertEquals($expected, $actual);
    }

    public function testParseValidYaml(): void
    {
        $yamlContent = file_get_contents($this->getFilePath('file1.yml'));
        $this->assertNotFalse($yamlContent);

        $expected = new \stdClass();
        $expected->host = 'hexlet.io';
        $expected->timeout = 50;
        $expected->proxy = '123.234.53.22';
        $expected->follow = false;
        $data = YamlParser::parse($yamlContent);
        $this->assertEquals($expected, $data);

        $yamlContent1 = file_get_contents($this->getFilePath('file2.yml'));
        $this->assertNotFalse($yamlContent1);

        $expected1 = new \stdClass();
        $expected1->timeout = 20;
        $expected1->verbose = true;
        $expected1->host = 'hexlet.io';
        $data = YamlParser::parse($yamlContent1);
        $this->assertEquals($expected1, $data);
    }

    public function testParseInvalidYaml(): void
    {
        $jsonContent = file_get_contents($this->getFilePath('corrupt_file1.yml'));
        $this->assertNotFalse($jsonContent);

        $this->expectException(\Exception::class);
        YamlParser::parse($jsonContent);
    }

    public function testGenDiffYamlFormatStylish(): void
    {
        $filePath1 = $this->getFilePath('nested_file1.yml');
        $filePath2 = $this->getFilePath('nested_file2.yml');
        $result = genDiff($filePath1, $filePath2);
        $this->assertNotEmpty($result);

        $expected = file_get_contents($this->getFilePath('stylish2.txt'));

        $this->assertEquals($expected, $result);
    }

    public function testGenDiffYamlFormatPlain(): void
    {
        $filePath1 = $this->getFilePath('nested_file1.yml');
        $filePath2 = $this->getFilePath('nested_file2.yml');
        $result = genDiff($filePath1, $filePath2, 'plain');
        $this->assertNotEmpty($result);

        $expected = file_get_contents($this->getFilePath('plain1.txt'));

        $this->assertEquals($expected, $result);
    }

    public function testGenDiffYamlFormatJson(): void
    {
        $filePath1 = $this->getFilePath('nested_file1.yml');
        $filePath2 = $this->getFilePath('nested_file2.yml');

        $actual = genDiff($filePath1, $filePath2, 'json');
        $expected = file_get_contents($this->getFilePath('nested_json1.txt'));

        $this->assertEquals($expected, $actual);
    }

    public function testGetUndefinedParser(): void
    {
        $this->expectException(\Exception::class);
        getParser('xml', 'xml');
    }

    public function testGetUndefinedFormatter(): void
    {
        $this->expectException(\Exception::class);
        getFormatter('blah blah');
    }

    public function testGetContentOfNotExistsFile(): void
    {
        $this->expectException(\Exception::class);
        getFileContent($this->getFilePath('random_file.json'));
    }
}
