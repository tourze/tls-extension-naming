<?php

namespace Tourze\TLSExtensionNaming\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TLSExtensionNaming\Exception\TLSExtensionException;
use Tourze\TLSExtensionNaming\Exception\UnknownExtensionTypeException;

/**
 * UnknownExtensionTypeException 测试类
 *
 * @internal
 */
#[CoversClass(UnknownExtensionTypeException::class)]
final class UnknownExtensionTypeExceptionTest extends AbstractExceptionTestCase
{
    /**
     * 测试异常继承关系
     */
    public function testExtendsTLSExtensionException(): void
    {
        $exception = new UnknownExtensionTypeException('Unknown type');
        $this->assertInstanceOf(TLSExtensionException::class, $exception);
    }

    /**
     * 测试异常消息
     */
    public function testExceptionMessage(): void
    {
        $message = 'Extension type 0x9999 is not supported';
        $exception = new UnknownExtensionTypeException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * 测试异常代码
     */
    public function testExceptionCode(): void
    {
        $code = 404;
        $exception = new UnknownExtensionTypeException('Not found', $code);
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * 测试创建带类型值的异常
     */
    public function testCreateWithTypeValue(): void
    {
        $type = 0x1234;
        $message = sprintf('Unknown extension type: 0x%04x', $type);
        $exception = new UnknownExtensionTypeException($message);
        $this->assertStringContainsString('0x1234', $exception->getMessage());
    }
}
