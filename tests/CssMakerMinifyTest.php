<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Tests\InitializedMakerCase;
use JDZ\CssMaker\CssMaker;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * - minify (shells out to the node "minify" tool)
 */
#[CoversClass(CssMaker::class)]
class CssMakerMinifyTest extends InitializedMakerCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireTool('minify');
    }

    public function testMinifyCreateMinifiedCssFile(): void
    {
        $cssFilePath = $this->fixtureCopy('css' . DIRECTORY_SEPARATOR . 'valid.css');
        $minFilePath = $this->tempDir . DIRECTORY_SEPARATOR . 'valid.min.css';

        $this->invokeProtected('minify', $cssFilePath, $minFilePath);

        $this->assertFileExists($minFilePath, 'Minified CSS file should be created');
        $minified = file_get_contents($minFilePath);
        $this->assertStringContainsString('.test-class{', $minified, 'Selectors should be kept, whitespace dropped');
        $this->assertLessThan(filesize($cssFilePath), strlen($minified), 'Minified CSS should be smaller than the source');
    }

    public function testMinifyHandlesEmptyFile(): void
    {
        $cssFilePath = $this->fixtureCopy('css' . DIRECTORY_SEPARATOR . 'empty.css');
        $minFilePath = $this->tempDir . DIRECTORY_SEPARATOR . 'empty.min.css';

        $this->invokeProtected('minify', $cssFilePath, $minFilePath);

        $this->assertFileExists($minFilePath, 'Minified file should be created even for empty CSS');
        $this->assertLessThanOrEqual(10, strlen(file_get_contents($minFilePath)), 'Minified empty CSS should remain very small');
    }

    private function invokeProtected(string $method, string ...$args): void
    {
        (new \ReflectionMethod($this->cssMaker, $method))->invoke($this->cssMaker, ...$args);
    }
}
