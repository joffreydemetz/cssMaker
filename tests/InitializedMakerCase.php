<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\CssMaker;
use PHPUnit\Framework\TestCase;
use JDZ\CssMaker\Tests\Helper;
use Symfony\Component\Process\ExecutableFinder;

/**
 * Test loaded CssMaker with initialized paths
 */
class InitializedMakerCase extends TestCase
{
    protected CssMaker $cssMaker;
    protected string $tempDir;
    protected string $srcFontsDir;
    protected string $srcLessDir;
    protected string $targetCssDir;
    protected string $targetFontsDir;
    protected string $fixturesDir;

    protected function setUp(): void
    {
        $this->tempDir = Helper::createTempStructure('build');
        $this->srcFontsDir = $this->tempDir . DIRECTORY_SEPARATOR . 'fonts';
        $this->srcLessDir = $this->tempDir . DIRECTORY_SEPARATOR . 'less';
        $this->targetCssDir = $this->tempDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'css';
        $this->targetFontsDir = $this->tempDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'fonts';

        $this->fixturesDir = __DIR__ . DIRECTORY_SEPARATOR . 'fixtures';

        $this->cssMaker = new CssMaker(null, self::nodejsBinPath());
        $this->cssMaker->setBuildPaths($this->tempDir, 'build');
    }

    /**
     * The package's own node_modules/.bin (composer npm:local), with its trailing
     * separator; '' when absent, so the tools resolve through PATH (npm:global).
     */
    protected static function nodejsBinPath(): string
    {
        $bin = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'node_modules' . DIRECTORY_SEPARATOR . '.bin';

        return is_dir($bin) ? $bin . DIRECTORY_SEPARATOR : '';
    }

    /**
     * Skip, visibly, when a node tool CssMaker shells out to is not installed.
     * Once it is installed, its failures fail the test.
     */
    protected function requireTool(string $tool): void
    {
        $local = self::nodejsBinPath();
        if ('' !== $local && (is_file($local . $tool) || is_file($local . $tool . '.cmd'))) {
            return;
        }

        if (null === (new ExecutableFinder())->find($tool)) {
            $this->markTestSkipped($tool . ' is not installed: composer npm:local (or npm:global)');
        }
    }

    /**
     * Copy a fixture into the temp dir, so tools that rewrite in place never touch tests/fixtures.
     */
    protected function fixtureCopy(string $relativePath): string
    {
        $copy = $this->tempDir . DIRECTORY_SEPARATOR . basename($relativePath);
        copy($this->fixturesDir . DIRECTORY_SEPARATOR . $relativePath, $copy);

        return $copy;
    }

    protected function tearDown(): void
    {
        Helper::removeDirectory($this->tempDir);
    }
}
