<?php

namespace Tourze\TLSExtensionNaming\Tests\Unit\Extension;

use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionNaming\Extension\ExtensionInterface;

/**
 * ExtensionInterface 测试类
 */
class ExtensionInterfaceTest extends TestCase
{
    /**
     * 测试接口方法定义
     */
    public function testInterfaceMethods(): void
    {
        $methods = get_class_methods(ExtensionInterface::class);
        
        $this->assertContains('getType', $methods);
        $this->assertContains('encode', $methods);
        $this->assertContains('decode', $methods);
        $this->assertContains('isApplicableForVersion', $methods);
    }

    /**
     * 测试接口实现
     */
    public function testInterfaceImplementation(): void
    {
        $extension = new class implements ExtensionInterface {
            public function getType(): int
            {
                return 0x0000;
            }

            public function encode(): string
            {
                return 'test';
            }

            public static function decode(string $data): static
            {
                return new static();
            }

            public function isApplicableForVersion(string $tlsVersion): bool
            {
                return true;
            }
        };

        $this->assertInstanceOf(ExtensionInterface::class, $extension);
        $this->assertEquals(0x0000, $extension->getType());
        $this->assertEquals('test', $extension->encode());
        $this->assertTrue($extension->isApplicableForVersion('1.3'));
        
        $decoded = $extension::decode('data');
        $this->assertInstanceOf(ExtensionInterface::class, $decoded);
    }

    /**
     * 测试静态方法存在性
     */
    public function testStaticMethodExists(): void
    {
        $reflectionClass = new \ReflectionClass(ExtensionInterface::class);
        $methods = $reflectionClass->getMethods();
        
        $staticMethodFound = false;
        foreach ($methods as $method) {
            if ($method->getName() === 'decode' && $method->isStatic()) {
                $staticMethodFound = true;
                break;
            }
        }
        
        $this->assertTrue($staticMethodFound, 'decode method should be static');
    }
}