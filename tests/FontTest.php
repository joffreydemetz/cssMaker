<?php

namespace JDZ\CssMaker\Tests;

use JDZ\CssMaker\Font;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Font::class)]
class FontTest extends TestCase
{
    /**
     * One format per row: the order several formats are listed in is not pinned here.
     */
    public static function fontFaceProvider(): array
    {
        return [
            'woff2' => [
                ['files' => ['woff2' => '/src/fonts/test-font.woff2']],
                "@font-face {\n  font-family: 'Test Font';\n  src: url('@{PATH_FONTS}test-font.woff2') format('woff2');\n}",
            ],
            'woff' => [
                ['files' => ['woff' => '/src/fonts/test-font.woff']],
                "@font-face {\n  font-family: 'Test Font';\n  src: url('@{PATH_FONTS}test-font.woff') format('woff');\n}",
            ],
            'ttf' => [
                ['files' => ['ttf' => '/src/fonts/test-font.ttf']],
                "@font-face {\n  font-family: 'Test Font';\n  src: url('@{PATH_FONTS}test-font.ttf') format('truetype');\n}",
            ],
            'eot, with the IE query' => [
                ['files' => ['eot' => '/src/fonts/test-font.eot']],
                "@font-face {\n  font-family: 'Test Font';\n  src: url('@{PATH_FONTS}test-font.eot?#iefix') format('embedded-opentype');\n}",
            ],
            'svg, anchored on the family without spaces' => [
                ['files' => ['svg' => '/src/fonts/test-font.svg']],
                "@font-face {\n  font-family: 'Test Font';\n  src: url('@{PATH_FONTS}test-font.svg#TestFont') format('svg');\n}",
            ],
            'display, style and weight' => [
                ['display' => 'swap', 'style' => 'italic', 'weight' => '700', 'files' => ['woff2' => '/src/fonts/test-font.woff2']],
                "@font-face {\n  font-display: swap;\n  font-family: 'Test Font';\n  font-style: italic;\n  font-weight: 700;\n  src: url('@{PATH_FONTS}test-font.woff2') format('woff2');\n}",
            ],
            // browsers take the first format they support: TTF used to come first, so WOFF2 was never used
            'every format, the IE fallback then the smallest' => [
                ['files' => [
                    'ttf' => '/src/fonts/test-font.ttf',
                    'svg' => '/src/fonts/test-font.svg',
                    'woff' => '/src/fonts/test-font.woff',
                    'eot' => '/src/fonts/test-font.eot',
                    'woff2' => '/src/fonts/test-font.woff2',
                ]],
                "@font-face {\n  font-family: 'Test Font';\n  src: url('@{PATH_FONTS}test-font.eot?#iefix') format('embedded-opentype'), "
                . "url('@{PATH_FONTS}test-font.woff2') format('woff2'), url('@{PATH_FONTS}test-font.woff') format('woff'), "
                . "url('@{PATH_FONTS}test-font.ttf') format('truetype'), url('@{PATH_FONTS}test-font.svg#TestFont') format('svg');\n}",
            ],
            'files that are not font formats are left out' => [
                ['files' => ['less' => '/src/fonts/test-font.less', 'otf' => '/src/fonts/test-font.otf', 'woff2' => '/src/fonts/test-font.woff2']],
                "@font-face {\n  font-family: 'Test Font';\n  src: url('@{PATH_FONTS}test-font.woff2') format('woff2');\n}",
            ],
        ];
    }

    #[DataProvider('fontFaceProvider')]
    public function testGetFontFaceCss(array $data, string $expected): void
    {
        $font = new Font(['id' => 'test-font', 'family' => 'Test Font'] + $data);

        $this->assertSame($expected, $font->load()->getFontFaceCss());
    }
}
