<?php

namespace Tourze\TLSExtensionNaming\Tests\Unit\Exception;

use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionNaming\Exception\ExtensionEncodingException;
use Tourze\TLSExtensionNaming\Exception\TLSExtensionException;

/**
 * ExtensionEncodingException 测试类
 */
class ExtensionEncodingExceptionTest extends TestCase
{
    /**
     * 测试异常继承关系
     */
    public function testExtendsTLSExtensionException(): void
    {
        $exception = new ExtensionEncodingException('Encoding error');
        $this->assertInstanceOf(TLSExtensionException::class, $exception);
    }

    /**
     * 测试异常消息
     */
    public function testExceptionMessage(): void
    {
        $message = 'Failed to encode extension';
        $exception = new ExtensionEncodingException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * 测试异常代码
     */
    public function testExceptionCode(): void
    {
        $code = 500;
        $exception = new ExtensionEncodingException('Error', $code);
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * 测试空构造函数
     */
    public function testEmptyConstructor(): void
    {
        $exception = new ExtensionEncodingException();
        $this->assertEquals('', $exception->getMessage());
        $this->assertEquals(0, $exception->getCode());
    }
}