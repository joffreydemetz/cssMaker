<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\CssMaker;

/**
 * @author Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Merger
{
  private array $variables = [];
  private array $mixins = [];
  private array $files = [];
  private string $less = '';

  public function getContent(): string
  {
    if ($this->variables || $this->mixins || $this->files) {
      $less = $this->less;

      $this->less = '';
      foreach ($this->variables as $key => $value) {
        $this->addString('@' . $key . ': ' . ($value === '' ? '""' : $value) . ';');
      }
      foreach ($this->mixins as $mixin) {
        $this->addFile($mixin);
      }
      foreach ($this->files as $file) {
        $this->addFile($file);
      }
      $this->less .= $less;
      $result = $this->less;
      $this->less = $less;

      return $result;
    }

    return $this->less;
  }

  public function setVariables(array $variables)
  {
    foreach ($variables as $key => $value) {
      $this->setVariable($key, $value);
    }
    return $this;
  }

  public function setVariable(string $name, string $value)
  {
    $this->variables[$name] = $value;
    return $this;
  }

  public function setMixins(array $mixins)
  {
    foreach ($mixins as $mixin) {
      $this->setMixin($mixin);
    }
    return $this;
  }

  public function setMixin(string $path)
  {
    if (file_exists($path)) {
      $this->mixins[] = $path;
    }

    return $this;
  }

  public function setFiles(array $files)
  {
    foreach ($files as $file) {
      $this->setFile($file);
    }
    return $this;
  }

  public function setFile(string $path)
  {
    if (file_exists($path)) {
      $this->files[] = $path;
    }
    return $this;
  }

  public function addFile(string $path)
  {
    if (file_exists($path)) {
      $content = file_get_contents($path);
      $content = trim($content);

      if ($content) {
        $this->less .= '// ' . $path . "\n";
        $this->less .= $content . "\n";
        $this->less .= "\n";
      }
    }

    return $this;
  }

  public function addString(string $str)
  {
    $str = trim($str);
    if ($str) {
      $this->less .= $str . "\n";
    }
    return $this;
  }
}
