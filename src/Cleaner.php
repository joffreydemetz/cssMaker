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
    // ASCII whitespace only, byte-wise: mb_ereg's \s also took the no-break space,
    // and returned null on bytes that are not UTF-8 (a TypeError); trim() works
    // on PHP 8.2 where mb_trim() does not exist
    // (\x0B, not PCRE's \v: that class includes byte 0x85, inside UTF-8 letters)
    $this->css = (string) preg_replace('/[ \t\n\f\x0B]+/', ' ', $this->css);
    $this->css = trim($this->css, " \t\n\f\v");
    return $this;
  }

  public function removeComments(): self
  {
    // block comments with the whitespace before them, each up to its own */ (an
    // empty one used to run on to the next comment). No "//" pattern: lessc already
    // drops // comments, and the old one only ever cut URLs
    $css = preg_replace('/\s*\/\*.*?\*\//s', ' ', $this->css);

    if (null === $css) {
      throw new \Exception('Error removing comments ' . \preg_last_error() . " \n\n " . \preg_last_error_msg());
    }

    $this->css = $css;
    return $this;
  }
}
