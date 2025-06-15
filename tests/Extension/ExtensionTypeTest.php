<?php

namespace Tourze\TLSExtensionNaming\Tests\Extension;

use PHPUnit\Framework\TestCase;
use Tourze\TLSExtensionNaming\Extension\ExtensionType;

/**
 * 扩展类型枚举测试类
 */
class ExtensionTypeTest extends TestCase
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
} 