<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\CssMaker;
use PHPUnit\Framework\TestCase;
use JDZ\CssMaker\Tests\Helper;

/**
 * @covers \JDZ\CssMaker\CssMaker
 */
class CssMakerTest extends TestCase
{
    public function testConstructor(): void
    {
        $cssMaker = new CssMaker();

        $this->assertInstanceOf(CssMaker::class, $cssMaker);
    }

    public function testSetBuildPaths(): void
    {
        $tempDir = Helper::createTempStructure('build');

        $cssMaker = new CssMaker();

        try {
            // Test setBuildPaths with default target 'build'
            $result = $cssMaker->setBuildPaths($tempDir, 'build');

            // Verify fluent interface
            $this->assertSame($cssMaker, $result, 'setBuildPaths should return self for fluent interface');
        } finally {
            Helper::removeDirectory($tempDir);
        }
    }

    public function testProcessGeneratesConsistentLess(): void
    {
        $cssMaker = new CssMaker();

        $tempDir = Helper::createTempStructure('build');

        // Create test LESS files with known content
        $mixinFile = $tempDir . DIRECTORY_SEPARATOR . 'mixins.less';
        $structureFile = $tempDir . DIRECTORY_SEPARATOR . 'structure.less';
        $variablesFile = $tempDir . DIRECTORY_SEPARATOR . 'variables.yml';

        // Create LESS content that should be processable
        file_put_contents($mixinFile, '.test-mixin() { color: red; }');
        file_put_contents($structureFile, '.test-class { .test-mixin(); font-size: 14px; }');
        file_put_contents($variablesFile, "test_color: blue\ntest_size: 16px");

        try {
            // Set up CssMaker with build paths
            $cssMaker->setBuildPaths($tempDir, 'build');

            // Add LESS files
            $paths = [
                'variables' => [$variablesFile],
                'mixins' => [$mixinFile],
                'structure' => [$structureFile],
            ];

            $cssMaker->addLessFiles($paths);

            // Mock the external processes since we can't rely on lessc, postcss, etc. being available
            // Instead, we'll test that the LESS file is generated correctly and simulate the process

            // Use reflection to call toLess directly to test LESS generation
            $reflection = new \ReflectionClass($cssMaker);
            $toLessMethod = $reflection->getMethod('toLess');
            $toLessMethod->setAccessible(true);

            $lessFilePath = $tempDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'test.less';

            // Generate the LESS file
            $toLessMethod->invoke($cssMaker, $lessFilePath);

            // Verify the LESS file was created
            $this->assertFileExists($lessFilePath, 'LESS file should be generated');

            // Read the generated LESS content
            $lessContent = file_get_contents($lessFilePath);

            // Verify that the LESS content contains our test files' content
            $this->assertStringContainsString('.test-mixin()', $lessContent, 'Generated LESS should contain mixin content');
            $this->assertStringContainsString('.test-class', $lessContent, 'Generated LESS should contain structure content');
            $this->assertStringContainsString('font-size: 14px', $lessContent, 'Generated LESS should contain specific CSS rules');

            // Verify variables were processed (they get converted to LESS variables)
            $this->assertStringContainsString('@test_color: blue;', $lessContent, 'Generated LESS should contain processed variables');
            $this->assertStringContainsString('@test_size: 16px;', $lessContent, 'Generated LESS should contain processed variables');

            // Verify the content order follows the expected structure
            $mixinPos = strpos($lessContent, '.test-mixin()');
            $structurePos = strpos($lessContent, '.test-class');

            $this->assertNotFalse($mixinPos, 'Mixin content should be found');
            $this->assertNotFalse($structurePos, 'Structure content should be found');

            // Variables should come before mixins, mixins before structure
            $variablePos = strpos($lessContent, '@test_color');
            $this->assertLessThan($mixinPos, $variablePos, 'Variables should come before mixins');
            $this->assertLessThan($structurePos, $mixinPos, 'Mixins should come before structure');
        } finally {
            Helper::removeDirectory($tempDir);
        }
    }
}
