<?php

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;
use function Differ\Differ\normalizePath;
use function Differ\Differ\createNode;
use function Differ\Differ\getParser;
use function Differ\Differ\getFileContent;
use Differ\Formatters\Stylish;
use Differ\Parsers\Json;
use Differ\Parsers\Yaml;

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
        $data = Json\parse($jsonContent);
        $this->assertEquals($expected, $data);

        $jsonContent1 = file_get_contents($this->getFilePath('file2.json'));
        $this->assertNotFalse($jsonContent1);

        $expected1 = new \stdClass();
        $expected1->timeout = 20;
        $expected1->verbose = true;
        $expected1->host = 'hexlet.io';
        $data = Json\parse($jsonContent1);
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

        $data = Json\parse($jsonContent);
        $this->assertEquals($common, $data->common);
    }

    public function testParseInvalidJson(): void
    {
        $jsonContent = file_get_contents($this->getFilePath('corrupt_file1.json'));
        $this->assertNotFalse($jsonContent);

        $this->expectException(\JsonException::class);
        Json\parse($jsonContent);
    }

    public function testGenDiffJson(): void
    {
        $filePath1 = $this->getFilePath('nested_file1.json');
        $filePath2 = $this->getFilePath('nested_file2.json');

        $result = genDiff($filePath1, $filePath2);

        $group2 = (function (): object {
            $deep = new \stdClass();
            $deep->id = 45;
            $group2 = new \stdClass();
            $group2->abc = 12345;
            $group2->deep = $deep;
            return $group2;
        })();

        $group3 = (function (): object {
            $id = new \stdClass();
            $id->number = 45;
            $deep = new \stdClass();
            $deep->id = $id;
            $result = new \stdClass();
            $result->deep = $deep;
            $result->fee = 100500;

            return $result;
        })();

        $nest = new \stdClass();
        $nest->key = 'value';

        $setting5 = new \stdClass();
        $setting5->key5 = 'value5';

        $expected = [
            'common' => createNode('', ' ', [
                'follow' => createNode(false, '+'),
                'setting1' => createNode('Value 1', ' '),
                'setting2' => createNode(200, '-'),
                'setting3' => createNode([true, null], '-+'),
                'setting4' => createNode('blah blah', '+'),
                'setting5' => createNode($setting5, '+'),
                'setting6' => createNode('', ' ', [
                    'doge' => createNode('', ' ', [
                        'wow' => createNode(['', 'so much'], '-+')
                    ]),
                    'key' => createNode('value', ' '),
                    'ops' => createNode('vops', '+'),
                ]),
            ]),
            'group1' => createNode('', ' ', [
                'baz' => createNode(['bas', 'bars'], '-+'),
                'foo' => createNode('bar', ' '),
                'nest' => createNode([$nest, 'str'], '-+'),
            ]),
            'group2' => createNode($group2, '-'),
            'group3' => createNode($group3, '+'),
        ];

        $this->assertEquals($expected, $result);

        $nest = [
            'nest' => createNode([$nest, 'str'], '-+')
        ];

        $expected = file_get_contents($this->getFilePath('stylish.txt'));

        $this->assertEquals(
            $expected,
            Stylish\format($nest)
        );

        $nest = [
            'nest' => createNode('', ' ', [
                'key' => createNode('value', '-')
            ])
        ];

        $expected = file_get_contents($this->getFilePath('stylish1.txt'));

        $this->assertEquals(
            $expected,
            Stylish\format($nest)
        );

        $formatResult = Stylish\format($result);
        $this->assertNotEmpty($formatResult);

        $expected = file_get_contents($this->getFilePath('stylish2.txt'));

        $this->assertEquals($expected, $formatResult);
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
        $data = Yaml\parse($yamlContent);
        $this->assertEquals($expected, $data);

        $yamlContent1 = file_get_contents($this->getFilePath('file2.yml'));
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
        $jsonContent = file_get_contents($this->getFilePath('corrupt_file1.yml'));
        $this->assertNotFalse($jsonContent);

        $this->expectException(\Exception::class);
        Yaml\parse($jsonContent);
    }

    public function testGenDiffYaml(): void
    {
        $filePath1 = $this->getFilePath('nested_file1.yml');
        $filePath2 = $this->getFilePath('nested_file2.yml');
        $result = genDiff($filePath1, $filePath2);
        $this->assertNotEmpty($result);

        $expected = file_get_contents($this->getFilePath('stylish2.txt'));

        $formatResult = Stylish\format($result);
        $this->assertEquals($expected, $formatResult);
    }

    public function testGetUndefinedParser(): void
    {
        $this->expectException(\Exception::class);
        getParser('xml', 'xml');
    }

    public function testGetContentOfNotExistsFile(): void
    {
        $this->expectException(\Exception::class);
        getFileContent($this->getFilePath('random_file.json'));
    }
}
