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
    private string $fixturePath = __DIR__ . '/fixtures/';

    private function getFilePath(string $name): string
    {
        return $this->fixturePath . $name;
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
        $inputJsonFiles = ['file1.json', 'file2.json'];
        $inputYmlFiles = ['file1.yml', 'file2.yml'];

        return [
            ['expected1.txt', 'stylish', $inputJsonFiles],
            ['expected2.txt', 'plain',   $inputJsonFiles],
            ['expected3.txt', 'json',    $inputJsonFiles],
            ['expected1.txt', 'stylish', $inputYmlFiles],
            ['expected2.txt', 'plain',   $inputYmlFiles],
            ['expected3.txt', 'json',    $inputYmlFiles],
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
