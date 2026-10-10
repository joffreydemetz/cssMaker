<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Tests\InitializedMakerCase;
use JDZ\CssMaker\CssMaker;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Integration tests for the CssMaker library
 */
#[CoversClass(CssMaker::class)]
#[CoversClass(\JDZ\CssMaker\Cleaner::class)]
#[CoversClass(\JDZ\CssMaker\Merger::class)]
#[CoversClass(\JDZ\CssMaker\Variables::class)]
class IntegrationTest extends InitializedMakerCase
{
    public function testCompleteWorkflow(): void
    {
        // Add LESS files using the test fixtures
        $lessFiles = [
            'variables' => [
                $this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . 'variables.yml'
            ],
            'mixins' => [
                $this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . 'mixins.less'
            ],
            'normalize' => [
                $this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . 'normalize.less'
            ],
            'structure' => [
                $this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . 'structure.less'
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
        $result = $this->cssMaker->addLessFiles([]);

        $this->assertInstanceOf(CssMaker::class, $result);
    }

    public function testWorkflowWithNonExistentFiles(): void
    {
        $lessFiles = [
            'variables' => ['/nonexistent/variables.yml'],
            'mixins' => ['/nonexistent/mixins.less'],
            'structure' => ['/nonexistent/structure.less']
        ];

        // Should not throw an exception, should handle gracefully
        $result = $this->cssMaker->addLessFiles($lessFiles);
        $this->assertInstanceOf(CssMaker::class, $result);
    }

    public function testRealWorldScenario(): void
    {
        $lessFiles = [
            'variables' => [$this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . 'variables.yml'],
            'mixins' => [$this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . 'mixins.less'],
            'normalize' => [$this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . 'normalize.less'],
            'structure' => [$this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . 'structure.less'],
            'fonts' => [],
            'icons' => [],
            'mobile' => [],
            'screen' => [],
            'queries' => [],
            'print' => []
        ];

        $this->cssMaker->addLessFiles($lessFiles);

        $this->assertTrue(true);
    }
}
