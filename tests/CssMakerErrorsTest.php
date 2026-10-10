<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\CssMaker;
use JDZ\CssMaker\Exception\LessMakerException;
use JDZ\CssMaker\Tests\Helper;
use JDZ\CssMaker\Tests\InitializedMakerCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Every throw CssMaker reaches, on the temp dir InitializedMakerCase builds ({dir}: tmp/, build/css/, build/fonts/).
 * The node tools are stubbed (Helper::stubTools): no row needs them installed.
 */
#[CoversClass(CssMaker::class)]
class CssMakerErrorsTest extends InitializedMakerCase
{
    public static function lessMakerExceptionProvider(): array
    {
        return [
            // no row for a font without an id: CssMaker.php:124 reads $font->id before the check (PHP warning)
            'font without a family' => [
                static fn(string $dir) => (new CssMaker())->addFont((object) ['id' => 'f', 'files' => []]),
                'Font object is missing required property: family',
            ],
            'font without files' => [
                static fn(string $dir) => (new CssMaker())->addFont((object) ['id' => 'f', 'family' => 'F']),
                'Font object is missing required property: files',
            ],
            'process() before setBuildPaths()' => [
                static fn(string $dir) => (new CssMaker())->process(),
                'Base, tmp, target CSS or target font paths are not set',
            ],
            'missing base dir' => [
                static fn(string $dir) => (new CssMaker())->setBuildPaths($dir . '/missing'),
                'Base directory does not exist: {dir}/missing/',
            ],
            'missing tmp dir' => [
                static function (string $dir): void {
                    rmdir($dir . DIRECTORY_SEPARATOR . 'tmp');
                    (new CssMaker())->setBuildPaths($dir);
                },
                'Temporary path does not exist: {dir}/tmp/',
            ],
            'missing target css dir' => [
                static fn(string $dir) => (new CssMaker())->setBuildPaths($dir, 'dist'),
                'Target path does not exist: {dir}/dist/css/',
            ],
            'missing target fonts dir' => [
                static function (string $dir): void {
                    mkdir($dir . DIRECTORY_SEPARATOR . 'dist' . DIRECTORY_SEPARATOR . 'css', 0777, true);
                    (new CssMaker())->setBuildPaths($dir, 'dist');
                },
                'Fonts path does not exist: {dir}/dist/fonts/',
            ],
            'LESS file not written' => [
                static function (string $dir): void {
                    $maker = (new CssMaker())->setBuildPaths($dir);
                    // the theme's folder does not exist: file_put_contents() warns, silenced to reach the throw
                    @$maker->process('missing/theme');
                },
                'LESS file not created: {dir}/build/css/missing/theme.less',
            ],
            'lessc exits 0 without writing the CSS' => [
                static fn(string $dir) => (new CssMaker(null, Helper::stubTools($dir, ['lessc' => ''])))->setBuildPaths($dir)->process(),
                'CSS file not created: {dir}/build/css/default.css',
            ],
        ];
    }

    #[DataProvider('lessMakerExceptionProvider')]
    public function testThrowsLessMakerException(\Closure $scenario, string $message): void
    {
        $this->expectException(LessMakerException::class);
        $this->expectExceptionMessageMatches('/^' . preg_quote(strtr($message, ['{dir}' => $this->tempDir]), '/') . '\z/');

        $scenario($this->tempDir);
    }

    public static function toolFailureProvider(): array
    {
        return [
            'lessc' => ['lessc', 3],
            'postcss' => ['postcss', 4],
            'minify' => ['minify', 5],
        ];
    }

    #[DataProvider('toolFailureProvider')]
    public function testProcessRethrowsTheFailureOfANodeTool(string $tool, int $exitCode): void
    {
        $bin = Helper::stubTools($this->tempDir, [$tool => 'fwrite(STDERR, "' . $tool . ' failed"); exit(' . $exitCode . ');']);
        $maker = (new CssMaker(null, $bin))->setBuildPaths($this->tempDir);

        $this->expectException(ProcessFailedException::class);
        $this->expectExceptionMessageMatches(
            '/^The command ".*' . preg_quote($bin . $tool, '/') . '.*" failed\.\n\nExit Code: ' . $exitCode . '\(.*\n' . $tool . ' failed\z/s'
        );

        $maker->process();
    }
}
