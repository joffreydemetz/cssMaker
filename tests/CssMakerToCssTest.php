<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Tests\InitializedMakerCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * - toCss (shells out to the node "lessc" tool)
 */
#[CoversClass(\JDZ\CssMaker\CssMaker::class)]
class CssMakerToCssTest extends InitializedMakerCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->requireTool('lessc');
    }

    public function testToCssConvertsLessToCss(): void
    {
        $lessFilePath = $this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . 'valid.less';
        $cssFilePath = $this->targetCssDir . DIRECTORY_SEPARATOR . 'default.css';

        (new \ReflectionMethod($this->cssMaker, 'toCss'))->invoke($this->cssMaker, $lessFilePath, $cssFilePath);

        $this->assertFileExists($cssFilePath, 'CSS file should be generated from LESS');
        $css = file_get_contents($cssFilePath);
        $this->assertStringContainsString('.test-class', $css, 'CSS should contain the class selector');
        $this->assertStringContainsString('color: #333', $css, 'The LESS variable should be resolved');
        $this->assertStringContainsString('font-size: 14px', $css, 'CSS should contain font-size');
        $this->assertStringNotContainsString('@primary-color', $css, 'CSS should not contain LESS variables');
        $this->assertStringContainsString('.test-class .nested', $css, 'Nesting should be flattened');
    }
}
