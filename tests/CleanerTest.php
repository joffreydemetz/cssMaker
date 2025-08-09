<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Cleaner;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JDZ\CssMaker\Cleaner
 */
class CleanerTest extends TestCase
{
    public function testConstructor(): void
    {
        $css = '.test { color: red; }';
        $cleaner = new Cleaner($css);

        $this->assertInstanceOf(Cleaner::class, $cleaner);
        $this->assertEquals($css, $cleaner->getCss());
    }

    public function testGetCss(): void
    {
        $css = '.test { color: red; }';
        $cleaner = new Cleaner($css);

        $this->assertEquals($css, $cleaner->getCss());
    }

    public function testRemoveSpaces(): void
    {
        $css = "  .test {\n    color:   red;\n}  ";
        $cleaner = new Cleaner($css);

        $result = $cleaner->removeSpaces();

        $this->assertInstanceOf(Cleaner::class, $result);
        $this->assertSame($cleaner, $result);

        $cleanedCss = $cleaner->getCss();
        $this->assertEquals(".test { color: red; }", $cleanedCss);
    }

    public function testRemoveSpacesWithCarriageReturns(): void
    {
        $css = ".test {\r\n  color: red;\r\n}";
        $cleaner = new Cleaner($css);

        $cleaner->removeSpaces();
        $cleanedCss = $cleaner->getCss();

        $this->assertEquals(".test { color: red; }", $cleanedCss);
    }

    public function testRemoveSpacesWithMixedLineEndings(): void
    {
        $css = ".test {\r\n  color: red;\r  background: blue;\n}";
        $cleaner = new Cleaner($css);

        $cleaner->removeSpaces();
        $cleanedCss = $cleaner->getCss();

        $this->assertEquals(".test { color: red; background: blue; }", $cleanedCss);
    }

    public function testRemoveComments(): void
    {
        $css = '.test { /* this is a comment */ color: red; }';
        $cleaner = new Cleaner($css);

        $result = $cleaner->removeComments();

        $this->assertInstanceOf(Cleaner::class, $result);
        $this->assertSame($cleaner, $result);

        $cleanedCss = $cleaner->getCss();
        $this->assertEquals('.test {  color: red; }', $cleanedCss);
    }

    public function testRemoveMultiLineComments(): void
    {
        $css = ".test {\n  /* This is a \n     multi-line comment */\n  color: red;\n}";
        $cleaner = new Cleaner($css);
        $cleaner->removeComments();
        $cleanedCss = $cleaner->getCss();

        $this->assertStringNotContainsString('/* This is a', $cleanedCss);
        $this->assertStringNotContainsString('multi-line comment */', $cleanedCss);
        $this->assertStringContainsString('color: red;', $cleanedCss);
    }

    public function testRemoveMultipleComments(): void
    {
        $css = '.test { /* comment 1 */ color: red; /* comment 2 */ background: blue; }';
        $cleaner = new Cleaner($css);

        $cleaner->removeComments();
        $cleanedCss = $cleaner->getCss();

        $this->assertStringNotContainsString('/* comment 1 */', $cleanedCss);
        $this->assertStringNotContainsString('/* comment 2 */', $cleanedCss);
        $this->assertStringContainsString('color: red;', $cleanedCss);
        $this->assertStringContainsString('background: blue;', $cleanedCss);
    }

    public function testRemoveCommentsWithAsterisks(): void
    {
        $css = '.test { /*** multiple asterisks ***/ color: red; }';
        $cleaner = new Cleaner($css);

        $cleaner->removeComments();
        $cleanedCss = $cleaner->getCss();

        $this->assertStringNotContainsString('/*** multiple asterisks ***/', $cleanedCss);
        $this->assertStringContainsString('color: red;', $cleanedCss);
    }

    public function testChainedCleaning(): void
    {
        $css = "\n.test {\n  /* This is a comment */\n  color: red;\n}\n";

        $cleaner = new Cleaner($css);

        $result = $cleaner
            ->removeComments()
            ->removeSpaces();

        $this->assertInstanceOf(Cleaner::class, $result);
        $this->assertSame($cleaner, $result);

        $cleanedCss = $cleaner->getCss();
        $this->assertStringNotContainsString('/* This is a comment */', $cleanedCss);
        $this->assertStringContainsString('color: red;', $cleanedCss);

        // Should not start or end with whitespace
        $this->assertEquals(trim($cleanedCss), $cleanedCss);
    }

    public function testFluentInterface(): void
    {
        $css = '  .test { /* comment */ color: red; }  ';
        $cleaner = new Cleaner($css);

        $result = $cleaner
            ->removeSpaces()
            ->removeComments();

        $this->assertInstanceOf(Cleaner::class, $result);
        $this->assertSame($cleaner, $result);
    }

    public function testEmptyCss(): void
    {
        $cleaner = new Cleaner('');

        $cleaner->removeSpaces()->removeComments();

        $this->assertEquals('', $cleaner->getCss());
    }

    public function testCssWithOnlyComments(): void
    {
        $css = '/* only comment */';
        $cleaner = new Cleaner($css);

        $cleaner->removeComments();
        $cleanedCss = $cleaner->getCss();

        $this->assertStringNotContainsString('/* only comment */', $cleanedCss);
        $this->assertStringContainsString(' ', $cleanedCss); // Comment is replaced with space
    }

    public function testCssWithOnlyWhitespace(): void
    {
        $css = "   \n\r\n   ";
        $cleaner = new Cleaner($css);

        $cleaner->removeSpaces();
        $cleanedCss = $cleaner->getCss();

        $this->assertEquals('', $cleanedCss);
    }

    public function testComplexCss(): void
    {
        $css = "/* Header styles */\n.header {\n  /* Primary color */\n  color: #007bff;\n  background: white; /* Background color */\n}\n\n/* Footer styles */\n.footer {\n  color: gray;\n}\n";

        $cleaner = new Cleaner($css);
        $cleanedCss = $cleaner
            ->removeComments()
            ->removeSpaces()
            ->getCss();

        $this->assertStringNotContainsString('/* Header styles */', $cleanedCss);
        $this->assertStringNotContainsString('/* Primary color */', $cleanedCss);
        $this->assertStringNotContainsString('/* Background color */', $cleanedCss);
        $this->assertStringNotContainsString('/* Footer styles */', $cleanedCss);

        $this->assertStringContainsString('.header', $cleanedCss);
        $this->assertStringContainsString('color: #007bff;', $cleanedCss);
        $this->assertStringContainsString('background: white;', $cleanedCss);
        $this->assertStringContainsString('.footer', $cleanedCss);
        $this->assertStringContainsString('color: gray;', $cleanedCss);
    }
}
