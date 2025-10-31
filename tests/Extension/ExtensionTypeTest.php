<?php

namespace Tourze\TLSExtensionNaming\Tests\Extension;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;
use Tourze\TLSExtensionNaming\Extension\ExtensionType;

/**
 * 扩展类型枚举测试类
 *
 * @internal
 */
#[CoversClass(ExtensionType::class)]
final class ExtensionTypeTest extends AbstractEnumTestCase
{
    /**
     * 测试扩展类型常量值是否符合RFC规范
     */
    public function testExtensionTypeValues(): void
    {
        $this->assertEquals(0x0000, ExtensionType::SERVER_NAME->value);
        $this->assertEquals(0x000A, ExtensionType::SUPPORTED_GROUPS->value);
        $this->assertEquals(0x0010, ExtensionType::ALPN->value);
        $this->assertEquals(0x0033, ExtensionType::KEY_SHARE->value);
        $this->assertEquals(0x002A, ExtensionType::EARLY_DATA->value);
        $this->assertEquals(0x0029, ExtensionType::PRE_SHARED_KEY->value);
    }

    /**
     * 测试 toArray 方法
     */
    public function testToArray(): void
    {
        $extensionType = ExtensionType::SERVER_NAME;
        $result = $extensionType->toArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('label', $result);
        $this->assertEquals(0x0000, $result['value']);
        $this->assertEquals('服务器名称指示', $result['label']);

        $extensionType = ExtensionType::ALPN;
        $result = $extensionType->toArray();
        $this->assertEquals(0x0010, $result['value']);
        $this->assertEquals('应用层协议协商', $result['label']);
    }
}
