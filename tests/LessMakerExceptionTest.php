<?php

namespace JDZ\CssMaker\Exception;

use JDZ\CssMaker\Exception\LessMakerException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LessMakerException::class)]
class LessMakerExceptionTest extends TestCase
{
    public function testFromError(): void
    {
        $originalMessage = 'Original error message';
        $originalCode = 456;
        $originalException = new \RuntimeException($originalMessage, $originalCode);

        $exception = LessMakerException::fromError($originalException);

        $this->assertInstanceOf(LessMakerException::class, $exception);
        $this->assertEquals($originalMessage, $exception->getMessage());
        $this->assertEquals($originalCode, $exception->getCode());
        $this->assertSame($originalException, $exception->getPrevious());
    }
}
