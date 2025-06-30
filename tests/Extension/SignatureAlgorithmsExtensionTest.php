<?php

namespace Tourze\TLSExtensionNaming\Tests\Extension;

use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionNaming\Extension\ExtensionType;
use Tourze\TLSExtensionNaming\Extension\SignatureAlgorithmsExtension;

/**
 * SignatureAlgorithmsExtension 测试类
 */
class SignatureAlgorithmsExtensionTest extends TestCase
{
    /**
     * 测试默认构造函数
     */
    public function testDefaultConstructor(): void
    {
        $extension = new SignatureAlgorithmsExtension();
        $this->assertEmpty($extension->getAlgorithms());
    }

    /**
     * 测试带参数的构造函数
     */
    public function testConstructorWithAlgorithms(): void
    {
        $algorithms = [
            SignatureAlgorithmsExtension::RSA_PKCS1_SHA256,
            SignatureAlgorithmsExtension::ECDSA_SECP256R1_SHA256
        ];

        $extension = new SignatureAlgorithmsExtension($algorithms);
        $this->assertEquals($algorithms, $extension->getAlgorithms());
    }

    /**
     * 测试添加算法
     */
    public function testAddAlgorithm(): void
    {
        $extension = new SignatureAlgorithmsExtension();

        $extension->addAlgorithm(SignatureAlgorithmsExtension::RSA_PKCS1_SHA256);
        $this->assertEquals([SignatureAlgorithmsExtension::RSA_PKCS1_SHA256], $extension->getAlgorithms());

        $extension->addAlgorithm(SignatureAlgorithmsExtension::ECDSA_SECP256R1_SHA256);
        $this->assertEquals([
            SignatureAlgorithmsExtension::RSA_PKCS1_SHA256,
            SignatureAlgorithmsExtension::ECDSA_SECP256R1_SHA256
        ], $extension->getAlgorithms());
    }

    /**
     * 测试添加重复算法
     */
    public function testAddDuplicateAlgorithm(): void
    {
        $extension = new SignatureAlgorithmsExtension();

        $extension->addAlgorithm(SignatureAlgorithmsExtension::RSA_PKCS1_SHA256);
        $extension->addAlgorithm(SignatureAlgorithmsExtension::RSA_PKCS1_SHA256);

        // 不应该有重复
        $this->assertEquals([SignatureAlgorithmsExtension::RSA_PKCS1_SHA256], $extension->getAlgorithms());
    }

    /**
     * 测试链式调用
     */
    public function testMethodChaining(): void
    {
        $extension = new SignatureAlgorithmsExtension();

        $result = $extension->addAlgorithm(SignatureAlgorithmsExtension::RSA_PKCS1_SHA256)
            ->addAlgorithm(SignatureAlgorithmsExtension::ECDSA_SECP256R1_SHA256);

        $this->assertSame($extension, $result);
    }

    /**
     * 测试获取扩展类型
     */
    public function testGetType(): void
    {
        $extension = new SignatureAlgorithmsExtension();
        $this->assertEquals(ExtensionType::SIGNATURE_ALGORITHMS->value, $extension->getType());
    }

    /**
     * 测试编码空算法列表
     */
    public function testEncodeEmptyList(): void
    {
        $extension = new SignatureAlgorithmsExtension();
        $encoded = $extension->encode();

        // 空列表应该编码为长度为0的列表
        $this->assertEquals("\x00\x00", $encoded);
    }

    /**
     * 测试编码单个算法
     */
    public function testEncodeSingleAlgorithm(): void
    {
        $extension = new SignatureAlgorithmsExtension();
        $extension->addAlgorithm(SignatureAlgorithmsExtension::RSA_PKCS1_SHA256);

        $encoded = $extension->encode();

        // 验证编码格式
        $expected = "\x00\x02" .     // 列表长度 (2 bytes)
            "\x04\x01";      // RSA_PKCS1_SHA256 = 0x0401

        $this->assertEquals($expected, $encoded);
    }

    /**
     * 测试编码多个算法
     */
    public function testEncodeMultipleAlgorithms(): void
    {
        $extension = new SignatureAlgorithmsExtension();
        $extension->addAlgorithm(SignatureAlgorithmsExtension::RSA_PKCS1_SHA256)
            ->addAlgorithm(SignatureAlgorithmsExtension::ECDSA_SECP256R1_SHA256)
            ->addAlgorithm(SignatureAlgorithmsExtension::RSA_PSS_RSAE_SHA256);

        $encoded = $extension->encode();

        // 验证编码格式
        $expected = "\x00\x06" .     // 列表长度 (6 bytes)
            "\x04\x01" .     // RSA_PKCS1_SHA256 = 0x0401
            "\x04\x03" .     // ECDSA_SECP256R1_SHA256 = 0x0403
            "\x08\x04";      // RSA_PSS_RSAE_SHA256 = 0x0804

        $this->assertEquals($expected, $encoded);
    }

    /**
     * 测试解码空列表
     */
    public function testDecodeEmptyList(): void
    {
        $data = "\x00\x00";
        $extension = SignatureAlgorithmsExtension::decode($data);

        $this->assertEmpty($extension->getAlgorithms());
    }

    /**
     * 测试解码单个算法
     */
    public function testDecodeSingleAlgorithm(): void
    {
        $data = "\x00\x02" .     // 列表长度
            "\x04\x01";      // RSA_PKCS1_SHA256

        $extension = SignatureAlgorithmsExtension::decode($data);

        $this->assertEquals([SignatureAlgorithmsExtension::RSA_PKCS1_SHA256], $extension->getAlgorithms());
    }

    /**
     * 测试解码多个算法
     */
    public function testDecodeMultipleAlgorithms(): void
    {
        $data = "\x00\x06" .     // 列表长度
            "\x04\x01" .     // RSA_PKCS1_SHA256
            "\x04\x03" .     // ECDSA_SECP256R1_SHA256
            "\x08\x04";      // RSA_PSS_RSAE_SHA256

        $extension = SignatureAlgorithmsExtension::decode($data);

        $this->assertEquals([
            SignatureAlgorithmsExtension::RSA_PKCS1_SHA256,
            SignatureAlgorithmsExtension::ECDSA_SECP256R1_SHA256,
            SignatureAlgorithmsExtension::RSA_PSS_RSAE_SHA256
        ], $extension->getAlgorithms());
    }

    /**
     * 测试编码解码往返
     */
    public function testEncodeDecodeRoundTrip(): void
    {
        $originalAlgorithms = [
            SignatureAlgorithmsExtension::RSA_PKCS1_SHA256,
            SignatureAlgorithmsExtension::ECDSA_SECP256R1_SHA256,
            SignatureAlgorithmsExtension::RSA_PSS_RSAE_SHA256,
            SignatureAlgorithmsExtension::ED25519
        ];

        $original = new SignatureAlgorithmsExtension($originalAlgorithms);
        $encoded = $original->encode();
        $decoded = SignatureAlgorithmsExtension::decode($encoded);

        $this->assertEquals($originalAlgorithms, $decoded->getAlgorithms());
    }

    /**
     * 测试算法常量
     */
    public function testAlgorithmConstants(): void
    {
        $this->assertEquals(0x0401, SignatureAlgorithmsExtension::RSA_PKCS1_SHA256);
        $this->assertEquals(0x0501, SignatureAlgorithmsExtension::RSA_PKCS1_SHA384);
        $this->assertEquals(0x0601, SignatureAlgorithmsExtension::RSA_PKCS1_SHA512);
        $this->assertEquals(0x0403, SignatureAlgorithmsExtension::ECDSA_SECP256R1_SHA256);
        $this->assertEquals(0x0503, SignatureAlgorithmsExtension::ECDSA_SECP384R1_SHA384);
        $this->assertEquals(0x0603, SignatureAlgorithmsExtension::ECDSA_SECP521R1_SHA512);
        $this->assertEquals(0x0804, SignatureAlgorithmsExtension::RSA_PSS_RSAE_SHA256);
        $this->assertEquals(0x0805, SignatureAlgorithmsExtension::RSA_PSS_RSAE_SHA384);
        $this->assertEquals(0x0806, SignatureAlgorithmsExtension::RSA_PSS_RSAE_SHA512);
        $this->assertEquals(0x0807, SignatureAlgorithmsExtension::ED25519);
        $this->assertEquals(0x0808, SignatureAlgorithmsExtension::ED448);
    }

    /**
     * 测试各种签名算法组合
     */
    public function testVariousAlgorithmCombinations(): void
    {
        // RSA 系列
        $rsaAlgorithms = [
            SignatureAlgorithmsExtension::RSA_PKCS1_SHA256,
            SignatureAlgorithmsExtension::RSA_PKCS1_SHA384,
            SignatureAlgorithmsExtension::RSA_PKCS1_SHA512,
        ];

        $extension = new SignatureAlgorithmsExtension($rsaAlgorithms);
        $this->assertEquals($rsaAlgorithms, $extension->getAlgorithms());

        // ECDSA 系列
        $ecdsaAlgorithms = [
            SignatureAlgorithmsExtension::ECDSA_SECP256R1_SHA256,
            SignatureAlgorithmsExtension::ECDSA_SECP384R1_SHA384,
            SignatureAlgorithmsExtension::ECDSA_SECP521R1_SHA512,
        ];

        $extension = new SignatureAlgorithmsExtension($ecdsaAlgorithms);
        $this->assertEquals($ecdsaAlgorithms, $extension->getAlgorithms());

        // PSS 系列
        $pssAlgorithms = [
            SignatureAlgorithmsExtension::RSA_PSS_RSAE_SHA256,
            SignatureAlgorithmsExtension::RSA_PSS_RSAE_SHA384,
            SignatureAlgorithmsExtension::RSA_PSS_RSAE_SHA512,
        ];

        $extension = new SignatureAlgorithmsExtension($pssAlgorithms);
        $this->assertEquals($pssAlgorithms, $extension->getAlgorithms());

        // Ed 系列
        $edAlgorithms = [
            SignatureAlgorithmsExtension::ED25519,
            SignatureAlgorithmsExtension::ED448,
        ];

        $extension = new SignatureAlgorithmsExtension($edAlgorithms);
        $this->assertEquals($edAlgorithms, $extension->getAlgorithms());
    }

    /**
     * 测试大量算法的编码解码
     */
    public function testLargeAlgorithmList(): void
    {
        $algorithms = [
            SignatureAlgorithmsExtension::RSA_PKCS1_SHA256,
            SignatureAlgorithmsExtension::RSA_PKCS1_SHA384,
            SignatureAlgorithmsExtension::RSA_PKCS1_SHA512,
            SignatureAlgorithmsExtension::ECDSA_SECP256R1_SHA256,
            SignatureAlgorithmsExtension::ECDSA_SECP384R1_SHA384,
            SignatureAlgorithmsExtension::ECDSA_SECP521R1_SHA512,
            SignatureAlgorithmsExtension::RSA_PSS_RSAE_SHA256,
            SignatureAlgorithmsExtension::RSA_PSS_RSAE_SHA384,
            SignatureAlgorithmsExtension::RSA_PSS_RSAE_SHA512,
            SignatureAlgorithmsExtension::ED25519,
            SignatureAlgorithmsExtension::ED448,
        ];

        $extension = new SignatureAlgorithmsExtension($algorithms);
        $encoded = $extension->encode();
        $decoded = SignatureAlgorithmsExtension::decode($encoded);

        $this->assertEquals($algorithms, $decoded->getAlgorithms());
    }
}
