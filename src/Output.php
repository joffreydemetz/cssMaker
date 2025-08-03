<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\CssMaker;

/**
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class Output
{
  public const VERBOSITY_STEP = 1;
  public const VERBOSITY_ERROR = 4;
  public const VERBOSITY_WARN = 8;
  public const VERBOSITY_INFO = 16;
  public const VERBOSITY_ALL = 32;

  private string $mode = '';
  private int $verbosity = 0;
  private array $output = [];

  public function __construct(string $mode = '')
  {
    if ('cli' === \PHP_SAPI) {
      $mode = 'cli';
    }
    $this->mode = $mode;
  }

  public function __toString(): string
  {
    return implode("\n", $this->output);
  }

  public function setVerbosity(int $verbosity = 0): self
  {
    $this->verbosity = $verbosity;
    return $this;
  }

  public function add(string $message, string $tag = 'info'): void
  {
    $output = '[' . strtoupper($tag) . '] ' . $message;

    $this->output[] = $output;

    if (!$this->verbosity) {
      return;
    }

    switch ($tag) {
      case 'step':
        break;

      case 'error':
        break;

      case 'info':
        if ($this->verbosity < self::VERBOSITY_INFO) {
          return;
        }
        break;

      case 'warn':
        if ($this->verbosity < self::VERBOSITY_WARN) {
          return;
        }
        break;

      case 'dump':
        if ($this->verbosity < self::VERBOSITY_ALL) {
          return;
        }
        break;

      default:
        return;
    }

    if ('cli' === $this->mode) {
      echo $output . "\n";
      return;
    }
  }

  public function step(string $message)
  {
    $this->add($message, 'step');
  }

  public function error(string $message)
  {
    $this->add($message, 'error');
  }

  public function warn(string $message)
  {
    $this->add($message, 'warn');
  }

  public function info(string $message)
  {
    $this->add($message, 'info');
  }

  public function dump(string $message)
  {
    $this->add($message, 'dump');
  }

  public function export(): void
  {
    if (empty($this->output)) {
      return;
    }

    echo implode("\n", $this->output);
  }
}
