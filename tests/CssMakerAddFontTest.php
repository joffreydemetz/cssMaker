<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Tests\InitializedMakerCase;

/**
 * @covers \JDZ\CssMaker\CssMaker
 * - addFont
 */
class CssMakerAddFontTest extends InitializedMakerCase
{
    public function testAddFontWithValidFont(): void
    {
        $testTtfFile = $this->fixturesDir . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'test-font.ttf';
        $testWoffFile = $this->fixturesDir . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'test-font.woff';
        $testWoff2File = $this->fixturesDir . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'test-font.woff2';

        // Create a font object with the expected structure
        $fontData = (object) [
            'id' => 'test-font',
            'family' => 'Test Font',
            'display' => 'swap',
            'style' => 'normal',
            'weight' => '400',
            'files' => [
                'ttf' => $testTtfFile,
                'woff' => $testWoffFile,
                'woff2' => $testWoff2File,
            ]
        ];

        // Test adding the font
        $this->cssMaker->addFont($fontData);

        // Use reflection to check that the font was added to the internal fonts array
        $reflection = new \ReflectionClass($this->cssMaker);
        $fontsProperty = $reflection->getProperty('fonts');
        $fontsProperty->setAccessible(true);
        $fonts = $fontsProperty->getValue($this->cssMaker);

        // Verify the font was added
        $this->assertArrayHasKey('test-font', $fonts, 'Font should be added to fonts array');
        $this->assertInstanceOf(\JDZ\CssMaker\Font::class, $fonts['test-font'], 'Font should be instance of Font class');

        // Verify font properties
        $addedFont = $fonts['test-font'];
        $this->assertEquals('test-font', $addedFont->id, 'Font ID should match');
        $this->assertEquals('Test Font', $addedFont->family, 'Font family should match');
        $this->assertEquals('swap', $addedFont->display, 'Font display should match');
        $this->assertEquals('normal', $addedFont->style, 'Font style should match');
        $this->assertEquals('400', $addedFont->weight, 'Font weight should match');
    }

    public function testAddFontWithDuplicateId(): void
    {
        $testTtfFile = $this->fixturesDir . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'test-font.ttf';

        // Create first font object
        $fontData1 = (object) [
            'id' => 'duplicate-font',
            'family' => 'First Font',
            'display' => 'swap',
            'style' => 'normal',
            'weight' => '400',
            'files' => ['ttf' => $testTtfFile]
        ];

        // Create second font object with same ID
        $fontData2 = (object) [
            'id' => 'duplicate-font',
            'family' => 'Second Font',
            'display' => 'block',
            'style' => 'italic',
            'weight' => '700',
            'files' => ['ttf' => $testTtfFile]
        ];

        // Add first font
        $this->cssMaker->addFont($fontData1);

        // Add second font with same ID (should be ignored)
        $this->cssMaker->addFont($fontData2);

        // Use reflection to check the fonts array
        $reflection = new \ReflectionClass($this->cssMaker);
        $fontsProperty = $reflection->getProperty('fonts');
        $fontsProperty->setAccessible(true);
        $fonts = $fontsProperty->getValue($this->cssMaker);

        // Verify only first font was added
        $this->assertArrayHasKey('duplicate-font', $fonts, 'Font should be in fonts array');
        $this->assertEquals('First Font', $fonts['duplicate-font']->family, 'Should keep first font, not overwrite with second');
    }

    public function testAddFontWithMissingFontFiles(): void
    {
        // Create a font object with non-existent files
        $fontData = (object) [
            'id' => 'missing-files-font',
            'family' => 'Missing Files Font',
            'display' => 'swap',
            'style' => 'normal',
            'weight' => '400',
            'files' => [
                'ttf' => $this->fixturesDir . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'nonexistent.ttf',
                'woff' => $this->fixturesDir . DIRECTORY_SEPARATOR . 'fonts' . DIRECTORY_SEPARATOR . 'nonexistent.woff',
            ]
        ];

        // This should not throw an exception, but should handle missing files gracefully
        $this->cssMaker->addFont($fontData);

        // Use reflection to check if font was added (it should be, even with missing files)
        $reflection = new \ReflectionClass($this->cssMaker);
        $fontsProperty = $reflection->getProperty('fonts');
        $fontsProperty->setAccessible(true);
        $fonts = $fontsProperty->getValue($this->cssMaker);

        // The font should still be added, but file copying operations would have failed gracefully
        $this->assertArrayHasKey('missing-files-font', $fonts, 'Font should be added even with missing files');
    }

    public function testAddFontWithInvalidFontData(): void
    {
        // Create a font object with missing required properties
        $invalidFontData = (object) [
            'id' => 'invalid-font',
            // Missing family, files
        ];

        // This should not throw an exception but should handle the error gracefully
        $this->expectException(\JDZ\CssMaker\Exception\LessMakerException::class);
        $this->expectExceptionMessage('Font object is missing required property');
        $this->cssMaker->addFont($invalidFontData);
    }
}
