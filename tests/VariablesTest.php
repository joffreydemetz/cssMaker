<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Variables;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use JDZ\CssMaker\Tests\Helper;

#[CoversClass(Variables::class)]
class VariablesTest extends TestCase
{
    private Variables $variables;
    private string $testTmpDir;

    protected function setUp(): void
    {
        $this->variables = new Variables();
        $this->testTmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cssmaker_variables_test_' . uniqid();
        mkdir($this->testTmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        Helper::removeDirectory($this->testTmpDir);
    }

    public function testExportVarsEmpty(): void
    {
        $vars = $this->variables->exportVars();
        $this->assertIsArray($vars);
        $this->assertEmpty($vars);
    }

    public function testExportVarsWithData(): void
    {
        $this->variables->set('primary-color', '#007bff');
        $this->variables->set('font-size', '14px');
        $this->variables->set('empty-value', '');

        $vars = $this->variables->exportVars();

        $this->assertIsArray($vars);
        $this->assertCount(3, $vars);
        $this->assertContains('@primary-color: #007bff;', $vars);
        $this->assertContains('@font-size: 14px;', $vars);
        $this->assertContains('@empty-value: "";', $vars);
    }

    public function testToFileEmpty(): void
    {
        $output = $this->variables->toFile();
        $this->assertEquals('', $output);
    }

    public function testToFileWithData(): void
    {
        $this->variables->set('primary-color', '#007bff');
        $this->variables->set('font-size', '14px');

        $output = $this->variables->toFile();
        $lines = explode("\n", $output);

        $this->assertCount(2, $lines);
        $this->assertContains('@primary-color: #007bff;', $lines);
        $this->assertContains('@font-size: 14px;', $lines);
    }

    public function testAddFromFileValidYaml(): void
    {
        $yamlFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'variables.yml';
        $yamlContent = <<<YAML
primary-color: '#007bff'
secondary-color: '#6c757d'
font-size: '14px'
line-height: 1.5
YAML;
        file_put_contents($yamlFile, $yamlContent);

        $result = $this->variables->addFromFile($yamlFile);

        $this->assertInstanceOf(Variables::class, $result);
        $this->assertSame($this->variables, $result);

        $vars = $this->variables->exportVars();
        $this->assertCount(4, $vars);
        $this->assertContains('@primary-color: #007bff;', $vars);
        $this->assertContains('@secondary-color: #6c757d;', $vars);
        $this->assertContains('@font-size: 14px;', $vars);
        $this->assertContains('@line-height: 1.5;', $vars);
    }

    public function testAddFromFileInvalidYaml(): void
    {
        $yamlFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'invalid.yml';
        $invalidYaml = <<<YAML
invalid: yaml: content:
  - item1
    item2: value
YAML;
        file_put_contents($yamlFile, $invalidYaml);

        // Should not throw exception, should silently handle parse errors
        $result = $this->variables->addFromFile($yamlFile);
        $this->assertInstanceOf(Variables::class, $result);

        $vars = $this->variables->exportVars();
        $this->assertEmpty($vars);
    }

    public function testAddFromFileEmptyYaml(): void
    {
        $yamlFile = $this->testTmpDir . DIRECTORY_SEPARATOR . 'empty.yml';
        file_put_contents($yamlFile, '');

        $result = $this->variables->addFromFile($yamlFile);
        $this->assertInstanceOf(Variables::class, $result);

        $vars = $this->variables->exportVars();
        $this->assertEmpty($vars);
    }
}
