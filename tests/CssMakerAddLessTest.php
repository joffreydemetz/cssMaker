<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Tests\InitializedMakerCase;

/**
 * @covers \JDZ\CssMaker\CssMaker
 * - addLessFiles
 */
class CssMakerAddLessTest extends InitializedMakerCase
{
    public function testAddLessFiles(): void
    {
        $validFile1 = $this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . 'valid1.less';
        $validFile2 = $this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . 'valid2.less';
        $underscoreFile = $this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . '_underscore.less';
        $nonExistentFile = $this->fixturesDir . DIRECTORY_SEPARATOR . 'less' . DIRECTORY_SEPARATOR . 'nonexistent.less';

        // Test addLessFiles with mixed file types
        $paths = [
            'mixins' => [$validFile1, $underscoreFile, $nonExistentFile],
            'structure' => [$validFile2],
            'nonexistent_type' => [$validFile1], // This type doesn't exist in the files array
        ];

        $result = $this->cssMaker->addLessFiles($paths);

        // Verify fluent interface
        $this->assertSame($this->cssMaker, $result, 'addLessFiles should return self for fluent interface');

        // Use reflection to access private $files property to verify filtering
        $reflection = new \ReflectionClass($this->cssMaker);
        $filesProperty = $reflection->getProperty('files');
        $filesProperty->setAccessible(true);
        $files = $filesProperty->getValue($this->cssMaker);

        // Check that only valid files were added
        $this->assertContains($validFile1, $files['mixins'], 'Valid file 1 should be included');
        $this->assertContains($validFile2, $files['structure'], 'Valid file 2 should be included');

        // Check that underscore files and non-existent files were excluded
        $this->assertNotContains($underscoreFile, $files['mixins'], 'Underscore file should be excluded');
        $this->assertNotContains($nonExistentFile, $files['mixins'], 'Non-existent file should be excluded');

        // Check that invalid file types are ignored (no error thrown)
        $this->assertEmpty($files['nonexistent_type'] ?? [], 'Non-existent file type should remain empty');
    }

    public function testAddLessFilesWithEmptyArray(): void
    {
        // Test with empty array
        $result = $this->cssMaker->addLessFiles([]);

        // Verify fluent interface
        $this->assertSame($this->cssMaker, $result, 'addLessFiles should return self even with empty array');

        // Use reflection to verify no files were added
        $reflection = new \ReflectionClass($this->cssMaker);
        $filesProperty = $reflection->getProperty('files');
        $filesProperty->setAccessible(true);
        $files = $filesProperty->getValue($this->cssMaker);

        // All file type arrays should remain empty
        foreach ($files as $fileType => $fileList) {
            $this->assertEmpty($fileList, "File type '$fileType' should remain empty");
        }
    }
}
