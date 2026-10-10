<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Cleaner;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Cleaner::class)]
class CleanerTest extends TestCase
{
    /**
     * A block comment and the whitespace before it become one space; what follows is untouched.
     */
    public static function commentsProvider(): array
    {
        return [
            'block comment inside a rule' => ['.test { /* this is a comment */ color: red; }', '.test {  color: red; }'],
            'block comment spanning lines' => [".test {\n  /* This is a \n     multi-line comment */\n  color: red;\n}", ".test { \n  color: red;\n}"],
            'several block comments' => ['.test { /* comment 1 */ color: red; /* comment 2 */ background: blue; }', '.test {  color: red;  background: blue; }'],
            'runs of asterisks' => ['.test { /*** multiple asterisks ***/ color: red; }', '.test {  color: red; }'],
            'docblock before a rule' => ["/**\n * Header\n */\n.header {}", " \n.header {}"],
            'comment only' => ['/* only comment */', ' '],
            'no comment' => ['.test { color: red; }', '.test { color: red; }'],
            'empty' => ['', ''],
        ];
    }

    #[DataProvider('commentsProvider')]
    public function testRemoveComments(string $css, string $expected): void
    {
        $this->assertSame($expected, (new Cleaner($css))->removeComments()->getCss());
    }

    /**
     * Every run of whitespace (any line ending, tabs, form feeds, vertical tabs) becomes one space, then the ends are trimmed.
     */
    public static function spacesProvider(): array
    {
        return [
            'indentation and line breaks' => ["  .test {\n    color:   red;\n}  ", '.test { color: red; }'],
            'CRLF line endings' => [".test {\r\n  color: red;\r\n}", '.test { color: red; }'],
            'CR, CRLF and LF mixed' => [".test {\r\n  color: red;\r  background: blue;\n}", '.test { color: red; background: blue; }'],
            'tabs, form feeds and vertical tabs' => ["a\t{\fcolor:\vred;\t}", 'a { color: red; }'],
            'whitespace only' => ["   \n\r\n   ", ''],
            'already compact' => ['.test{color:red}', '.test{color:red}'],
            'empty' => ['', ''],
        ];
    }

    #[DataProvider('spacesProvider')]
    public function testRemoveSpaces(string $css, string $expected): void
    {
        $this->assertSame($expected, (new Cleaner($css))->removeSpaces()->getCss());
    }

    /**
     * Comments then spaces: the order CssMaker::process() cleans the compiled CSS in.
     */
    public static function cleanProvider(): array
    {
        return [
            'commented rules' => [
                "/* Header styles */\n.header {\n  /* Primary color */\n  color: #007bff;\n  background: white; /* Background color */\n}\n\n/* Footer styles */\n.footer {\n  color: gray;\n}\n",
                '.header { color: #007bff; background: white; } .footer { color: gray; }',
            ],
            'comment on its own line' => ["\n.test {\n  /* This is a comment */\n  color: red;\n}\n", '.test { color: red; }'],
        ];
    }

    #[DataProvider('cleanProvider')]
    public function testRemoveCommentsThenSpaces(string $css, string $expected): void
    {
        $this->assertSame($expected, (new Cleaner($css))->removeComments()->removeSpaces()->getCss());
    }
}
