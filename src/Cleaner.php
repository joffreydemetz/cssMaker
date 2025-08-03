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
class Cleaner
{
  private string $css;

  public function __construct(string $css)
  {
    $this->css = $css;
  }

  public function getCss(): string
  {
    return $this->css;
  }

  public function removeSpaces(): self
  {
    $this->css = str_replace("\r\n", "\n", $this->css);
    $this->css = str_replace("\r", "\n", $this->css);
    //$this->css = mb_ereg_replace("\s+", " ", $this->css);
    $this->css = mb_trim($this->css);
    return $this;
  }

  public function removeComments(): self
  {
    $this->css = preg_replace("/(\s*\/[*]{1,}\s*.+\s*[*]{1,}\/\s*)/mUs", " ", $this->css);
    // $this->css = preg_replace("/\s+/", " ", $this->css);

    if (!$this->css && \preg_last_error() !== \PREG_NO_ERROR) {
      throw new \Exception('Error removing comments ' . \preg_last_error() . " \n\n " . \preg_last_error_msg());
    }

    return $this;
  }
}
