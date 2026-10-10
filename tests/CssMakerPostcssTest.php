<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Tests\InitializedMakerCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * - postcss (shells out to the node "postcss" tool, --replace: always on a temp copy)
 */
#[CoversClass(\JDZ\CssMaker\CssMaker::class)]
class CssMakerPostCssTest extends InitializedMakerCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireTool('postcss');
    }

    public function testPostcssKeepsTheCssStructure(): void
    {
        $cssFilePath = $this->fixtureCopy('css' . DIRECTORY_SEPARATOR . 'valid.css');

        $this->invokeProtected('postcss', $cssFilePath);

        $processed = file_get_contents($cssFilePath);
        $this->assertStringContainsString('.test-class', $processed, 'CSS selectors should be preserved');
        $this->assertStringContainsString('display:', $processed, 'CSS properties should be preserved');
    }

    public function testPostcssLeavesAnUnparsableFileInPlace(): void
    {
        $cssFilePath = $this->fixtureCopy('css' . DIRECTORY_SEPARATOR . 'invalid.css');

        $this->invokeProtected('postcss', $cssFilePath);

        $this->assertFileExists($cssFilePath, 'CSS file should still exist after postcss processing');
        $this->assertStringContainsString('.invalid-class', file_get_contents($cssFilePath));
    }

    private function invokeProtected(string $method, string ...$args): void
    {
        (new \ReflectionMethod($this->cssMaker, $method))->invoke($this->cssMaker, ...$args);
    }
}
