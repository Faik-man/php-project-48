<?php

namespace Differ\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;
use function Differ\Differ\buildDiff;
use function Differ\Differ\normalizePath;
use function Differ\Parsers\getParser;
use function Differ\Differ\getFileContent;
use function Differ\Formatters\getFormatter;

class DifferTest extends TestCase
{
    private array $corrupts = [];
    private array $expected = [];
    private string $path = __DIR__ . '/fixtures/';

    public function setUp(): void
    {
        $corrupts = (string)file_get_contents($this->getFilePath('corrupts.txt'));
        $this->corrupts = explode("\n\n\n", $corrupts);

        $expected = (string)file_get_contents($this->getFilePath('expected.txt'));
        $this->expected = explode("\n\n\n", $expected);
    }

    private function getFilePath(string $name): string
    {
        return $this->path . $name;
    }

    public function testNormalizeFilePath(): void
    {
        $normalizedFilePath1 = normalizePath($this->getFilePath('expected.json'));

        chdir(__DIR__);
        $normalizedFilePath2 = normalizePath('./fixtures/expected.json');

        chdir(__DIR__ . '/../src');
        $normalizedFilePath3 = normalizePath('../tests/fixtures/expected.json');

        $expected = $this->getFilePath('expected.json');
        $this->assertEquals($expected, $normalizedFilePath1);
        $this->assertEquals($expected, $normalizedFilePath2);
        $this->assertEquals($expected, $normalizedFilePath3);
    }

    #[DataProvider('corruptProvider')]
    public function testParseInvalidFiles(int $caseIndex, string $fileExtension): void
    {
        $this->expectException(\Exception::class);

        $parser = getParser($fileExtension);
        $parser::parse($this->corrupts[$caseIndex]);
    }

    public static function corruptProvider(): array
    {
        return [
            [0, 'json'],
            [1, 'yml']
        ];
    }

    #[DataProvider('jsonProvider')]
    public function testGenDiffJson(array $filenames, string $format, int $caseIndex): void
    {
        $filePath1 = $this->getFilePath($filenames[0]);
        $filePath2 = $this->getFilePath($filenames[1]);

        $actual = genDiff($filePath1, $filePath2, $format);

        $this->assertEquals($this->expected[$caseIndex], $actual);
    }

    public static function jsonProvider(): array
    {
        return [
            [['file1.json', 'file2.json'], 'stylish', 0],
            [['file1.json', 'file2.json'], 'plain', 1],
            [['file1.json', 'file2.json'], 'json', 2]
        ];
    }

    public static function ymlProvider(): array
    {
        return [
            [['file1.yml', 'file2.yml'], 'stylish', 0],
            [['file1.yml', 'file2.yml'], 'plain', 1],
            [['file1.yml', 'file2.yml'], 'json', 2]
        ];
    }

    #[DataProvider('ymlProvider')]
    public function testGenDiffYaml(array $filenames, string $format, int $caseIndex): void
    {
        $filePath1 = $this->getFilePath($filenames[0]);
        $filePath2 = $this->getFilePath($filenames[1]);
        $actual = genDiff($filePath1, $filePath2, $format);

        $this->assertEquals($this->expected[$caseIndex], $actual);
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

    public function testGenDiffUnmatchedParsers(): void
    {
        $filePath1 = $this->getFilePath('file1.json');
        $filePath2 = $this->getFilePath('file1.yml');

        $this->expectException(\Exception::class);
        genDiff($filePath1, $filePath2);
    }
}
