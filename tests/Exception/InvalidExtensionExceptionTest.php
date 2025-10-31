<?php

namespace Tourze\TLSExtensionNaming\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;
use Tourze\TLSExtensionNaming\Exception\InvalidExtensionException;

/**
 * InvalidExtensionException 测试类
 *
 * @internal
 */
#[CoversClass(InvalidExtensionException::class)]
final class InvalidExtensionExceptionTest extends AbstractExceptionTestCase
{
    /**
     * 测试异常继承关系
     */
    public function testExtendsInvalidArgumentException(): void
    {
        $exception = new InvalidExtensionException('Invalid extension');
        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
    }

    /**
     * 测试异常消息
     */
    public function testExceptionMessage(): void
    {
        $message = 'Extension data is invalid';
        $exception = new InvalidExtensionException($message);
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * 测试异常代码
     */
    public function testExceptionCode(): void
    {
        $code = 400;
        $exception = new InvalidExtensionException('Invalid', $code);
        $this->assertEquals($code, $exception->getCode());
    }

    /**
     * 测试异常链
     */
    public function testExceptionChaining(): void
    {
        $previous = new \Exception('Original error');
        $exception = new InvalidExtensionException('Invalid extension', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}
