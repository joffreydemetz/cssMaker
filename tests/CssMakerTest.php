<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\CssMaker;
use JDZ\CssMaker\Exception\LessMakerException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @covers \JDZ\CssMaker\CssMaker
 */
class CssMakerTest extends TestCase
{
    private CssMaker $cssMaker;
    private string $testTmpDir;
    private string $testTargetDir;

    protected function setUp(): void
    {
        $this->testTmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cssmaker_test_' . uniqid();
        $this->testTargetDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cssmaker_target_' . uniqid();

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

    public function testConstructor(): void
    {
        $cssMaker = new CssMaker();
        $this->assertInstanceOf(CssMaker::class, $cssMaker);
    }

    public function testSetBasePath(): void
    {
        $result = $this->cssMaker->setBasePath($this->testTargetDir);
        $this->assertInstanceOf(CssMaker::class, $result);
        $this->assertSame($this->cssMaker, $result);
    }

    public function testSetBasePathWithInvalidPath(): void
    {
        $this->expectException(LessMakerException::class);
        $this->expectExceptionMessage('Base directory does not exist');

        $this->cssMaker->setBasePath('/invalid/path/that/does/not/exist');
    }

    public function testSetTmpPath(): void
    {
        $result = $this->cssMaker->setTmpPath($this->testTmpDir);
        $this->assertInstanceOf(CssMaker::class, $result);
        $this->assertSame($this->cssMaker, $result);
    }

    public function testSetTmpPathWithInvalidPath(): void
    {
        $this->expectException(LessMakerException::class);
        $this->expectExceptionMessage('Temporary path does not exist');

        $this->cssMaker->setTmpPath('/invalid/path/that/does/not/exist');
    }

    public function testSetTargetCssPath(): void
    {
        $cssPath = $this->testTargetDir . DIRECTORY_SEPARATOR . 'css';
        $result = $this->cssMaker->setTargetCssPath($cssPath);
        $this->assertInstanceOf(CssMaker::class, $result);
        $this->assertSame($this->cssMaker, $result);
    }

    public function testSetTargetCssPathWithInvalidPath(): void
    {
        $this->expectException(LessMakerException::class);
        $this->expectExceptionMessage('Target path does not exist');

        $this->cssMaker->setTargetCssPath('/invalid/path/that/does/not/exist');
    }

    public function testSetTargetFontPath(): void
    {
        $fontPath = $this->testTargetDir . DIRECTORY_SEPARATOR . 'fonts';
        $result = $this->cssMaker->setTargetFontPath($fontPath);
        $this->assertInstanceOf(CssMaker::class, $result);
        $this->assertSame($this->cssMaker, $result);
    }

    public function testSetTargetFontPathWithInvalidPath(): void
    {
        $this->expectException(LessMakerException::class);
        $this->expectExceptionMessage('Fonts path does not exist');

        $this->cssMaker->setTargetFontPath('/invalid/path/that/does/not/exist');
    }

    public function testSetDumpOutputPath(): void
    {
        $result = $this->cssMaker->setDumpOutputPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'output.log');
        $this->assertInstanceOf(CssMaker::class, $result);
        $this->assertSame($this->cssMaker, $result);
    }

    public function testAddTmpFile(): void
    {
        $tempFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'temp.less';
        file_put_contents($tempFile, '.test { color: red; }');

        $result = $this->cssMaker->addTmpFile($tempFile);
        $this->assertInstanceOf(CssMaker::class, $result);
        $this->assertSame($this->cssMaker, $result);
    }

    public function testAddLessFilesWithEmptyArray(): void
    {
        $result = $this->cssMaker->addLessFiles([]);
        $this->assertInstanceOf(CssMaker::class, $result);
        $this->assertSame($this->cssMaker, $result);
    }

    public function testAddLessFilesWithValidStructure(): void
    {
        // Create test less files
        $variablesFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'variables.yml';
        $mixinsFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'mixins.less';

        file_put_contents($variablesFile, "primary-color: '#007bff'\nfont-size: '14px'");
        file_put_contents($mixinsFile, '.border-radius(@radius) { border-radius: @radius; }');

        $paths = [
            'variables' => [$variablesFile],
            'mixins' => [$mixinsFile],
            'structure' => [],
        ];

        $result = $this->cssMaker->addLessFiles($paths);
        $this->assertInstanceOf(CssMaker::class, $result);
        $this->assertSame($this->cssMaker, $result);
    }

    public function testAddLessFilesWithNonExistentFiles(): void
    {
        $paths = [
            'variables' => ['/nonexistent/variables.yml'],
            'mixins' => ['/nonexistent/mixins.less'],
        ];

        // Should not throw exception, should handle gracefully
        $result = $this->cssMaker->addLessFiles($paths);
        $this->assertInstanceOf(CssMaker::class, $result);
    }

    public function testAddLessFilesWithUnderscoreFiles(): void
    {
        // Create files starting with underscore (should be ignored)
        $underscoreFile = $this->testTmpDir . DIRECTORY_SEPARATOR . '_private.less';
        file_put_contents($underscoreFile, '.private { display: none; }');

        $paths = [
            'structure' => [$underscoreFile],
        ];

        $result = $this->cssMaker->addLessFiles($paths);
        $this->assertInstanceOf(CssMaker::class, $result);
    }

    public function testClean(): void
    {
        // Add a temp file
        $tempFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'temp_cleanup.less';
        file_put_contents($tempFile, '.cleanup { color: blue; }');
        $this->cssMaker->addTmpFile($tempFile);

        $result = $this->cssMaker->clean();
        $this->assertInstanceOf(CssMaker::class, $result);
        $this->assertSame($this->cssMaker, $result);

        // File should be removed
        $this->assertFileDoesNotExist($tempFile);
    }

    public function testProcessWithoutRequiredPaths(): void
    {
        $this->expectException(LessMakerException::class);
        $this->expectExceptionMessage('Base, tmp, target CSS or target font paths are not set');

        $this->cssMaker->process('test-theme');
    }

    public function testSetBuildPaths(): void
    {
        $buildBaseDir = $this->testTargetDir . DIRECTORY_SEPARATOR . 'build_test';
        mkdir($buildBaseDir, 0777, true);
        mkdir($buildBaseDir . DIRECTORY_SEPARATOR . 'tmp', 0777, true);
        mkdir($buildBaseDir . DIRECTORY_SEPARATOR . 'build', 0777, true);
        mkdir($buildBaseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'css', 0777, true);
        mkdir($buildBaseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'fonts', 0777, true);

        $result = $this->cssMaker->setBuildPaths($buildBaseDir, 'build');
        $this->assertInstanceOf(CssMaker::class, $result);
        $this->assertSame($this->cssMaker, $result);
    }

    public function testFluentInterface(): void
    {
        $result = $this->cssMaker
            ->setBasePath($this->testTargetDir)
            ->setTmpPath($this->testTmpDir)
            ->setTargetCssPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'css')
            ->setTargetFontPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'fonts')
            ->setDumpOutputPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'output.log')
            ->addLessFiles([])
            ->clean();

        $this->assertInstanceOf(CssMaker::class, $result);
        $this->assertSame($this->cssMaker, $result);
    }

    public function testCompleteWorkflowWithoutProcessing(): void
    {
        // Set up all required paths
        $this->cssMaker
            ->setBasePath($this->testTargetDir)
            ->setTmpPath($this->testTmpDir)
            ->setTargetCssPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'css')
            ->setTargetFontPath($this->testTargetDir . DIRECTORY_SEPARATOR . 'fonts');

        // Add some LESS files
        $variablesFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'variables.yml';
        $structureFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'structure.less';

        file_put_contents($variablesFile, "primary-color: '#007bff'");
        file_put_contents($structureFile, '.header { background: @primary-color; }');

        $lessFiles = [
            'variables' => [$variablesFile],
            'structure' => [$structureFile],
        ];

        $result = $this->cssMaker
            ->addLessFiles($lessFiles)
            ->clean();

        $this->assertInstanceOf(CssMaker::class, $result);
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testInvalidPaths(string $method, string $invalidPath, string $expectedMessage): void
    {
        $this->expectException(LessMakerException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote($expectedMessage, '/') . '/');

        $this->cssMaker->$method($invalidPath);
    }

    public static function invalidPathProvider(): array
    {
        return [
            ['setBasePath', '/nonexistent/path', 'Base directory does not exist'],
            ['setTmpPath', '/nonexistent/path', 'Temporary path does not exist'],
            ['setTargetCssPath', '/nonexistent/path', 'Target path does not exist'],
            ['setTargetFontPath', '/nonexistent/path', 'Fonts path does not exist'],
        ];
    }

    public function testFileStructureTypes(): void
    {
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

        foreach ($fileTypes as $type) {
            $testFile = $this->testTmpDir . DIRECTORY_SEPARATOR . $type . '.less';
            file_put_contents($testFile, ".{$type} { color: red; }");

            $paths = [$type => [$testFile]];
            $result = $this->cssMaker->addLessFiles($paths);

            $this->assertInstanceOf(CssMaker::class, $result);
        }
    }

    public function testMultipleFilesPerType(): void
    {
        $files = [];
        for ($i = 1; $i <= 3; $i++) {
            $file = $this->testTmpDir . DIRECTORY_SEPARATOR . "structure{$i}.less";
            file_put_contents($file, ".structure{$i} { margin: {$i}px; }");
            $files[] = $file;
        }

        $paths = ['structure' => $files];
        $result = $this->cssMaker->addLessFiles($paths);

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
