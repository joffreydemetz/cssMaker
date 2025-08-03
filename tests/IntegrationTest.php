<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\CssMaker;
use JDZ\CssMaker\Exception\LessMakerException;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for the CssMaker library
 * 
 * @covers \JDZ\CssMaker\CssMaker
 * @covers \JDZ\CssMaker\Merger
 * @covers \JDZ\CssMaker\Variables
 * @covers \JDZ\CssMaker\Output
 */
class IntegrationTest extends TestCase
{
    private CssMaker $cssMaker;
    private string $testTmpDir;
    private string $testTargetDir;
    private string $fixturesDir;

    protected function setUp(): void
    {
        $this->testTmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cssmaker_integration_test_' . uniqid();
        $this->testTargetDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cssmaker_target_' . uniqid();
        $this->fixturesDir = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures';

        mkdir($this->testTmpDir, 0777, true);
        mkdir($this->testTargetDir, 0777, true);
        mkdir($this->testTargetDir . DIRECTORY_SEPARATOR . 'css', 0777, true);
        mkdir($this->testTargetDir . DIRECTORY_SEPARATOR . 'fonts', 0777, true);

        $this->cssMaker = new CssMaker();
    }

    protected function tearDown(): void
    {
        $this->cssMaker->clean();
        $this->removeDirectory($this->testTmpDir);
        $this->removeDirectory($this->testTargetDir);
    }

    public function testCompleteWorkflow(): void
    {
        // Set up the CssMaker instance
        $this->cssMaker
            ->setBasePath($this->testTargetDir)
            ->setTmpPath($this->testTmpDir)
            ->setTargetCssPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'css')
            ->setTargetFontPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'fonts')
            ->setDumpOutputPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'output.log');

        // Add LESS files using the test fixtures
        $lessFiles = [
            'variables' => [
                $this->fixturesDir . DIRECTORY_SEPARATOR . 'variables.yml'
            ],
            'mixins' => [
                $this->fixturesDir . DIRECTORY_SEPARATOR . 'mixins.less'
            ],
            'normalize' => [
                $this->fixturesDir . DIRECTORY_SEPARATOR . 'normalize.less'
            ],
            'structure' => [
                $this->fixturesDir . DIRECTORY_SEPARATOR . 'structure.less'
            ],
            'fonts' => [],
            'icons' => [],
            'mobile' => [],
            'screen' => [],
            'queries' => [],
            'print' => []
        ];

        $result = $this->cssMaker->addLessFiles($lessFiles);

        $this->assertInstanceOf(CssMaker::class, $result);
        $this->assertSame($this->cssMaker, $result);
    }

    public function testWorkflowWithEmptyArrays(): void
    {
        $this->cssMaker
            ->setBasePath($this->testTargetDir)
            ->setTmpPath($this->testTmpDir)
            ->setTargetCssPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'css')
            ->setTargetFontPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'fonts');

        $result = $this->cssMaker->addLessFiles([]);

        $this->assertInstanceOf(CssMaker::class, $result);
    }

    public function testWorkflowWithNonExistentFiles(): void
    {
        $this->cssMaker
            ->setBasePath($this->testTargetDir)
            ->setTmpPath($this->testTmpDir)
            ->setTargetCssPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'css')
            ->setTargetFontPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'fonts');

        $lessFiles = [
            'variables' => ['/nonexistent/variables.yml'],
            'mixins' => ['/nonexistent/mixins.less'],
            'structure' => ['/nonexistent/structure.less']
        ];

        // Should not throw an exception, should handle gracefully
        $result = $this->cssMaker->addLessFiles($lessFiles);
        $this->assertInstanceOf(CssMaker::class, $result);
    }

    public function testSetBuildPathsIntegration(): void
    {
        $buildBaseDir = $this->testTargetDir . DIRECTORY_SEPARATOR . 'build_integration';
        mkdir($buildBaseDir, 0777, true);
        mkdir($buildBaseDir . DIRECTORY_SEPARATOR . 'tmp', 0777, true);
        mkdir($buildBaseDir . DIRECTORY_SEPARATOR . 'build', 0777, true);
        mkdir($buildBaseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'css', 0777, true);
        mkdir($buildBaseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'fonts', 0777, true);

        $result = $this->cssMaker->setBuildPaths($buildBaseDir, 'build');
        $this->assertInstanceOf(CssMaker::class, $result);
    }

    public function testChainedOperations(): void
    {
        $lessFiles = [
            'variables' => [$this->fixturesDir . DIRECTORY_SEPARATOR . 'variables.yml'],
            'mixins' => [$this->fixturesDir . DIRECTORY_SEPARATOR . 'mixins.less']
        ];

        $result = $this->cssMaker
            ->setBasePath($this->testTargetDir)
            ->setTmpPath($this->testTmpDir)
            ->setTargetCssPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'css')
            ->setTargetFontPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'fonts')
            ->setDumpOutputPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'output.log')
            ->addLessFiles($lessFiles)
            ->clean();

        $this->assertInstanceOf(CssMaker::class, $result);
        $this->assertSame($this->cssMaker, $result);
    }

    public function testErrorHandling(): void
    {
        // Test setting invalid paths should throw exceptions
        $invalidPath = '/absolutely/nonexistent/path/that/should/not/exist';

        $this->expectException(LessMakerException::class);
        $this->cssMaker->setBasePath($invalidPath);
    }

    public function testMultipleErrorTypes(): void
    {
        $invalidPaths = [
            'setBasePath',
            'setTmpPath',
            'setTargetCssPath',
            'setTargetFontPath'
        ];

        foreach ($invalidPaths as $method) {
            try {
                $this->cssMaker->$method('/invalid/path');
                $this->fail("Expected LessMakerException for method: $method");
            } catch (LessMakerException $e) {
                $this->assertStringContainsString('does not exist', $e->getMessage());
            }
        }
    }

    public function testProcessingRequiredPaths(): void
    {
        // Test that processing without all required paths throws exception
        $this->expectException(LessMakerException::class);
        $this->expectExceptionMessage('Base, tmp, target CSS or target font paths are not set');

        $this->cssMaker->process('test-theme');
    }

    public function testTmpFileManagement(): void
    {
        // Create some temporary files
        $tmpFile1 = $this->testTmpDir . DIRECTORY_SEPARATOR . 'temp1.less';
        $tmpFile2 = $this->testTmpDir . DIRECTORY_SEPARATOR . 'temp2.less';

        file_put_contents($tmpFile1, '.temp1 { color: red; }');
        file_put_contents($tmpFile2, '.temp2 { color: blue; }');

        $this->cssMaker
            ->addTmpFile($tmpFile1)
            ->addTmpFile($tmpFile2);

        // Verify files exist
        $this->assertFileExists($tmpFile1);
        $this->assertFileExists($tmpFile2);

        // Clean should remove them
        $this->cssMaker->clean();

        $this->assertFileDoesNotExist($tmpFile1);
        $this->assertFileDoesNotExist($tmpFile2);
    }

    public function testRealWorldScenario(): void
    {
        // Simulate a real-world CSS building scenario

        // 1. Set up paths
        $this->cssMaker
            ->setBasePath($this->testTargetDir)
            ->setTmpPath($this->testTmpDir)
            ->setTargetCssPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'css')
            ->setTargetFontPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'fonts')
            ->setDumpOutputPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'output.log');

        // 2. Add variables and mixins
        $lessFiles = [
            'variables' => [$this->fixturesDir . DIRECTORY_SEPARATOR . 'variables.yml'],
            'mixins' => [$this->fixturesDir . DIRECTORY_SEPARATOR . 'mixins.less'],
            'normalize' => [$this->fixturesDir . DIRECTORY_SEPARATOR . 'normalize.less'],
            'structure' => [$this->fixturesDir . DIRECTORY_SEPARATOR . 'structure.less'],
            'fonts' => [],
            'icons' => [],
            'mobile' => [],
            'screen' => [],
            'queries' => [],
            'print' => []
        ];

        $this->cssMaker->addLessFiles($lessFiles);

        // 3. Add some temporary files
        $tmpCustomFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'custom.less';
        file_put_contents($tmpCustomFile, '.custom { padding: 20px; }');
        $this->cssMaker->addTmpFile($tmpCustomFile);

        // 4. Clean up
        $this->cssMaker->clean();

        // Verify temporary file was cleaned up
        $this->assertFileDoesNotExist($tmpCustomFile);

        // If we get here without exceptions, the workflow completed successfully
        $this->assertTrue(true);
    }

    public function testAllFileTypes(): void
    {
        $this->cssMaker
            ->setBasePath($this->testTargetDir)
            ->setTmpPath($this->testTmpDir)
            ->setTargetCssPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'css')
            ->setTargetFontPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'fonts');

        // Test all supported file types
        $fileTypes = [
            'variables',
            'mixins',
            'normalize',
            'animations',
            'fonts',
            'structure',
            'icons',
            'mobile',
            'screen',
            'queries',
            'print'
        ];

        $lessFiles = [];
        foreach ($fileTypes as $type) {
            if ($type === 'variables') {
                // Variables use YAML files
                $file = $this->testTmpDir . DIRECTORY_SEPARATOR . $type . '.yml';
                file_put_contents($file, "test-{$type}: 'value'");
            } else {
                // Other types use LESS files
                $file = $this->testTmpDir . DIRECTORY_SEPARATOR . $type . '.less';
                file_put_contents($file, ".{$type} { content: '{$type}'; }");
            }
            $lessFiles[$type] = [$file];
        }

        $result = $this->cssMaker->addLessFiles($lessFiles);
        $this->assertInstanceOf(CssMaker::class, $result);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
