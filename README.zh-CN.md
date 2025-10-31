# TLS 扩展命名包

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/tls-extension-naming.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-naming)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/tls-extension-naming.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-naming)
[![License](https://img.shields.io/packagist/l/tourze/tls-extension-naming.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-naming)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/tls-extension-naming.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-naming)
[![Coverage Status](https://img.shields.io/codecov/c/gh/tourze/php-monorepo.svg?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

本包为 PHP 应用程序提供全面的 TLS 扩展命名和处理功能，根据 RFC 规范实现各种 TLS 扩展。

## 功能特性

- 完整实现 20+ 种常见的 TLS 扩展
- 使用 PHP 8.2+ 枚举进行类型安全的扩展处理
- 提供易用的工厂方法创建扩展
- 支持二进制 TLS 数据的编码和解码
- 同时支持 TLS 1.2 和 TLS 1.3 扩展
- 全面的测试覆盖，包含 128 个测试用例

## 安装

```bash
composer require tourze/tls-extension-naming
```

## 支持的扩展

- **服务器名称指示 (SNI)** - RFC 6066
- **应用层协议协商 (ALPN)** - RFC 7301
- **支持的版本** - RFC 8446 (TLS 1.3)
- **签名算法** - RFC 8446
- **密钥共享** - RFC 8446 (TLS 1.3)
- **支持的组** - RFC 8446
- **预共享密钥** - RFC 8446 (TLS 1.3)
- **早期数据** - RFC 8446 (TLS 1.3)
- **扩展主密钥** - RFC 7627
- **会话票据** - RFC 5077
- 还有更多...

## 快速开始

### 使用扩展工厂

```php
use Tourze\TLSExtensionNaming\ExtensionFactory;

// 创建服务器名称扩展
$sniExtension = ExtensionFactory::createServerName('example.com');

// 创建 ALPN 扩展
$alpnExtension = ExtensionFactory::createALPN(['h2', 'http/1.1']);

// 创建支持的版本扩展
$versionsExtension = ExtensionFactory::createSupportedVersions([
    0x0304, // TLS 1.3
    0x0303  // TLS 1.2
]);

// 创建签名算法扩展
$sigAlgsExtension = ExtensionFactory::createSignatureAlgorithms([
    0x0804, // rsa_pss_rsae_sha256
    0x0401  // rsa_pkcs1_sha256
]);

// 创建密钥共享扩展
$keyShareExtension = ExtensionFactory::createKeyShare([
    ['group' => 0x001d, 'key_exchange' => 'public_key_data_here']
]);
```

### 直接使用扩展

```php
use Tourze\TLSExtensionNaming\Extension\ServerNameExtension;
use Tourze\TLSExtensionNaming\Extension\ALPNExtension;

// 服务器名称扩展
$sni = new ServerNameExtension();
$sni->addServerName('example.com')
    ->addServerName('www.example.com');

// 编码为二进制
$encoded = $sni->encode();

// 从二进制解码
$decoded = ServerNameExtension::decode($encoded);

// ALPN 扩展
$alpn = new ALPNExtension();
$alpn->addProtocol(ALPNExtension::PROTOCOL_HTTP_2)
     ->addProtocol(ALPNExtension::PROTOCOL_HTTP_1_1);
```

### 扩展类型枚举

```php
use Tourze\TLSExtensionNaming\Extension\ExtensionType;

// 访问扩展类型值
$sniType = ExtensionType::SERVER_NAME->value; // 0x0000
$alpnType = ExtensionType::ALPN->value;       // 0x0010
$keyShareType = ExtensionType::KEY_SHARE->value; // 0x0033

// 获取扩展标签
echo ExtensionType::SERVER_NAME->getLabel(); // "服务器名称指示"

// 获取所有已注册的扩展类型
$registeredTypes = ExtensionFactory::getRegisteredTypes();
```

## 高级用法

### 自定义扩展

您可以注册自定义扩展类型：

```php
use Tourze\TLSExtensionNaming\ExtensionFactory;
use Tourze\TLSExtensionNaming\Extension\AbstractExtension;

class MyCustomExtension extends AbstractExtension {
    public function getType(): int {
        return 0x9999;
    }
    
    public function encode(): string {
        // 实现
    }
    
    public static function decode(string $data): static {
        // 实现
    }
}

// 注册自定义扩展
ExtensionFactory::registerExtension(0x9999, MyCustomExtension::class);
```

### 错误处理

```php
use Tourze\TLSExtensionNaming\Exception\UnknownExtensionTypeException;
use Tourze\TLSExtensionNaming\Exception\ExtensionEncodingException;

try {
    $extension = ExtensionFactory::create(0x9999, $data);
} catch (UnknownExtensionTypeException $e) {
    echo "未知的扩展类型: " . $e->getMessage();
} catch (ExtensionEncodingException $e) {
    echo "编码错误: " . $e->getMessage();
}
```

## 系统要求

- PHP 8.2 或更高版本
- Composer

## 测试

```bash
vendor/bin/phpunit
```

## 贡献

请参阅 [CONTRIBUTING.md](CONTRIBUTING.md) 了解详情。

## 许可证

MIT 许可证。请查看 [LICENSE](LICENSE) 文件获取更多信息。