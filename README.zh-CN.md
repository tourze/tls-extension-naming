# TLS 扩展命名包

本包为 PHP 应用程序提供全面的 TLS 扩展命名和处理功能，根据 RFC 规范实现各种 TLS 扩展。

## 功能特性

- 完整实现常见的 TLS 扩展
- 使用 PHP 8.1+ 枚举进行类型安全的扩展处理
- 提供易用的工厂方法创建扩展
- 支持二进制 TLS 数据的编码和解码
- 同时支持 TLS 1.2 和 TLS 1.3 扩展

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

## 使用方法

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
```

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

## 测试

```bash
vendor/bin/phpunit
```

## 系统要求

- PHP 8.1 或更高版本
- Composer

## 许可证

MIT