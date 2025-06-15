<?php

namespace Tourze\TLSExtensionNaming\Tests\Extension;

use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionNaming\Extension\ExtensionType;
use Tourze\TLSExtensionNaming\Extension\SupportedVersionsExtension;

/**
 * SupportedVersionsExtension 测试类
 */
class SupportedVersionsExtensionTest extends TestCase
{
    /**
     * 测试默认构造函数
     */
    public function testDefaultConstructor(): void
    {
        $extension = new SupportedVersionsExtension();
        $this->assertEmpty($extension->getVersions());
        $this->assertFalse($extension->isServerExtension());
    }
    
    /**
     * 测试客户端扩展构造函数
     */
    public function testClientExtensionConstructor(): void
    {
        $versions = [
            SupportedVersionsExtension::TLS_1_3,
            SupportedVersionsExtension::TLS_1_2
        ];
        
        $extension = new SupportedVersionsExtension($versions, false);
        $this->assertEquals($versions, $extension->getVersions());
        $this->assertFalse($extension->isServerExtension());
    }
    
    /**
     * 测试服务器端扩展构造函数
     */
    public function testServerExtensionConstructor(): void
    {
        $versions = [SupportedVersionsExtension::TLS_1_3];
        
        $extension = new SupportedVersionsExtension($versions, true);
        $this->assertEquals($versions, $extension->getVersions());
        $this->assertTrue($extension->isServerExtension());
    }
    
    /**
     * 测试添加版本
     */
    public function testAddVersion(): void
    {
        $extension = new SupportedVersionsExtension();
        
        $extension->addVersion(SupportedVersionsExtension::TLS_1_3);
        $this->assertEquals([SupportedVersionsExtension::TLS_1_3], $extension->getVersions());
        
        $extension->addVersion(SupportedVersionsExtension::TLS_1_2);
        $this->assertEquals([
            SupportedVersionsExtension::TLS_1_3,
            SupportedVersionsExtension::TLS_1_2
        ], $extension->getVersions());
    }
    
    /**
     * 测试添加重复版本
     */
    public function testAddDuplicateVersion(): void
    {
        $extension = new SupportedVersionsExtension();
        
        $extension->addVersion(SupportedVersionsExtension::TLS_1_3);
        $extension->addVersion(SupportedVersionsExtension::TLS_1_3);
        
        // 不应该有重复
        $this->assertEquals([SupportedVersionsExtension::TLS_1_3], $extension->getVersions());
    }
    
    /**
     * 测试链式调用
     */
    public function testMethodChaining(): void
    {
        $extension = new SupportedVersionsExtension();
        
        $result = $extension->addVersion(SupportedVersionsExtension::TLS_1_3)
                          ->addVersion(SupportedVersionsExtension::TLS_1_2);
        
        $this->assertSame($extension, $result);
    }
    
    /**
     * 测试获取扩展类型
     */
    public function testGetType(): void
    {
        $extension = new SupportedVersionsExtension();
        $this->assertEquals(ExtensionType::SUPPORTED_VERSIONS->value, $extension->getType());
    }
    
    /**
     * 测试编码客户端扩展
     */
    public function testEncodeClientExtension(): void
    {
        $extension = new SupportedVersionsExtension([
            SupportedVersionsExtension::TLS_1_3,
            SupportedVersionsExtension::TLS_1_2
        ], false);
        
        $encoded = $extension->encode();
        
        // 验证编码格式
        $expected = "\x04" .      // 列表长度 (4 bytes)
                   "\x03\x04" .   // TLS 1.3
                   "\x03\x03";    // TLS 1.2
        
        $this->assertEquals($expected, $encoded);
    }
    
    /**
     * 测试编码服务器端扩展
     */
    public function testEncodeServerExtension(): void
    {
        $extension = new SupportedVersionsExtension([
            SupportedVersionsExtension::TLS_1_3
        ], true);
        
        $encoded = $extension->encode();
        
        // 服务器端只编码一个版本
        $expected = "\x03\x04"; // TLS 1.3
        
        $this->assertEquals($expected, $encoded);
    }
    
    /**
     * 测试编码空服务器端扩展应该抛出异常
     */
    public function testEncodeEmptyServerExtensionThrowsException(): void
    {
        $extension = new SupportedVersionsExtension([], true);
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Server extension must have exactly one selected version');
        
        $extension->encode();
    }
    
    /**
     * 测试编码过长的版本列表
     */
    public function testEncodeTooLongVersionList(): void
    {
        $extension = new SupportedVersionsExtension();
        
        // 添加128个版本（每个2字节，总共256字节，超过254的限制）
        for ($i = 0; $i < 128; $i++) {
            $extension->addVersion(0x0300 + $i);
        }
        
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Version list too long');
        
        $extension->encode();
    }
    
    /**
     * 测试解码客户端扩展
     */
    public function testDecodeClientExtension(): void
    {
        $data = "\x04" .      // 列表长度
               "\x03\x04" .   // TLS 1.3
               "\x03\x03";    // TLS 1.2
        
        $extension = SupportedVersionsExtension::decode($data);
        
        $this->assertEquals([
            SupportedVersionsExtension::TLS_1_3,
            SupportedVersionsExtension::TLS_1_2
        ], $extension->getVersions());
        $this->assertFalse($extension->isServerExtension());
    }
    
    /**
     * 测试解码服务器端扩展
     */
    public function testDecodeServerExtension(): void
    {
        $data = "\x03\x04"; // TLS 1.3
        
        $extension = SupportedVersionsExtension::decode($data);
        
        $this->assertEquals([SupportedVersionsExtension::TLS_1_3], $extension->getVersions());
        $this->assertTrue($extension->isServerExtension());
    }
    
    /**
     * 测试编码解码往返 - 客户端
     */
    public function testEncodeDecodeRoundTripClient(): void
    {
        $original = new SupportedVersionsExtension([
            SupportedVersionsExtension::TLS_1_3,
            SupportedVersionsExtension::TLS_1_2,
            SupportedVersionsExtension::TLS_1_1,
            SupportedVersionsExtension::TLS_1_0
        ], false);
        
        $encoded = $original->encode();
        $decoded = SupportedVersionsExtension::decode($encoded);
        
        $this->assertEquals($original->getVersions(), $decoded->getVersions());
        $this->assertEquals($original->isServerExtension(), $decoded->isServerExtension());
    }
    
    /**
     * 测试编码解码往返 - 服务器端
     */
    public function testEncodeDecodeRoundTripServer(): void
    {
        $original = new SupportedVersionsExtension([
            SupportedVersionsExtension::TLS_1_3
        ], true);
        
        $encoded = $original->encode();
        $decoded = SupportedVersionsExtension::decode($encoded);
        
        $this->assertEquals($original->getVersions(), $decoded->getVersions());
        $this->assertEquals($original->isServerExtension(), $decoded->isServerExtension());
    }
    
    /**
     * 测试版本适用性
     */
    public function testIsApplicableForVersion(): void
    {
        $extension = new SupportedVersionsExtension();
        
        // 仅适用于 TLS 1.3 及以上版本
        $this->assertFalse($extension->isApplicableForVersion('1.0'));
        $this->assertFalse($extension->isApplicableForVersion('1.1'));
        $this->assertFalse($extension->isApplicableForVersion('1.2'));
        $this->assertTrue($extension->isApplicableForVersion('1.3'));
        $this->assertTrue($extension->isApplicableForVersion('1.4')); // 未来版本
    }
    
    /**
     * 测试版本常量
     */
    public function testVersionConstants(): void
    {
        $this->assertEquals(0x0301, SupportedVersionsExtension::TLS_1_0);
        $this->assertEquals(0x0302, SupportedVersionsExtension::TLS_1_1);
        $this->assertEquals(0x0303, SupportedVersionsExtension::TLS_1_2);
        $this->assertEquals(0x0304, SupportedVersionsExtension::TLS_1_3);
    }
}