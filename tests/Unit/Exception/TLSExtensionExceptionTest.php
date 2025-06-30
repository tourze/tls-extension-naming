<?php

namespace Tourze\TLSExtensionNaming\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tourze\TLSExtensionNaming\Exception\TLSExtensionException;

/**
 * TLSExtensionException 测试类
 */
class TLSExtensionExceptionTest extends TestCase
{
    /**
     * 测试异常是RuntimeException的子类
     */
    public function testExtendsRuntimeException(): void
    {
        $exception = new TLSExtensionException('Test message');
        $this->assertInstanceOf(RuntimeException::class, $exception);
    }

    /**
     * 测试异常消息
     */
    public function testExceptionMessage(): void
    {
        $message = 'Test exception message';
        $exception = new TLSExtensionException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * 测试异常代码
     */
    public function testExceptionCode(): void
    {
        $code = 123;
        $exception = new TLSExtensionException('Test', $code);
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * 测试异常链
     */
    public function testExceptionChaining(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new TLSExtensionException('Test', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}