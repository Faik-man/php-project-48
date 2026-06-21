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
    private const INPUT_JSON_FILES = ['file1.json', 'file2.json'];
    private const INPUT_YML_FILES = ['file1.yml', 'file2.yml'];

    private string $fixturePath = __DIR__ . '/fixtures/';

    private function getFilePath(string $name): string
    {
        return $this->fixturePath . $name;
    }

    #[DataProvider('genDiffDefaultProvider')]
    public function testGenDiffDefault(array $inputFilenames): void
    {
        [$firstInputFilename, $secondInputFilename] = $inputFilenames;
        $filePath1 = $this->getFilePath($firstInputFilename);
        $filePath2 = $this->getFilePath($secondInputFilename);
        $actual = genDiff($filePath1, $filePath2);

        $this->assertStringEqualsFile($this->getFilePath('expected1.txt'), $actual);
    }

    public static function genDiffDefaultProvider(): array
    {
        return [
            [self::INPUT_JSON_FILES],
            [self::INPUT_YML_FILES]
        ];
    }

    #[DataProvider('genDiffProvider')]
    public function testGenDiff(string $expectedFilename, string $format, array $inputFilenames): void
    {
        [$firstInputFilename, $secondInputFilename] = $inputFilenames;
        $filePath1 = $this->getFilePath($firstInputFilename);
        $filePath2 = $this->getFilePath($secondInputFilename);
        $actual = genDiff($filePath1, $filePath2, $format);

        $this->assertStringEqualsFile($this->getFilePath($expectedFilename), $actual);
    }

    public static function genDiffProvider(): array
    {
        return [
            ['expected1.txt', 'stylish', self::INPUT_JSON_FILES],
            ['expected2.txt', 'plain',   self::INPUT_JSON_FILES],
            ['expected3.txt', 'json',    self::INPUT_JSON_FILES],
            ['expected1.txt', 'stylish', self::INPUT_YML_FILES],
            ['expected2.txt', 'plain',   self::INPUT_YML_FILES],
            ['expected3.txt', 'json',    self::INPUT_YML_FILES],
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
