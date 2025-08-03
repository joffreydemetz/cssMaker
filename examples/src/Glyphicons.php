<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/Icons/GlyphiconsRegular.php';
require_once __DIR__ . '/Icons/GlyphiconsHalflings.php';
require_once __DIR__ . '/Icons/GlyphiconsFiletypes.php';
require_once __DIR__ . '/Icons/GlyphiconsSocial.php';

/**
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Glyphicons
{
  private array $selection = [
    'glyphicons' => [],
    'halflings' => [],
    'filetypes' => [],
    'social' => [],
  ];

  public function addSelection(array $selection = []): self
  {
    foreach ($selection as $type => $icons) {
      $this->addTypeIcons($type, $icons);
    }

    return $this;
  }

  public function addTypeIcons(string $type, array $icons): self
  {
    if (!isset($this->selection[$type])) {
      throw new \Exception('Unknown Glyphicons type : ' . $type);
    }

    $icons = \array_unique($icons);

    foreach ($icons as $icon) {
      $this->addIcon($type, $icon);
    }

    return $this;
  }

  public function isEmpty(): bool
  {
    return empty($this->selection['glyphicons'])
      && empty($this->selection['halflings'])
      && empty($this->selection['filetypes'])
      && empty($this->selection['social']);
  }

  public function getFonts(): array
  {
    $fonts = [];
    if (!empty($this->selection['glyphicons'])) {
      $fonts[] = 'regular';
    }
    if (!empty($this->selection['halflings'])) {
      $fonts[] = 'halflings';
    }
    if (!empty($this->selection['filetypes'])) {
      $fonts[] = 'filetypes';
    }
    if (!empty($this->selection['social'])) {
      $fonts[] = 'social';
    }
    return $fonts;
  }

  public function toFile(): string
  {
    $selection = [];

    foreach ($this->selection as $type => $icons) {
      if (empty($icons)) {
        continue;
      }

      \ksort($icons);
      foreach ($icons as $icon => $char) {
        $selection[] = ".icon('" . $type . "', '" . $icon . "', 'E" . $char . "');";
      }
    }

    return implode("\n", $selection);
  }

  private function addIcon(string $type, string $icon): void
  {
    if (!empty($this->selection[$type][$icon])) {
      return;
    }

    try {
      $iconAlias = str_replace('-', '_', strtoupper($icon));

      switch ($type) {
        case 'glyphicons':
          $char = constant("GlyphiconsRegular::$iconAlias")->value;
          break;

        case 'halflings':
          $char = constant("GlyphiconsHalflings::$iconAlias")->value;
          break;

        case 'filetypes':
          $char = constant("GlyphiconsFiletypes::$iconAlias")->value;
          break;

        case 'social':
          $char = constant("GlyphiconsSocial::$iconAlias")->value;
          break;
      }
    } catch (\Throwable $e) {
      throw new \Exception('Icon not found : ' . $type . ' / ' . $icon . ' (' . $iconAlias . ')', 0, $e);
    }

    $this->selection[$type][$icon] = $char;
  }
}
