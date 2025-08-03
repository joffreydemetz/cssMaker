<?php

/**
 * (c) Joffrey Demetz <joffrey.demetz@gmail.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JDZ\CssMaker\Exception;

/**
 * @author  Joffrey Demetz <joffrey.demetz@gmail.com>
 */
class LessMakerException extends \RuntimeException
{
    public function __construct($message = "", $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function fromError(\Throwable $e): self
    {
        return new self($e->getMessage(), $e->getCode(), $e);
    }
}
