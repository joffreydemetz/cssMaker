<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\CssMaker;

/**
 * Font class to handle font properties and formats
 * 
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Font
{
  public string $type = 'local';
  public string $id = '';
  public string $family = '';
  public string $style = '';
  public string $weight = '';
  public string $display = '';

  public array $files = [];
  public array $formats = [];

  public function __construct(array $data)
  {
    $this->formats = [
      'ttf' => null,
      'eot' => null,
      'woff2' => null,
      'woff' => null,
      'svg' => null,
    ];

    $this->setProperties($data);
  }

  public function setProperties(array $data): self
  {
    foreach ($data as $key => $value) {
      $this->$key = $value;
    }
    return $this;
  }

  public function load(): self
  {
    foreach ($this->files as $format => $file) {
      if (!in_array($format, ['eot', 'ttf', 'woff', 'woff2', 'svg'])) {
        continue;
      }

      $fi = new \SplFileInfo($file);
      $filename = $fi->getBasename();

      $this->formats[$format] = (object)[
        'source' => $file,
        'filename' => $filename,
        'fontFace' => '',
      ];

      switch ($format) {
        // IE6-IE8
        case 'eot':
          $this->formats[$format]->fontFace = "url('@{PATH_FONTS}" . $filename . "?#iefix') format('embedded-opentype')";
          break;
        // Super Modern Browsers
        case 'woff2':
          $this->formats[$format]->fontFace = "url('@{PATH_FONTS}" . $filename . "') format('woff2')";
          break;
        // Pretty Modern Browsers
        case 'woff':
          $this->formats[$format]->fontFace = "url('@{PATH_FONTS}" . $filename . "') format('woff')";
          break;
        // Safari, Android, iOS
        case 'ttf':
          $this->formats[$format]->fontFace = "url('@{PATH_FONTS}" . $filename . "') format('truetype')";
          break;
        // Legacy iOS
        case 'svg':
          $this->formats[$format]->fontFace = "url('@{PATH_FONTS}" . $filename . "#" . str_replace(' ', '', $this->family) . "') format('svg')";
          // $this->formats[$format]->fontFace = "url('@{PATH_FONTS}".$filename."#svgFontName') format('svg')";
          break;
      }
    }

    return $this;
  }

  public function getTheme(): string|false
  {
    return empty($this->files['less']) ? false : $this->files['less'];
  }

  public function getFormatFiles(): array
  {
    $files = [];
    foreach ($this->formats as $data) {
      if (empty($data)) {
        continue;
      }

      $files[] = (object)[
        'source' => $data->source,
        'filename' => $data->filename,
      ];
    }
    return $files;
  }

  public function getFontFaceCss(): string
  {
    $faces = [];

    foreach ($this->formats as $data) {
      if (empty($data)) {
        continue;
      }

      if ($data->fontFace) {
        $faces[] = $data->fontFace;
      }
    }

    $css = [];

    $css[] = "@font-face {";
    if ($this->display) {
      $css[] = "  font-display: " . $this->display . ";";
    }
    $css[] = "  font-family: '" . $this->family . "';";
    if ($this->style) {
      $css[] = "  font-style: " . $this->style . ";";
    }
    if ($this->weight) {
      $css[] = "  font-weight: " . $this->weight . ";";
    }

    $css[] = "  src: " . implode(", ", $faces) . ";";
    $css[] = "}";

    return implode("\n", $css);
  }
}
