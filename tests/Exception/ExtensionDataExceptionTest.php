<?php

namespace Tourze\TLSExtensionNaming\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TLSExtensionNaming\Exception\ExtensionDataException;

/**
 * ExtensionDataException 测试类
 *
 * @internal
 */
#[CoversClass(ExtensionDataException::class)]
final class ExtensionDataExceptionTest extends AbstractExceptionTestCase
{
    /**
     * 测试异常继承关系
     */
    public function testExtendsInvalidArgumentException(): void
    {
        $exception = new ExtensionDataException('Test message');
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }

    /**
     * 测试异常消息
     */
    public function testExceptionMessage(): void
    {
        $message = 'Extension data processing failed';
        $exception = new ExtensionDataException($message);

        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * 测试异常代码
     */
    public function testExceptionCode(): void
    {
        $code = 1001;
        $exception = new ExtensionDataException('Test message', $code);

        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * 测试异常链
     */
    public function testExceptionChaining(): void
    {
        $previousException = new \RuntimeException('Previous exception');
        $exception = new ExtensionDataException('Test message', 0, $previousException);

        $this->assertSame($previousException, $exception->getPrevious());
    }
}
