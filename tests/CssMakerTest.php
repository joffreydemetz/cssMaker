<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Cleaner;
use JDZ\CssMaker\CssMaker;
use JDZ\CssMaker\Font;
use JDZ\CssMaker\Merger;
use JDZ\CssMaker\Variables;
use JDZ\CssMaker\Tests\Helper;
use JDZ\CssMaker\Tests\InitializedMakerCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * process() end to end. The stubbed toolchain (Helper::stubTools) runs everything but the
 * node tools, unconditionally; the last test runs the real lessc / postcss / minify.
 */
#[CoversClass(CssMaker::class)]
#[CoversClass(Merger::class)]
#[CoversClass(Variables::class)]
#[CoversClass(Cleaner::class)]
#[CoversClass(Font::class)]
class CssMakerTest extends InitializedMakerCase
{
    public function testProcessMergesTheSourcesInTypeOrder(): void
    {
        $src = $this->tempDir . DIRECTORY_SEPARATOR;
        $maker = $this->stubbedMaker();

        // listed backwards: the order of the merged LESS is CssMaker's, not the caller's
        $maker->addLessFiles([
            'print' => [$this->source('print.less', '.print { display: none; }')],
            'queries' => [$this->source('queries.less', '@media (hover: hover) { .queries { color: blue; } }')],
            'screen' => [$this->source('screen.less', '.screen { display: block; }')],
            'mobile' => [$this->source('mobile.less', '.mobile { display: block; }')],
            'icons' => [$this->source('icons.less', '.icon { width: 1em; }')],
            'structure' => [
                $this->source('structure.less', '.structure { .m(); }'),
                $this->fixtureCopy('less' . DIRECTORY_SEPARATOR . '_underscore.less'),
                $src . 'missing.less',
            ],
            'fonts' => [$this->source('fonts.less', '.font { font-family: serif; }')],
            'animations' => [$this->source('animations.less', '@keyframes spin { to { opacity: 1; } }')],
            'normalize' => [$this->source('normalize.less', 'html { margin: 0; }')],
            'mixins' => [$this->source('mixins.less', '.m() { color: @brand; }')],
            'variables' => [$this->source('variables.yml', "brand: red\nscreen-breakpoint: 768px\n"), $src . 'missing.yml'],
            'unknown' => [$this->source('unknown.less', '.unknown {}')],
        ]);
        $maker->process('theme');

        $this->assertSame($this->lines([
            '@screen-breakpoint: 768px;',
            '@brand: red;',
            '// ' . $src . 'mixins.less',
            '.m() { color: @brand; }',
            '',
            '// ' . $src . 'normalize.less',
            'html { margin: 0; }',
            '',
            '// ' . $src . 'animations.less',
            '@keyframes spin { to { opacity: 1; } }',
            '',
            '// ' . $src . 'fonts.less',
            '.font { font-family: serif; }',
            '',
            '// ' . $src . 'structure.less',
            '.structure { .m(); }',
            '',
            '// ' . $src . 'icons.less',
            '.icon { width: 1em; }',
            '',
            '@media(max-width: @screen-breakpoint - 1px){',
            '// ' . $src . 'mobile.less',
            '.mobile { display: block; }',
            '',
            '}',
            '@media(min-width: @screen-breakpoint){',
            '// ' . $src . 'screen.less',
            '.screen { display: block; }',
            '',
            '}',
            '// ' . $src . 'queries.less',
            '@media (hover: hover) { .queries { color: blue; } }',
            '',
            '@media print {',
            '// ' . $src . 'print.less',
            '.print { display: none; }',
            '',
            '}',
            '',
        ]), file_get_contents($this->targetCssDir . DIRECTORY_SEPARATOR . 'theme.less'));
    }

    public function testProcessPrependsTheFontFacesAndCopiesTheFontFiles(): void
    {
        $src = $this->tempDir . DIRECTORY_SEPARATOR;
        $woff2 = $this->fixtureCopy('fonts' . DIRECTORY_SEPARATOR . 'test-font.woff2');
        $maker = $this->stubbedMaker();

        $maker->addFont((object) [
            'id' => 'test-font',
            'family' => 'Test Font',
            'display' => 'swap',
            'style' => 'normal',
            'weight' => '400',
            'files' => ['woff2' => $woff2, 'less' => $this->source('test-font.less', ".test-font { font-family: 'Test Font'; }")],
        ]);
        // same id: the first font stays
        $maker->addFont((object) ['id' => 'test-font', 'family' => 'Ignored', 'files' => ['woff' => $woff2]]);
        // a missing file is still declared, with nothing to copy
        $maker->addFont((object) ['id' => 'missing', 'family' => 'Missing', 'files' => ['woff' => $src . 'missing.woff']]);
        $maker->process('theme');

        $this->assertSame($this->lines([
            '@font-face {',
            '  font-display: swap;',
            "  font-family: 'Test Font';",
            '  font-style: normal;',
            '  font-weight: 400;',
            "  src: url('@{PATH_FONTS}test-font.woff2') format('woff2');",
            '}',
            '',
            '@font-face {',
            "  font-family: 'Missing';",
            "  src: url('@{PATH_FONTS}missing.woff') format('woff');",
            '}',
            '',
            '',
            '',
            '@screen-breakpoint: 900px;',
            '// ' . $src . 'test-font.less',
            ".test-font { font-family: 'Test Font'; }",
            '',
            '',
        ]), file_get_contents($this->targetCssDir . DIRECTORY_SEPARATOR . 'theme.less'));
        $this->assertSame(['test-font.woff2'], $this->listing($this->targetFontsDir));
    }

    public function testProcessCleansTheCompiledCssThenPostprocessesAndMinifiesIt(): void
    {
        $this->stubbedMaker()->process('theme');

        $this->assertSame(['theme.css', 'theme.less', 'theme.min.css'], $this->listing($this->targetCssDir));
        $this->assertSame([], $this->listing($this->tempDir . DIRECTORY_SEPARATOR . 'tmp'), 'The temporary variables file is removed');
        $this->assertSame(
            '.compiled-from-theme { color: red; } /* postcss */',
            file_get_contents($this->targetCssDir . DIRECTORY_SEPARATOR . 'theme.css')
        );
        $this->assertSame(
            '/* min */.compiled-from-theme { color: red; } /* postcss */',
            file_get_contents($this->targetCssDir . DIRECTORY_SEPARATOR . 'theme.min.css')
        );
    }

    public function testProcessBuildsTheThemeWithTheNodeTools(): void
    {
        $this->requireTool('lessc');
        $this->requireTool('postcss');
        $this->requireTool('minify');

        $this->cssMaker->addLessFiles([
            'variables' => [$this->fixtureCopy('less' . DIRECTORY_SEPARATOR . 'variables.yml')],
            'mixins' => [$this->fixtureCopy('less' . DIRECTORY_SEPARATOR . 'mixin.less')],
            'normalize' => [$this->fixtureCopy('less' . DIRECTORY_SEPARATOR . 'normalize.less')],
            'structure' => [$this->fixtureCopy('less' . DIRECTORY_SEPARATOR . 'valid.less')],
            'mobile' => [$this->source('mobile.less', '.nav { display: none; }')],
            'screen' => [$this->source('screen.less', '.nav { display: flex; }')],
            'print' => [$this->source('print.less', '.nav { display: none; }')],
        ]);
        $this->cssMaker->process('theme');

        $this->assertSame(['theme.css', 'theme.less', 'theme.min.css'], $this->listing($this->targetCssDir));
        $this->assertSame([], $this->listing($this->tempDir . DIRECTORY_SEPARATOR . 'tmp'));
        $this->assertSame(
            'html { font-family: "Helvetica Neue", Arial, sans-serif; font-size: 14px; line-height: 1.5; }'
                . ' body { margin: 0; padding: 0; background: #fff; color: #333; }'
                . ' * { box-sizing: border-box; }'
                . ' .test-class { color: #333; font-size: 14px; }'
                . ' .test-class .nested { background: #666666; }'
                . ' @media (max-width: 899px) { .nav { display: none; } }'
                . ' @media (min-width: 900px) { .nav { display: flex; } }'
                . ' @media print { .nav { display: none; } }',
            file_get_contents($this->targetCssDir . DIRECTORY_SEPARATOR . 'theme.css')
        );
        $this->assertSame(
            'html{font-family:Helvetica Neue,Arial,sans-serif;font-size:14px;line-height:1.5}'
                . 'body{color:#333;background:#fff;margin:0;padding:0}'
                . '*{box-sizing:border-box}'
                . '.test-class{color:#333;font-size:14px}'
                . '.test-class .nested{background:#666}'
                . '@media (width<=899px){.nav{display:none}}'
                . '@media (width>=900px){.nav{display:flex}}'
                . '@media print{.nav{display:none}}' . "\n",
            file_get_contents($this->targetCssDir . DIRECTORY_SEPARATOR . 'theme.min.css')
        );
    }

    private function stubbedMaker(): CssMaker
    {
        return (new CssMaker(null, Helper::stubTools($this->tempDir)))->setBuildPaths($this->tempDir, 'build');
    }

    private function source(string $name, string $content): string
    {
        $path = $this->tempDir . DIRECTORY_SEPARATOR . $name;
        file_put_contents($path, $content);

        return $path;
    }

    private function lines(array $lines): string
    {
        return implode("\n", $lines);
    }

    private function listing(string $dir): array
    {
        $files = array_values(array_diff(scandir($dir), ['.', '..']));
        sort($files);

        return $files;
    }
}
