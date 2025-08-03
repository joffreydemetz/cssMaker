<?php

namespace JDZ\CssMaker\Tests\Exception;

use JDZ\CssMaker\Exception\LessMakerException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \JDZ\CssMaker\Exception\LessMakerException
 */
class LessMakerExceptionTest extends TestCase
{
    public function testConstructor(): void
    {
        $exception = new LessMakerException();
        $this->assertInstanceOf(LessMakerException::class, $exception);
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testConstructorWithMessage(): void
    {
        $message = 'Test error message';
        $exception = new LessMakerException($message);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithMessageAndCode(): void
    {
        $message = 'Test error message';
        $code = 123;
        $exception = new LessMakerException($message, $code);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testConstructorWithPrevious(): void
    {
        $message = 'Test error message';
        $code = 123;
        $previous = new \Exception('Previous exception');
        $exception = new LessMakerException($message, $code, $previous);

        $this->assertEquals($message, $exception->getMessage());
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

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

    public function testFromErrorWithDifferentExceptionTypes(): void
    {
        $exceptions = [
            new \InvalidArgumentException('Invalid argument'),
            new \LogicException('Logic error'),
            new \OutOfBoundsException('Out of bounds'),
        ];

        foreach ($exceptions as $originalException) {
            $exception = LessMakerException::fromError($originalException);

            $this->assertInstanceOf(LessMakerException::class, $exception);
            $this->assertEquals($originalException->getMessage(), $exception->getMessage());
            $this->assertEquals($originalException->getCode(), $exception->getCode());
            $this->assertSame($originalException, $exception->getPrevious());
        }
    }

    public function testExceptionChaining(): void
    {
        $rootCause = new \Exception('Root cause');
        $intermediate = new \RuntimeException('Intermediate error', 0, $rootCause);
        $exception = LessMakerException::fromError($intermediate);

        $this->assertEquals('Intermediate error', $exception->getMessage());
        $this->assertSame($intermediate, $exception->getPrevious());
        $this->assertSame($rootCause, $exception->getPrevious()->getPrevious());
    }

    public function testInheritance(): void
    {
        $exception = new LessMakerException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }
}
