<?php

namespace JDZ\CssMaker\Tests;

class Helper
{
    public static function createTempStructure(string $target = 'build'): string
    {
        $tempDir = __DIR__ . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR . 'cssmaker_test_' . uniqid();
        mkdir($tempDir, 0777, true);
        mkdir($tempDir . DIRECTORY_SEPARATOR . 'tmp', 0777, true);
        mkdir($tempDir . DIRECTORY_SEPARATOR . $target, 0777, true);
        mkdir($tempDir . DIRECTORY_SEPARATOR . $target . DIRECTORY_SEPARATOR . 'css', 0777, true);
        mkdir($tempDir . DIRECTORY_SEPARATOR . $target . DIRECTORY_SEPARATOR . 'fonts', 0777, true);

        return $tempDir;
    }

    public static function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                self::removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
