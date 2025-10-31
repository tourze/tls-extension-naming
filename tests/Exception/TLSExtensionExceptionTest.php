<?php

namespace Tourze\TLSExtensionNaming\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TLSExtensionNaming\Exception\TLSExtensionException;

/**
 * TLSExtensionException 测试类
 *
 * @internal
 */
#[CoversClass(TLSExtensionException::class)]
final class TLSExtensionExceptionTest extends AbstractExceptionTestCase
{
    /**
     * 测试异常是RuntimeException的子类
     */
    public function testExtendsRuntimeException(): void
    {
        $exception = new class('Test message') extends TLSExtensionException {};
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    /**
     * 测试异常消息
     */
    public function testExceptionMessage(): void
    {
        $message = 'Test exception message';
        $exception = new class($message) extends TLSExtensionException {};
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * 测试异常代码
     */
    public function testExceptionCode(): void
    {
        $code = 123;
        $exception = new class('Test', $code) extends TLSExtensionException {};
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * 测试异常链
     */
    public function testExceptionChaining(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new class('Test', 0, $previous) extends TLSExtensionException {};
        $this->assertSame($previous, $exception->getPrevious());
    }
}
