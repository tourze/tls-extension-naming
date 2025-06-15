<?php

namespace Tourze\TLSExtensionNaming\Tests;

use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionNaming\Extension\ALPNExtension;
use Tourze\TLSExtensionNaming\Extension\ExtensionType;
use Tourze\TLSExtensionNaming\Extension\KeyShareExtension;
use Tourze\TLSExtensionNaming\Extension\ServerNameExtension;
use Tourze\TLSExtensionNaming\Extension\SignatureAlgorithmsExtension;
use Tourze\TLSExtensionNaming\Extension\SupportedVersionsExtension;
use Tourze\TLSExtensionNaming\ExtensionFactory;

/**
 * ExtensionFactory 测试类
 */
class ExtensionFactoryTest extends TestCase
{
    /**
     * 测试创建服务器名称扩展
     */
    public function testCreateServerName(): void
    {
        $extension = ExtensionFactory::createServerName('example.com');
        
        $this->assertInstanceOf(ServerNameExtension::class, $extension);
        $this->assertEquals(
            [ServerNameExtension::NAME_TYPE_HOST_NAME => 'example.com'],
            $extension->getServerNames()
        );
    }
    
    /**
     * 测试创建 ALPN 扩展
     */
    public function testCreateALPN(): void
    {
        $protocols = ['h2', 'http/1.1'];
        $extension = ExtensionFactory::createALPN($protocols);
        
        $this->assertInstanceOf(ALPNExtension::class, $extension);
        $this->assertEquals($protocols, $extension->getProtocols());
    }
    
    /**
     * 测试创建客户端支持的版本扩展
     */
    public function testCreateClientSupportedVersions(): void
    {
        $versions = [
            SupportedVersionsExtension::TLS_1_3,
            SupportedVersionsExtension::TLS_1_2
        ];
        
        $extension = ExtensionFactory::createSupportedVersions($versions, false);
        
        $this->assertInstanceOf(SupportedVersionsExtension::class, $extension);
        $this->assertEquals($versions, $extension->getVersions());
        $this->assertFalse($extension->isServerExtension());
    }
    
    /**
     * 测试创建服务器端支持的版本扩展
     */
    public function testCreateServerSupportedVersions(): void
    {
        $versions = [SupportedVersionsExtension::TLS_1_3];
        
        $extension = ExtensionFactory::createSupportedVersions($versions, true);
        
        $this->assertInstanceOf(SupportedVersionsExtension::class, $extension);
        $this->assertEquals($versions, $extension->getVersions());
        $this->assertTrue($extension->isServerExtension());
    }
    
    /**
     * 测试创建签名算法扩展
     */
    public function testCreateSignatureAlgorithms(): void
    {
        $algorithms = [
            SignatureAlgorithmsExtension::RSA_PSS_RSAE_SHA256,
            SignatureAlgorithmsExtension::ECDSA_SECP256R1_SHA256
        ];
        
        $extension = ExtensionFactory::createSignatureAlgorithms($algorithms);
        
        $this->assertInstanceOf(SignatureAlgorithmsExtension::class, $extension);
        $this->assertEquals($algorithms, $extension->getAlgorithms());
    }
    
    /**
     * 测试创建密钥共享扩展
     */
    public function testCreateKeyShare(): void
    {
        $keyShares = [
            ['group' => KeyShareExtension::GROUP_X25519, 'key_exchange' => 'key_data_1'],
            ['group' => KeyShareExtension::GROUP_SECP256R1, 'key_exchange' => 'key_data_2']
        ];
        
        $extension = ExtensionFactory::createKeyShare($keyShares);
        
        $this->assertInstanceOf(KeyShareExtension::class, $extension);
        $this->assertEquals($keyShares, $extension->getKeyShares());
        $this->assertFalse($extension->isHelloRetryRequest());
    }
    
    /**
     * 测试创建 HelloRetryRequest 密钥共享扩展
     */
    public function testCreateHelloRetryRequestKeyShare(): void
    {
        $selectedGroup = KeyShareExtension::GROUP_X25519;
        
        $extension = ExtensionFactory::createHelloRetryRequestKeyShare($selectedGroup);
        
        $this->assertInstanceOf(KeyShareExtension::class, $extension);
        $this->assertTrue($extension->isHelloRetryRequest());
        $this->assertEquals($selectedGroup, $extension->getSelectedGroup());
        $this->assertEmpty($extension->getKeyShares());
    }
    
    /**
     * 测试根据类型创建扩展
     */
    public function testCreate(): void
    {
        // 测试创建服务器名称扩展
        $serverNameData = "\x00\x0E\x00\x00\x0Bexample.com";
        $extension = ExtensionFactory::create(ExtensionType::SERVER_NAME->value, $serverNameData);
        
        $this->assertInstanceOf(ServerNameExtension::class, $extension);
        $this->assertEquals(
            [ServerNameExtension::NAME_TYPE_HOST_NAME => 'example.com'],
            $extension->getServerNames()
        );
    }
    
    /**
     * 测试创建未知类型的扩展
     */
    public function testCreateUnknownType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unknown extension type: 0x9999');
        
        ExtensionFactory::create(0x9999, 'data');
    }

    /**
     * 测试注册无效的扩展类
     */
    public function testRegisterInvalidExtension(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must implement');
        
        // 尝试注册一个不实现 ExtensionInterface 的类
        ExtensionFactory::registerExtension(0x9998, \stdClass::class);
    }
    
    /**
     * 测试获取已注册的类型
     */
    public function testGetRegisteredTypes(): void
    {
        $types = ExtensionFactory::getRegisteredTypes();
        
        $this->assertIsArray($types);
        $this->assertContains(ExtensionType::SERVER_NAME->value, $types);
        $this->assertContains(ExtensionType::SUPPORTED_VERSIONS->value, $types);
        $this->assertContains(ExtensionType::ALPN->value, $types);
        $this->assertContains(ExtensionType::SIGNATURE_ALGORITHMS->value, $types);
        $this->assertContains(ExtensionType::KEY_SHARE->value, $types);
    }
    
    /**
     * 测试检查类型是否已注册
     */
    public function testIsTypeRegistered(): void
    {
        $this->assertTrue(ExtensionFactory::isTypeRegistered(ExtensionType::SERVER_NAME->value));
        $this->assertTrue(ExtensionFactory::isTypeRegistered(ExtensionType::ALPN->value));
        $this->assertFalse(ExtensionFactory::isTypeRegistered(0x8888));
    }
}