<?php

namespace Tourze\TLSExtensionNaming\Tests\Extension;

use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionNaming\Extension\AbstractExtension;
use Tourze\TLSExtensionNaming\Extension\ExtensionInterface;

/**
 * AbstractExtension 测试类
 */
class AbstractExtensionTest extends TestCase
{
    /**
     * 测试抽象类
     * @phpstan-var object&AbstractExtension
     */
    private object $extension;
    
    /**
     * 测试扩展接口实现
     */
    public function testImplementsExtensionInterface(): void
    {
        $this->assertInstanceOf(ExtensionInterface::class, $this->extension);
    }
    
    /**
     * 测试 encodeUint16 方法
     *
     * @dataProvider uint16Provider
     */
    public function testEncodeUint16(int $value, string $expected): void
    {
        /** @phpstan-ignore-next-line */
        $result = $this->extension->testEncodeUint16($value);
        $this->assertEquals($expected, $result);
    }
    
    /**
     * 提供 uint16 测试数据
     *
     * @return array<array{int, string}>
     */
    public function uint16Provider(): array
    {
        return [
            [0, "\x00\x00"],
            [1, "\x00\x01"],
            [255, "\x00\xFF"],
            [256, "\x01\x00"],
            [65535, "\xFF\xFF"],
        ];
    }
    
    /**
     * 提供 uint16 解码测试数据
     *
     * @return array<array{string, int, int}>
     */
    public function uint16DecodeProvider(): array
    {
        return [
            ["\x00\x00", 0, 2],
            ["\x00\x01", 1, 2],
            ["\x00\xFF", 255, 2],
            ["\x01\x00", 256, 2],
            ["\xFF\xFF", 65535, 2],
            ["\x00\x0A\x00\x0B", 10, 2], // 测试偏移量更新
        ];
    }
    
    /**
     * 测试多次解码 uint16
     */
    public function testMultipleDecodeUint16(): void
    {
        $data = "\x00\x0A\x00\x0B\x00\x0C";
        $offset = 0;

        /** @phpstan-ignore-next-line */
        $value1 = $this->extension::testDecodeUint16($data, $offset);
        $this->assertEquals(10, $value1);
        $this->assertEquals(2, $offset);

        /** @phpstan-ignore-next-line */
        $value2 = $this->extension::testDecodeUint16($data, $offset);
        $this->assertEquals(11, $value2);
        $this->assertEquals(4, $offset);

        /** @phpstan-ignore-next-line */
        $value3 = $this->extension::testDecodeUint16($data, $offset);
        $this->assertEquals(12, $value3);
        $this->assertEquals(6, $offset);
    }
    
    /**
     * 测试 decodeUint16 方法
     *
     * @dataProvider uint16DecodeProvider
     */
    public function testDecodeUint16(string $data, int $expectedValue, int $expectedOffset): void
    {
        $offset = 0;
        /** @phpstan-ignore-next-line */
        $value = $this->extension::testDecodeUint16($data, $offset);

        $this->assertEquals($expectedValue, $value);
        $this->assertEquals($expectedOffset, $offset);
    }
    
    /**
     * 测试默认的 isApplicableForVersion 方法
     */
    public function testIsApplicableForVersion(): void
    {
        // 默认实现应该对所有版本返回 true
        $this->assertTrue($this->extension->isApplicableForVersion('1.0'));
        $this->assertTrue($this->extension->isApplicableForVersion('1.1'));
        $this->assertTrue($this->extension->isApplicableForVersion('1.2'));
        $this->assertTrue($this->extension->isApplicableForVersion('1.3'));
    }
    
    protected function setUp(): void
    {
        $this->extension = new class extends AbstractExtension {
            public function getType(): int
            {
                return 0x9999;
            }

            public function encode(): string
            {
                return 'test';
            }

            public static function decode(string $data): static
            {
                return new static();
            }

            // 暴露受保护的方法以便测试
            public function testEncodeUint16(int $value): string
            {
                return $this->encodeUint16($value);
            }

            public static function testDecodeUint16(string $data, int &$offset): int
            {
                return parent::decodeUint16($data, $offset);
            }
        };
    }
}