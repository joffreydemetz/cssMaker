<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Merger;
use PHPUnit\Framework\TestCase;
use JDZ\CssMaker\Tests\Helper;

/**
 * @covers \JDZ\CssMaker\Merger
 */
class MergerTest extends TestCase
{
    private Merger $merger;
    private string $testTmpDir;

    protected function setUp(): void
    {
        $this->merger = new Merger();
        $this->testTmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cssmaker_test_' . uniqid();
        mkdir($this->testTmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        Helper::removeDirectory($this->testTmpDir);
    }

    public function testGetContentEmpty(): void
    {
        $content = $this->merger->getContent();
        $this->assertEquals('', $content);
    }

    public function testSetVariable(): void
    {
        $result = $this->merger->setVariable('primary-color', '#007bff');
        $this->assertInstanceOf(Merger::class, $result);
        $this->assertSame($this->merger, $result);

        $content = $this->merger->getContent();
        $this->assertStringContainsString('@primary-color: #007bff;', $content);
    }

    public function testSetVariableWithEmptyValue(): void
    {
        $this->merger->setVariable('empty-var', '');
        $content = $this->merger->getContent();
        $this->assertStringContainsString('@empty-var: "";', $content);
    }

    public function testSetVariables(): void
    {
        $variables = [
            'primary-color' => '#007bff',
            'secondary-color' => '#6c757d',
            'font-size' => '14px'
        ];

        $result = $this->merger->setVariables($variables);
        $this->assertInstanceOf(Merger::class, $result);

        $content = $this->merger->getContent();
        $this->assertStringContainsString('@primary-color: #007bff;', $content);
        $this->assertStringContainsString('@secondary-color: #6c757d;', $content);
        $this->assertStringContainsString('@font-size: 14px;', $content);
    }

    public function testSetMixin(): void
    {
        $mixinFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'mixins.less';
        file_put_contents($mixinFile, '.border-radius(@radius) { border-radius: @radius; }');
        $this->merger->setMixins([$mixinFile]);

        $result = $this->merger->setMixin($mixinFile);
        $this->assertInstanceOf(Merger::class, $result);

        $content = $this->merger->getContent();
        $this->assertStringContainsString('.border-radius(@radius) { border-radius: @radius; }', $content);
        $this->assertStringContainsString('// ' . $mixinFile, $content);
    }

    public function testSetMixinNonExistent(): void
    {
        $result = $this->merger->setMixin('/nonexistent/file.less');
        $this->assertInstanceOf(Merger::class, $result);

        $content = $this->merger->getContent();
        $this->assertEquals('', $content);
    }

    public function testSetFile(): void
    {
        $lessFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'styles.less';
        file_put_contents($lessFile, '.header { background: @primary-color; font-size: @font-size; }');

        $result = $this->merger->setFile($lessFile);
        $this->assertInstanceOf(Merger::class, $result);

        $content = $this->merger->getContent();
        $this->assertStringContainsString('.header { background: @primary-color; font-size: @font-size; }', $content);
        $this->assertStringContainsString('// ' . $lessFile, $content);
    }

    public function testSetFileNonExistent(): void
    {
        $result = $this->merger->setFile('/nonexistent/file.less');
        $this->assertInstanceOf(Merger::class, $result);

        $content = $this->merger->getContent();
        $this->assertEquals('', $content);
    }

    public function testAddFile(): void
    {
        $lessFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'additional.less';
        file_put_contents($lessFile, '.button { padding: 10px; }');

        $result = $this->merger->addFile($lessFile);
        $this->assertInstanceOf(Merger::class, $result);

        $content = $this->merger->getContent();
        $this->assertStringContainsString('.button { padding: 10px; }', $content);
        $this->assertStringContainsString('// ' . $lessFile, $content);
    }

    public function testAddFileNonExistent(): void
    {
        $result = $this->merger->addFile('/nonexistent/file.less');
        $this->assertInstanceOf(Merger::class, $result);

        $content = $this->merger->getContent();
        $this->assertEquals('', $content);
    }

    public function testAddFileEmpty(): void
    {
        $emptyFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'empty.less';
        file_put_contents($emptyFile, '');

        $result = $this->merger->addFile($emptyFile);
        $this->assertInstanceOf(Merger::class, $result);

        $content = $this->merger->getContent();
        $this->assertEquals('', $content);
    }

    public function testAddFileWithWhitespace(): void
    {
        $whitespaceFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'whitespace.less';
        file_put_contents($whitespaceFile, "   \n\n   ");

        $result = $this->merger->addFile($whitespaceFile);
        $this->assertInstanceOf(Merger::class, $result);

        $content = $this->merger->getContent();
        $this->assertEquals('', $content);
    }

    public function testAddString(): void
    {
        $result = $this->merger->addString('.custom { color: green; }');
        $this->assertInstanceOf(Merger::class, $result);

        $content = $this->merger->getContent();
        $this->assertStringContainsString('.custom { color: green; }', $content);
    }

    public function testAddStringEmpty(): void
    {
        $result = $this->merger->addString('');
        $this->assertInstanceOf(Merger::class, $result);

        $content = $this->merger->getContent();
        $this->assertEquals('', $content);
    }

    public function testAddStringWithWhitespace(): void
    {
        $result = $this->merger->addString("   \n\n   ");
        $this->assertInstanceOf(Merger::class, $result);

        $content = $this->merger->getContent();
        $this->assertEquals('', $content);
    }

    public function testComplexMerging(): void
    {
        // Set variables
        $this->merger->setVariables([
            'primary-color' => '#007bff',
            'font-size' => '14px'
        ]);

        // Create and set mixins
        $mixinFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'mixins.less';
        file_put_contents($mixinFile, '.border-radius(@radius) { border-radius: @radius; }');
        $this->merger->setMixins([$mixinFile]);

        // Create and set files
        $styleFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'styles.less';
        file_put_contents($styleFile, '.header { background: @primary-color; font-size: @font-size; }');
        $this->merger->setFiles([$styleFile]);

        // Add additional content
        $this->merger->addString('.footer { text-align: center; }');

        $content = $this->merger->getContent();

        // Check that variables come first
        $this->assertStringContainsString('@primary-color: #007bff;', $content);
        $this->assertStringContainsString('@font-size: 14px;', $content);

        // Check that mixins are included
        $this->assertStringContainsString('.border-radius(@radius)', $content);

        // Check that files are included
        $this->assertStringContainsString('.header { background: @primary-color; font-size: @font-size; }', $content);

        // Check that additional strings are included
        $this->assertStringContainsString('.footer { text-align: center; }', $content);
    }

    public function testFluentInterface(): void
    {
        $mixinFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'mixin.less';
        $styleFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'style.less';

        file_put_contents($mixinFile, '.mixin() { color: red; }');
        file_put_contents($styleFile, '.style { background: blue; }');

        $result = $this->merger
            ->setVariable('color', 'red')
            ->setMixin($mixinFile)
            ->setFile($styleFile)
            ->addString('.additional { margin: 0; }');

        $this->assertInstanceOf(Merger::class, $result);
        $this->assertSame($this->merger, $result);
    }
}
