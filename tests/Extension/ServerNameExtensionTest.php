<?php

namespace Tourze\TLSExtensionNaming\Tests\Extension;

use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionNaming\Extension\ExtensionType;
use Tourze\TLSExtensionNaming\Extension\ServerNameExtension;

/**
 * ServerNameExtension 测试类
 */
class ServerNameExtensionTest extends TestCase
{
    /**
     * 测试默认构造函数
     */
    public function testDefaultConstructor(): void
    {
        $extension = new ServerNameExtension();
        $this->assertEmpty($extension->getServerNames());
    }
    
    /**
     * 测试带参数的构造函数
     */
    public function testConstructorWithServerNames(): void
    {
        $serverNames = [
            ServerNameExtension::NAME_TYPE_HOST_NAME => 'example.com'
        ];
        
        $extension = new ServerNameExtension($serverNames);
        $this->assertEquals($serverNames, $extension->getServerNames());
    }
    
    /**
     * 测试添加服务器名称
     */
    public function testAddServerName(): void
    {
        $extension = new ServerNameExtension();
        
        $extension->addServerName('example.com');
        $this->assertEquals(
            [ServerNameExtension::NAME_TYPE_HOST_NAME => 'example.com'],
            $extension->getServerNames()
        );
        
        $extension->addServerName('test.com', 1);
        $this->assertEquals(
            [
                ServerNameExtension::NAME_TYPE_HOST_NAME => 'example.com',
                1 => 'test.com'
            ],
            $extension->getServerNames()
        );
    }
    
    /**
     * 测试链式调用
     */
    public function testMethodChaining(): void
    {
        $extension = new ServerNameExtension();
        
        $result = $extension->addServerName('example.com')
                          ->addServerName('test.com');
        
        $this->assertSame($extension, $result);
    }
    
    /**
     * 测试获取扩展类型
     */
    public function testGetType(): void
    {
        $extension = new ServerNameExtension();
        $this->assertEquals(ExtensionType::SERVER_NAME->value, $extension->getType());
    }
    
    /**
     * 测试编码空服务器名称列表
     */
    public function testEncodeEmptyList(): void
    {
        $extension = new ServerNameExtension();
        $encoded = $extension->encode();
        
        // 空列表应该编码为长度为0的列表
        $this->assertEquals("\x00\x00", $encoded);
    }
    
    /**
     * 测试编码单个服务器名称
     */
    public function testEncodeSingleServerName(): void
    {
        $extension = new ServerNameExtension();
        $extension->addServerName('example.com');
        
        $encoded = $extension->encode();
        
        // 验证编码格式
        $expected = "\x00\x0E" . // 列表长度 (14 bytes)
                   "\x00" .      // 名称类型 (host_name)
                   "\x00\x0B" .  // 名称长度 (11 bytes)
                   "example.com"; // 服务器名称
        
        $this->assertEquals($expected, $encoded);
    }
    
    /**
     * 测试编码多个服务器名称
     */
    public function testEncodeMultipleServerNames(): void
    {
        $extension = new ServerNameExtension();
        $extension->addServerName('example.com')
                  ->addServerName('custom-type', 1);
        
        $encoded = $extension->encode();
        
        // 验证编码格式
        $expected = "\x00\x1C" .    // 列表长度 (28 bytes)
                   "\x00" .         // 名称类型 0
                   "\x00\x0B" .     // 名称长度 (11 bytes)
                   "example.com" .  // 服务器名称
                   "\x01" .         // 名称类型 1
                   "\x00\x0B" .     // 名称长度 (11 bytes)
                   "custom-type";   // 服务器名称
        
        $this->assertEquals($expected, $encoded);
    }
    
    /**
     * 测试解码空列表
     */
    public function testDecodeEmptyList(): void
    {
        $data = "\x00\x00";
        $extension = ServerNameExtension::decode($data);
        
        $this->assertEmpty($extension->getServerNames());
    }
    
    /**
     * 测试解码单个服务器名称
     */
    public function testDecodeSingleServerName(): void
    {
        $data = "\x00\x0E" .    // 列表长度
               "\x00" .         // 名称类型
               "\x00\x0B" .     // 名称长度
               "example.com";   // 服务器名称
        
        $extension = ServerNameExtension::decode($data);
        
        $this->assertEquals(
            [ServerNameExtension::NAME_TYPE_HOST_NAME => 'example.com'],
            $extension->getServerNames()
        );
    }
    
    /**
     * 测试解码多个服务器名称
     */
    public function testDecodeMultipleServerNames(): void
    {
        $data = "\x00\x1C" .    // 列表长度
               "\x00" .         // 名称类型 0
               "\x00\x0B" .     // 名称长度
               "example.com" .  // 服务器名称
               "\x01" .         // 名称类型 1
               "\x00\x0B" .     // 名称长度
               "custom-type";   // 服务器名称
        
        $extension = ServerNameExtension::decode($data);
        
        $this->assertEquals(
            [
                0 => 'example.com',
                1 => 'custom-type'
            ],
            $extension->getServerNames()
        );
    }
    
    /**
     * 测试编码解码往返
     */
    public function testEncodeDecodeRoundTrip(): void
    {
        $original = new ServerNameExtension();
        $original->addServerName('test.example.com')
                 ->addServerName('another.example.com', 1)
                 ->addServerName('third.example.com', 2);
        
        $encoded = $original->encode();
        $decoded = ServerNameExtension::decode($encoded);
        
        $this->assertEquals($original->getServerNames(), $decoded->getServerNames());
    }
    
    /**
     * 测试长域名
     */
    public function testLongDomainName(): void
    {
        $longDomain = str_repeat('a', 63) . '.' . str_repeat('b', 63) . '.com';
        
        $extension = new ServerNameExtension();
        $extension->addServerName($longDomain);
        
        $encoded = $extension->encode();
        $decoded = ServerNameExtension::decode($encoded);
        
        $this->assertEquals(
            [ServerNameExtension::NAME_TYPE_HOST_NAME => $longDomain],
            $decoded->getServerNames()
        );
    }
}