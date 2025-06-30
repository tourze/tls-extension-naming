<?php

namespace Tourze\TLSExtensionNaming\Tests\Extension;

use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionNaming\Exception\ExtensionEncodingException;
use Tourze\TLSExtensionNaming\Extension\ExtensionType;
use Tourze\TLSExtensionNaming\Extension\KeyShareExtension;

/**
 * KeyShareExtension 测试类
 */
class KeyShareExtensionTest extends TestCase
{
    /**
     * 测试默认构造函数
     */
    public function testDefaultConstructor(): void
    {
        $extension = new KeyShareExtension();
        $this->assertEmpty($extension->getKeyShares());
        $this->assertFalse($extension->isHelloRetryRequest());
        $this->assertNull($extension->getSelectedGroup());
    }

    /**
     * 测试带密钥共享的构造函数
     */
    public function testConstructorWithKeyShares(): void
    {
        $keyShares = [
            ['group' => KeyShareExtension::GROUP_X25519, 'key_exchange' => 'test_key_1'],
            ['group' => KeyShareExtension::GROUP_SECP256R1, 'key_exchange' => 'test_key_2']
        ];

        $extension = new KeyShareExtension($keyShares);
        $this->assertEquals($keyShares, $extension->getKeyShares());
        $this->assertFalse($extension->isHelloRetryRequest());
    }

    /**
     * 测试 HelloRetryRequest 构造函数
     */
    public function testHelloRetryRequestConstructor(): void
    {
        $selectedGroup = KeyShareExtension::GROUP_X25519;
        $extension = new KeyShareExtension([], true, $selectedGroup);

        $this->assertEmpty($extension->getKeyShares());
        $this->assertTrue($extension->isHelloRetryRequest());
        $this->assertEquals($selectedGroup, $extension->getSelectedGroup());
    }

    /**
     * 测试添加密钥共享
     */
    public function testAddKeyShare(): void
    {
        $extension = new KeyShareExtension();

        $extension->addKeyShare(KeyShareExtension::GROUP_X25519, 'key_data_1');
        $keyShares = $extension->getKeyShares();

        $this->assertCount(1, $keyShares);
        $this->assertEquals(KeyShareExtension::GROUP_X25519, $keyShares[0]['group']);
        $this->assertEquals('key_data_1', $keyShares[0]['key_exchange']);

        // 添加第二个密钥共享
        $extension->addKeyShare(KeyShareExtension::GROUP_SECP256R1, 'key_data_2');
        $keyShares = $extension->getKeyShares();

        $this->assertCount(2, $keyShares);
        $this->assertEquals(KeyShareExtension::GROUP_SECP256R1, $keyShares[1]['group']);
        $this->assertEquals('key_data_2', $keyShares[1]['key_exchange']);
    }

    /**
     * 测试链式调用
     */
    public function testMethodChaining(): void
    {
        $extension = new KeyShareExtension();

        $result = $extension->addKeyShare(KeyShareExtension::GROUP_X25519, 'key1')
            ->addKeyShare(KeyShareExtension::GROUP_SECP256R1, 'key2');

        $this->assertSame($extension, $result);
    }

    /**
     * 测试获取扩展类型
     */
    public function testGetType(): void
    {
        $extension = new KeyShareExtension();
        $this->assertEquals(ExtensionType::KEY_SHARE->value, $extension->getType());
    }

    /**
     * 测试编码空密钥共享列表
     */
    public function testEncodeEmptyKeyShares(): void
    {
        $extension = new KeyShareExtension();
        $encoded = $extension->encode();

        // 空列表应该编码为长度为0的列表
        $this->assertEquals("\x00\x00", $encoded);
    }

    /**
     * 测试编码单个密钥共享
     */
    public function testEncodeSingleKeyShare(): void
    {
        $extension = new KeyShareExtension();
        $extension->addKeyShare(KeyShareExtension::GROUP_X25519, 'key');

        $encoded = $extension->encode();

        // 验证编码格式
        $expected = "\x00\x07" .           // 列表长度 (7 bytes)
            "\x00\x1D" .           // 组 (X25519 = 0x001D)
            "\x00\x03" .           // 密钥长度 (3 bytes)
            "key";                 // 密钥数据

        $this->assertEquals($expected, $encoded);
    }

    /**
     * 测试编码多个密钥共享
     */
    public function testEncodeMultipleKeyShares(): void
    {
        $extension = new KeyShareExtension();
        $extension->addKeyShare(KeyShareExtension::GROUP_X25519, 'key1')
            ->addKeyShare(KeyShareExtension::GROUP_SECP256R1, 'key2');

        $encoded = $extension->encode();

        // 验证编码格式
        $expected = "\x00\x10" .           // 列表长度 (16 bytes)
            "\x00\x1D" .           // X25519
            "\x00\x04" . "key1" .  // 密钥1
            "\x00\x17" .           // SECP256R1
            "\x00\x04" . "key2";   // 密钥2

        $this->assertEquals($expected, $encoded);
    }

    /**
     * 测试编码 HelloRetryRequest
     */
    public function testEncodeHelloRetryRequest(): void
    {
        $extension = new KeyShareExtension([], true, KeyShareExtension::GROUP_X25519);
        $encoded = $extension->encode();

        // HelloRetryRequest 格式只有选择的组
        $expected = "\x00\x1D"; // X25519 = 0x001D

        $this->assertEquals($expected, $encoded);
    }

    /**
     * 测试编码 HelloRetryRequest 缺少选择组时抛出异常
     */
    public function testEncodeHelloRetryRequestWithoutSelectedGroup(): void
    {
        $extension = new KeyShareExtension([], true, null);

        $this->expectException(ExtensionEncodingException::class);
        $this->expectExceptionMessage('HelloRetryRequest must have a selected group');

        $extension->encode();
    }

    /**
     * 测试解码空密钥共享列表
     */
    public function testDecodeEmptyKeyShares(): void
    {
        $data = "\x00\x00"; // 看起来像空列表，但实际上被解释为 HelloRetryRequest，组为0
        $extension = KeyShareExtension::decode($data);

        // 由于长度为2字节，被解释为 HelloRetryRequest
        $this->assertTrue($extension->isHelloRetryRequest());
        $this->assertEquals(0, $extension->getSelectedGroup());
        $this->assertEmpty($extension->getKeyShares());
    }

    /**
     * 测试解码单个密钥共享
     */
    public function testDecodeSingleKeyShare(): void
    {
        $data = "\x00\x07" .           // 列表长度
            "\x00\x1D" .           // X25519
            "\x00\x03" .           // 密钥长度
            "key";                 // 密钥数据

        $extension = KeyShareExtension::decode($data);
        $keyShares = $extension->getKeyShares();

        $this->assertCount(1, $keyShares);
        $this->assertEquals(KeyShareExtension::GROUP_X25519, $keyShares[0]['group']);
        $this->assertEquals('key', $keyShares[0]['key_exchange']);
    }

    /**
     * 测试解码多个密钥共享
     */
    public function testDecodeMultipleKeyShares(): void
    {
        $data = "\x00\x10" .           // 列表长度
            "\x00\x1D" .           // X25519
            "\x00\x04" . "key1" .  // 密钥1
            "\x00\x17" .           // SECP256R1
            "\x00\x04" . "key2";   // 密钥2

        $extension = KeyShareExtension::decode($data);
        $keyShares = $extension->getKeyShares();

        $this->assertCount(2, $keyShares);
        $this->assertEquals(KeyShareExtension::GROUP_X25519, $keyShares[0]['group']);
        $this->assertEquals('key1', $keyShares[0]['key_exchange']);
        $this->assertEquals(KeyShareExtension::GROUP_SECP256R1, $keyShares[1]['group']);
        $this->assertEquals('key2', $keyShares[1]['key_exchange']);
    }

    /**
     * 测试解码 HelloRetryRequest
     */
    public function testDecodeHelloRetryRequest(): void
    {
        $data = "\x00\x1D"; // X25519，只有2字节，表示 HelloRetryRequest
        $extension = KeyShareExtension::decode($data);

        $this->assertTrue($extension->isHelloRetryRequest());
        $this->assertEquals(KeyShareExtension::GROUP_X25519, $extension->getSelectedGroup());
        $this->assertEmpty($extension->getKeyShares());
    }

    /**
     * 测试编码解码往返
     */
    public function testEncodeDecodeRoundTrip(): void
    {
        $originalKeyShares = [
            ['group' => KeyShareExtension::GROUP_X25519, 'key_exchange' => 'test_key_1'],
            ['group' => KeyShareExtension::GROUP_SECP256R1, 'key_exchange' => 'test_key_2']
        ];

        $original = new KeyShareExtension($originalKeyShares);
        $encoded = $original->encode();
        $decoded = KeyShareExtension::decode($encoded);

        $this->assertEquals($originalKeyShares, $decoded->getKeyShares());
        $this->assertEquals($original->isHelloRetryRequest(), $decoded->isHelloRetryRequest());
    }

    /**
     * 测试 HelloRetryRequest 编码解码往返
     */
    public function testHelloRetryRequestEncodeDecodeRoundTrip(): void
    {
        $selectedGroup = KeyShareExtension::GROUP_X25519;
        $original = new KeyShareExtension([], true, $selectedGroup);

        $encoded = $original->encode();
        $decoded = KeyShareExtension::decode($encoded);

        $this->assertTrue($decoded->isHelloRetryRequest());
        $this->assertEquals($selectedGroup, $decoded->getSelectedGroup());
        $this->assertEmpty($decoded->getKeyShares());
    }

    /**
     * 测试版本适用性
     */
    public function testIsApplicableForVersion(): void
    {
        $extension = new KeyShareExtension();

        $this->assertTrue($extension->isApplicableForVersion('1.3'));
        $this->assertTrue($extension->isApplicableForVersion('1.4'));
        $this->assertFalse($extension->isApplicableForVersion('1.2'));
        $this->assertFalse($extension->isApplicableForVersion('1.1'));
    }

    /**
     * 测试组常量
     */
    public function testGroupConstants(): void
    {
        $this->assertEquals(0x0017, KeyShareExtension::GROUP_SECP256R1);
        $this->assertEquals(0x0018, KeyShareExtension::GROUP_SECP384R1);
        $this->assertEquals(0x0019, KeyShareExtension::GROUP_SECP521R1);
        $this->assertEquals(0x001D, KeyShareExtension::GROUP_X25519);
        $this->assertEquals(0x001E, KeyShareExtension::GROUP_X448);
        $this->assertEquals(0x0100, KeyShareExtension::GROUP_FFDHE2048);
        $this->assertEquals(0x0101, KeyShareExtension::GROUP_FFDHE3072);
        $this->assertEquals(0x0102, KeyShareExtension::GROUP_FFDHE4096);
    }
}
