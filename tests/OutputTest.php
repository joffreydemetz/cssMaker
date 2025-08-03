<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Output;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JDZ\CssMaker\Output
 */
class OutputTest extends TestCase
{
    private Output $output;

    protected function setUp(): void
    {
        $this->output = new Output();
    }

    public function testConstructor(): void
    {
        $output = new Output();
        $this->assertInstanceOf(Output::class, $output);
    }

    public function testAddMessage(): void
    {
        $this->output->add('Test message');
        $string = (string) $this->output;
        $this->assertStringContainsString('[INFO] Test message', $string);
    }

    public function testAddMessageWithTag(): void
    {
        $this->output->add('Error message', 'error');
        $string = (string) $this->output;
        $this->assertStringContainsString('[ERROR] Error message', $string);
    }

    public function testAddMultipleMessages(): void
    {
        $this->output->add('First message', 'info');
        $this->output->add('Second message', 'warn');
        $this->output->add('Third message', 'error');

        $string = (string) $this->output;
        $this->assertStringContainsString('[INFO] First message', $string);
        $this->assertStringContainsString('[WARN] Second message', $string);
        $this->assertStringContainsString('[ERROR] Third message', $string);
    }

    public function testToString(): void
    {
        $this->output->add('Test message 1');
        $this->output->add('Test message 2');

        $string = (string) $this->output;
        $lines = explode("\n", $string);

        $this->assertCount(2, $lines);
        $this->assertEquals('[INFO] Test message 1', $lines[0]);
        $this->assertEquals('[INFO] Test message 2', $lines[1]);
    }

    public function testVerbosityConstants(): void
    {
        $this->assertEquals(1, Output::VERBOSITY_STEP);
        $this->assertEquals(4, Output::VERBOSITY_ERROR);
        $this->assertEquals(8, Output::VERBOSITY_WARN);
        $this->assertEquals(16, Output::VERBOSITY_INFO);
        $this->assertEquals(32, Output::VERBOSITY_ALL);
    }

    public function testFluentInterface(): void
    {
        $result = $this->output
            ->setVerbosity(Output::VERBOSITY_ALL);

        $this->assertInstanceOf(Output::class, $result);
        $this->assertSame($this->output, $result);
    }

    public function testEmptyOutput(): void
    {
        $string = (string) $this->output;
        $this->assertEquals('', $string);
    }
}
