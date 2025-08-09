<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Tests\InitializedMakerCase;

/**
 * @covers \JDZ\CssMaker\CssMaker
 */
class CssMakerToCssTest extends InitializedMakerCase
{
    public function testToCssConvertsLessToCss(): void
    {
        // Create a simple LESS file with basic content
        $lessFilePath = $this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . 'valid.less';
        $cssFilePath = $this->targetCssDir . DIRECTORY_SEPARATOR . 'default.css';

        // Use reflection to access the protected toCss method
        $reflection = new \ReflectionClass($this->cssMaker);
        $toCssMethod = $reflection->getMethod('toCss');
        $toCssMethod->setAccessible(true);

        // This test will only pass if lessc is available and working
        // In a real CI environment, this would need lessc to be installed
        try {
            // Call toCss method
            $toCssMethod->invoke($this->cssMaker, $lessFilePath, $cssFilePath);

            // Verify CSS file was created
            $this->assertFileExists($cssFilePath, 'CSS file should be generated from LESS');

            // Read the generated CSS content
            $cssContent = file_get_contents($cssFilePath);

            // Verify that LESS was processed correctly
            $this->assertStringContainsString('.test-class', $cssContent, 'CSS should contain the class selector');
            $this->assertStringContainsString('color:', $cssContent, 'CSS should contain color property');
            $this->assertStringContainsString('font-size: 14px', $cssContent, 'CSS should contain font-size');

            // Verify LESS variables were processed (should not contain @primary-color)
            $this->assertStringNotContainsString('@primary-color', $cssContent, 'CSS should not contain LESS variables');

            // Verify nesting was processed (should contain .test-class .nested or similar)
            $this->assertStringContainsString('.test-class .nested', $cssContent, 'CSS should contain nested selector');
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            // If lessc is not available, skip this test part but still verify the method exists
            $this->markTestSkipped('lessc command not available for toCss test. Error: ' . $e->getMessage());
        }
    }
}
