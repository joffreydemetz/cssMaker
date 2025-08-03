<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/Icons/FlagsEnum.php';

/**
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Flags
{
  private array $selection = [];

  public function addSelection(array $selection = []): self
  {
    foreach ($selection as $icons) {
      $icons = \array_unique($icons);

      foreach ($icons as $icon) {
        $this->addIcon($icon);
      }
    }
    return $this;
  }

  public function isEmpty(): bool
  {
    return empty($this->selection);
  }

  public function toFile(): string
  {
    $selection = [];

    foreach ($this->selection as $value) {
      $selection[] = ".flag-icon(" . $value . ");";
    }

    return implode("\n", $selection);
  }

  private function addIcon(string $icon): void
  {
    try {
      $iconAlias = str_replace('-', '_', strtoupper($icon));
      $char = constant("FlagsEnum::$iconAlias")->value;
    } catch (\Throwable $e) {
      throw new \Exception('Flag icon not found: ' . $icon, 0, $e);
    }

    if (!isset($this->selection[$icon])) {
      $this->selection[$icon] = $char;
    }
  }
}
