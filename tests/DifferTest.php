<?php

namespace Differ\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Differ\Differ\genDiff;
use function Differ\Differ\buildDiff;
use function Differ\Parsers\getParser;
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

    #[DataProvider('genDiffProvider')]
    public function testGenDiffJson(string $format, int $caseIndex): void
    {
        $filePath1 = $this->getFilePath('file1.json');
        $filePath2 = $this->getFilePath('file2.json');

        $actual = genDiff($filePath1, $filePath2, $format);

        $this->assertEquals($this->expected[$caseIndex], $actual);
    }

    #[DataProvider('genDiffProvider')]
    public function testGenDiffYaml(string $format, int $caseIndex): void
    {
        $filePath1 = $this->getFilePath('file1.yml');
        $filePath2 = $this->getFilePath('file2.yml');
        $actual = genDiff($filePath1, $filePath2, $format);

        $this->assertEquals($this->expected[$caseIndex], $actual);
    }

    public static function genDiffProvider(): array
    {
        return [
            ['stylish', 0],
            ['plain', 1],
            ['json', 2]
        ];
    }

    public function testGetUndefinedParser(): void
    {
        $this->expectException(\Exception::class);
        getParser('xml');
    }

    public function testGetUndefinedFormatter(): void
    {
        $this->expectException(\Exception::class);
        getFormatter('blah blah');
    }

    public function testGetContentOfNotExistsFile(): void
    {
        $this->expectException(\Exception::class);
        genDiff($this->getFilePath('random_file1.json'), $this->getFilePath('random_file2.json'));
    }
}
