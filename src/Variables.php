<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\CssMaker;

use JDZ\Utils\Data as jData;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Variables extends jData
{
  public function addFromFile(string $path)
  {
    try {
      if ($vars = Yaml::parseFile($path)) {
        $this->sets((array)$vars);
      }
    } catch (\Symfony\Component\Yaml\Exception\ParseException $e) {
    }

    return $this;
  }

  public function exportVars(): array
  {
    $data = [];
    foreach ($this->data as $key => $value) {
      $data[] = '@' . $key . ': ' . ($value === '' ? '""' : $value) . ';';
    }
    return $data;
  }

  public function toFile(): string
  {
    return implode("\n", $this->exportVars());
  }
}
