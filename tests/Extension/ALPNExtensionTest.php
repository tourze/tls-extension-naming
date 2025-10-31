<?php

namespace Tourze\TLSExtensionNaming\Tests\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionNaming\Exception\ExtensionEncodingException;
use Tourze\TLSExtensionNaming\Extension\ALPNExtension;
use Tourze\TLSExtensionNaming\Extension\ExtensionType;

/**
 * ALPNExtension 测试类
 *
 * @internal
 */
#[CoversClass(ALPNExtension::class)]
final class ALPNExtensionTest extends TestCase
{
    /**
     * 测试默认构造函数
     */
    public function testDefaultConstructor(): void
    {
        $extension = new ALPNExtension();
        $this->assertEmpty($extension->getProtocols());
    }

    /**
     * 测试带参数的构造函数
     */
    public function testConstructorWithProtocols(): void
    {
        $protocols = [
            ALPNExtension::PROTOCOL_HTTP_2,
            ALPNExtension::PROTOCOL_HTTP_1_1,
        ];

        $extension = new ALPNExtension($protocols);
        $this->assertEquals($protocols, $extension->getProtocols());
    }

    /**
     * 测试添加协议
     */
    public function testAddProtocol(): void
    {
        $extension = new ALPNExtension();

        $extension->addProtocol(ALPNExtension::PROTOCOL_HTTP_2);
        $this->assertEquals([ALPNExtension::PROTOCOL_HTTP_2], $extension->getProtocols());

        $extension->addProtocol(ALPNExtension::PROTOCOL_HTTP_1_1);
        $this->assertEquals([
            ALPNExtension::PROTOCOL_HTTP_2,
            ALPNExtension::PROTOCOL_HTTP_1_1,
        ], $extension->getProtocols());
    }

    /**
     * 测试添加重复协议
     */
    public function testAddDuplicateProtocol(): void
    {
        $extension = new ALPNExtension();

        $extension->addProtocol(ALPNExtension::PROTOCOL_HTTP_2);
        $extension->addProtocol(ALPNExtension::PROTOCOL_HTTP_2);

        // 不应该有重复
        $this->assertEquals([ALPNExtension::PROTOCOL_HTTP_2], $extension->getProtocols());
    }

    /**
     * 测试链式调用
     */
    public function testMethodChaining(): void
    {
        $extension = new ALPNExtension();

        $result = $extension->addProtocol(ALPNExtension::PROTOCOL_HTTP_2)
            ->addProtocol(ALPNExtension::PROTOCOL_HTTP_1_1)
        ;

        $this->assertSame($extension, $result);
    }

    /**
     * 测试获取扩展类型
     */
    public function testGetType(): void
    {
        $extension = new ALPNExtension();
        $this->assertEquals(ExtensionType::ALPN->value, $extension->getType());
    }

    /**
     * 测试编码空协议列表
     */
    public function testEncodeEmptyList(): void
    {
        $extension = new ALPNExtension();
        $encoded = $extension->encode();

        // 空列表应该编码为长度为0的列表
        $this->assertEquals("\x00\x00", $encoded);
    }

    /**
     * 测试编码单个协议
     */
    public function testEncodeSingleProtocol(): void
    {
        $extension = new ALPNExtension();
        $extension->addProtocol('h2');

        $encoded = $extension->encode();

        // 验证编码格式
        $expected = "\x00\x03" . // 列表长度 (3 bytes)
            "\x02" .      // 协议长度 (2 bytes)
            'h2';         // 协议名称

        $this->assertEquals($expected, $encoded);
    }

    /**
     * 测试编码多个协议
     */
    public function testEncodeMultipleProtocols(): void
    {
        $extension = new ALPNExtension();
        $extension->addProtocol('h2')
            ->addProtocol('http/1.1')
            ->addProtocol('h3')
        ;

        $encoded = $extension->encode();

        // 验证编码格式
        $expected = "\x00\x0F" .   // 列表长度 (15 bytes)
            "\x02" . 'h2' . // h2: 长度2 + 内容
            "\x08" . 'http/1.1' . // http/1.1: 长度8 + 内容
            "\x02" . 'h3';  // h3: 长度2 + 内容

        $this->assertEquals($expected, $encoded);
    }

    /**
     * 测试编码过长的协议名称
     */
    public function testEncodeTooLongProtocolName(): void
    {
        $extension = new ALPNExtension();
        $longProtocol = str_repeat('a', 256); // 超过255字节的限制

        $extension->addProtocol($longProtocol);

        $this->expectException(ExtensionEncodingException::class);
        $this->expectExceptionMessage('Protocol name too long');

        $extension->encode();
    }

    /**
     * 测试解码空列表
     */
    public function testDecodeEmptyList(): void
    {
        $data = "\x00\x00";
        $extension = ALPNExtension::decode($data);

        $this->assertEmpty($extension->getProtocols());
    }

    /**
     * 测试解码单个协议
     */
    public function testDecodeSingleProtocol(): void
    {
        $data = "\x00\x03" . // 列表长度
            "\x02" .      // 协议长度
            'h2';         // 协议名称

        $extension = ALPNExtension::decode($data);

        $this->assertEquals(['h2'], $extension->getProtocols());
    }

    /**
     * 测试解码多个协议
     */
    public function testDecodeMultipleProtocols(): void
    {
        $data = "\x00\x0F" .        // 列表长度
            "\x02" . 'h2' .      // h2
            "\x08" . 'http/1.1' . // http/1.1
            "\x02" . 'h3';       // h3

        $extension = ALPNExtension::decode($data);

        $this->assertEquals(['h2', 'http/1.1', 'h3'], $extension->getProtocols());
    }

    /**
     * 测试编码解码往返
     */
    public function testEncodeDecodeRoundTrip(): void
    {
        $original = new ALPNExtension([
            ALPNExtension::PROTOCOL_HTTP_2,
            ALPNExtension::PROTOCOL_HTTP_1_1,
            ALPNExtension::PROTOCOL_HTTP_3,
            ALPNExtension::PROTOCOL_SPDY_3_1,
        ]);

        $encoded = $original->encode();
        $decoded = ALPNExtension::decode($encoded);

        $this->assertEquals($original->getProtocols(), $decoded->getProtocols());
    }

    /**
     * 测试自定义协议
     */
    public function testCustomProtocols(): void
    {
        $customProtocols = [
            'custom-protocol-1',
            'my-app-protocol/v2',
            'test',
        ];

        $extension = new ALPNExtension($customProtocols);

        $encoded = $extension->encode();
        $decoded = ALPNExtension::decode($encoded);

        $this->assertEquals($customProtocols, $decoded->getProtocols());
    }

    /**
     * 测试协议常量
     */
    public function testProtocolConstants(): void
    {
        $this->assertEquals('http/1.1', ALPNExtension::PROTOCOL_HTTP_1_1);
        $this->assertEquals('h2', ALPNExtension::PROTOCOL_HTTP_2);
        $this->assertEquals('h3', ALPNExtension::PROTOCOL_HTTP_3);
        $this->assertEquals('spdy/3.1', ALPNExtension::PROTOCOL_SPDY_3_1);
    }

    /**
     * 测试最大长度协议名称（255字节）
     */
    public function testMaxLengthProtocolName(): void
    {
        $maxProtocol = str_repeat('x', 255);

        $extension = new ALPNExtension();
        $extension->addProtocol($maxProtocol);

        $encoded = $extension->encode();
        $decoded = ALPNExtension::decode($encoded);

        $this->assertEquals([$maxProtocol], $decoded->getProtocols());
    }
}
