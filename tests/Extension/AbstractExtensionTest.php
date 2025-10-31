<?php

namespace Tourze\TLSExtensionNaming\Tests\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionNaming\Extension\AbstractExtension;
use Tourze\TLSExtensionNaming\Extension\ExtensionInterface;

/**
 * AbstractExtension 测试类
 *
 * @internal
 */
#[CoversClass(AbstractExtension::class)]
final class AbstractExtensionTest extends TestCase
{
    /**
     * 测试抽象类
     *
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
     */
    #[DataProvider('uint16Provider')]
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
    public static function uint16Provider(): array
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
    public static function uint16DecodeProvider(): array
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
        [$value1, $offset] = $this->extension::testDecodeUint16($data, $offset);
        $this->assertEquals(10, $value1);
        $this->assertEquals(2, $offset);

        /** @phpstan-ignore-next-line */
        [$value2, $offset] = $this->extension::testDecodeUint16($data, $offset);
        $this->assertEquals(11, $value2);
        $this->assertEquals(4, $offset);

        /** @phpstan-ignore-next-line */
        [$value3, $offset] = $this->extension::testDecodeUint16($data, $offset);
        $this->assertEquals(12, $value3);
        $this->assertEquals(6, $offset);
    }

    /**
     * 测试 decodeUint16 方法
     */
    #[DataProvider('uint16DecodeProvider')]
    public function testDecodeUint16(string $data, int $expectedValue, int $expectedOffset): void
    {
        /** @phpstan-ignore-next-line */
        $result = $this->extension::testDecodeUint16($data, 0);

        $this->assertEquals($expectedValue, $result[0]);
        $this->assertEquals($expectedOffset, $result[1]);
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
        parent::setUp();

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
                return new self();
            }

            // 暴露受保护的方法以便测试
            public function testEncodeUint16(int $value): string
            {
                return $this->encodeUint16($value);
            }

            /**
             * @return array{0: int, 1: int}
             */
            public static function testDecodeUint16(string $data, int $offset): array
            {
                return parent::decodeUint16($data, $offset);
            }
        };
    }
}
