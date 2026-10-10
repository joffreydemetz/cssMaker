<?php

namespace JDZ\CssMaker\Tests;

class Helper
{
    /**
     * Stand-ins for the node tools CssMaker shells out to, as PHP bodies run by PHP_BINARY:
     * lessc writes one rule named after its input (with a comment and line breaks for the
     * Cleaner), postcss appends a marker to the CSS file it gets, minify prints the CSS
     * behind a marker. Each step is visible in the files process() leaves.
     */
    public const STUB_TOOLS = [
        'lessc' => 'file_put_contents($argv[2], ".compiled-from-" . basename($argv[1], ".less") . " {\n  color: red; /* note */\n}\n");',
        'postcss' => 'foreach (array_slice($argv, 1) as $arg) { if (str_ends_with($arg, ".css")) { file_put_contents($arg, " /* postcss */", FILE_APPEND); } }',
        'minify' => 'echo "/* min */", file_get_contents($argv[1]);',
    ];

    public static function createTempStructure(string $target = 'build'): string
    {
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'jdz-cssmaker-' . uniqid();
        mkdir($tempDir, 0777, true);
        mkdir($tempDir . DIRECTORY_SEPARATOR . 'tmp', 0777, true);
        mkdir($tempDir . DIRECTORY_SEPARATOR . $target, 0777, true);
        mkdir($tempDir . DIRECTORY_SEPARATOR . $target . DIRECTORY_SEPARATOR . 'css', 0777, true);
        mkdir($tempDir . DIRECTORY_SEPARATOR . $target . DIRECTORY_SEPARATOR . 'fonts', 0777, true);

        return $tempDir;
    }

    /**
     * Write the stub tools (STUB_TOOLS, $overrides replacing some bodies) to $dir/bin and
     * return that path with its trailing separator, as CssMaker's $nodejsBinPath.
     */
    public static function stubTools(string $dir, array $overrides = []): string
    {
        $bin = $dir . DIRECTORY_SEPARATOR . 'bin';
        if (!is_dir($bin)) {
            mkdir($bin);
        }

        foreach (array_merge(self::STUB_TOOLS, $overrides) as $name => $body) {
            $script = $bin . DIRECTORY_SEPARATOR . $name . '.php';
            file_put_contents($script, "<?php\n" . $body . "\n");

            if ('\\' === DIRECTORY_SEPARATOR) {
                file_put_contents(
                    $bin . DIRECTORY_SEPARATOR . $name . '.cmd',
                    '@"' . PHP_BINARY . '" "' . $script . '" %*' . "\r\n" . '@exit /b %ERRORLEVEL%' . "\r\n"
                );
            } else {
                $launcher = $bin . DIRECTORY_SEPARATOR . $name;
                file_put_contents($launcher, "#!/bin/sh\nexec " . escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' "$@"' . "\n");
                chmod($launcher, 0755);
            }
        }

        return $bin . DIRECTORY_SEPARATOR;
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
