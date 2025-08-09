<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\CssMaker;
use PHPUnit\Framework\TestCase;
use JDZ\CssMaker\Tests\Helper;

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

        $nodejsBinPath = realpath(__DIR__ . '/../../../node_modules/.bin/');
        $this->cssMaker = new CssMaker(null, $nodejsBinPath);
        $this->cssMaker->setBuildPaths($this->tempDir, 'build');
    }

    protected function tearDown(): void
    {
        Helper::removeDirectory($this->tempDir);
    }
}
