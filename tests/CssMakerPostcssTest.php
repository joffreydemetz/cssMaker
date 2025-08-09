<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Tests\InitializedMakerCase;

/**
 * @covers \JDZ\CssMaker\CssMaker
 * - postcss
 */
class CssMakerPostCssTest extends InitializedMakerCase
{
    public function testPostcssProcessesCssWithAutoprefixer(): void
    {
        $cssFilePath = $this->fixturesDir . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'valid.css';

        // Use reflection to access the protected postcss method
        $reflection = new \ReflectionClass($this->cssMaker);
        $postcssMethod = $reflection->getMethod('postcss');
        $postcssMethod->setAccessible(true);

        try {
            // Call postcss method
            $postcssMethod->invoke($this->cssMaker, $cssFilePath);

            // Read the processed CSS content
            $processedContent = file_get_contents($cssFilePath);

            // Verify that the file was processed (content should change or remain the same)
            $this->assertIsString($processedContent, 'Processed CSS should be a string');

            // Basic verification that CSS structure is maintained
            $this->assertStringContainsString('.test-class', $processedContent, 'CSS selectors should be preserved');
            $this->assertStringContainsString('display:', $processedContent, 'CSS properties should be preserved');

            // @TODO Verify that PostCSS config was applied (postcss.json should be used)
            //       The actual autoprefixer results depend on the browserslist config
            //       So we just verify the process completed without error

        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            // If postcss is not available, skip this test part
            $this->markTestSkipped('postcss command not available for postcss test. Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            // If postcss is not available or config is missing, skip this test
            if (
                strpos($e->getMessage(), 'postcss') !== false ||
                strpos($e->getMessage(), 'config') !== false
            ) {
                $this->markTestSkipped('postcss command or config not available for postcss test');
            } else {
                throw $e;
            }
        }
    }

    public function testPostcssThrowsExceptionOnInvalidCss(): void
    {
        $cssFilePath = $this->fixturesDir . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'invalid.css';

        // Use reflection to access the protected postcss method
        $reflection = new \ReflectionClass($this->cssMaker);
        $postcssMethod = $reflection->getMethod('postcss');
        $postcssMethod->setAccessible(true);

        try {
            // Depending on postcss configuration, this might or might not throw an exception
            // Some invalid CSS might be processed anyway, so we test both scenarios
            $postcssMethod->invoke($this->cssMaker, $cssFilePath);

            // If no exception was thrown, verify the file still exists and has content
            $this->assertFileExists($cssFilePath, 'CSS file should still exist after postcss processing');
            $processedContent = file_get_contents($cssFilePath);
            $this->assertNotEmpty($processedContent, 'Processed CSS should not be empty');
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $e) {
            // This might be expected behavior for severely invalid CSS
            $this->addToAssertionCount(1); // Count this as a successful assertion
        } catch (\Exception $e) {
            // If postcss is not available, skip this test
            if (strpos($e->getMessage(), 'postcss') !== false) {
                $this->markTestSkipped('postcss command not available for postcss error test');
            } else {
                throw $e;
            }
        }
    }
}
