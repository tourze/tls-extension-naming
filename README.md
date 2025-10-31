# TLS Extension Naming Package

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/tls-extension-naming.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-naming)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/tls-extension-naming.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-naming)
[![License](https://img.shields.io/packagist/l/tourze/tls-extension-naming.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-naming)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/tls-extension-naming.svg?style=flat-square)](https://packagist.org/packages/tourze/tls-extension-naming)
[![Coverage Status](https://img.shields.io/codecov/c/gh/tourze/php-monorepo.svg?style=flat-square)](https://codecov.io/gh/tourze/php-monorepo)

This package provides comprehensive TLS extension naming and handling functionality for PHP applications, implementing various TLS extensions according to RFC specifications.

## Features

- Full implementation of 20+ common TLS extensions
- Type-safe extension handling with PHP 8.2+ enums
- Easy-to-use factory methods for creating extensions
- Encoding and decoding support for binary TLS data
- Support for both TLS 1.2 and TLS 1.3 extensions
- Comprehensive test coverage with 128 test cases

## Installation

```bash
composer require tourze/tls-extension-naming
```

## Supported Extensions

- **Server Name Indication (SNI)** - RFC 6066
- **Application-Layer Protocol Negotiation (ALPN)** - RFC 7301
- **Supported Versions** - RFC 8446 (TLS 1.3)
- **Signature Algorithms** - RFC 8446
- **Key Share** - RFC 8446 (TLS 1.3)
- **Supported Groups** - RFC 8446
- **Pre-Shared Key** - RFC 8446 (TLS 1.3)
- **Early Data** - RFC 8446 (TLS 1.3)
- **Extended Master Secret** - RFC 7627
- **Session Ticket** - RFC 5077
- And many more...

## Quick Start

### Using the Extension Factory

```php
use Tourze\TLSExtensionNaming\ExtensionFactory;

// Create a Server Name extension
$sniExtension = ExtensionFactory::createServerName('example.com');

// Create an ALPN extension
$alpnExtension = ExtensionFactory::createALPN(['h2', 'http/1.1']);

// Create a Supported Versions extension
$versionsExtension = ExtensionFactory::createSupportedVersions([
    0x0304, // TLS 1.3
    0x0303  // TLS 1.2
]);

// Create a Signature Algorithms extension
$sigAlgsExtension = ExtensionFactory::createSignatureAlgorithms([
    0x0804, // rsa_pss_rsae_sha256
    0x0401  // rsa_pkcs1_sha256
]);

// Create a Key Share extension
$keyShareExtension = ExtensionFactory::createKeyShare([
    ['group' => 0x001d, 'key_exchange' => 'public_key_data_here']
]);
```

### Working with Extensions Directly

```php
use Tourze\TLSExtensionNaming\Extension\ServerNameExtension;
use Tourze\TLSExtensionNaming\Extension\ALPNExtension;

// Server Name Extension
$sni = new ServerNameExtension();
$sni->addServerName('example.com')
    ->addServerName('www.example.com');

// Encode to binary
$encoded = $sni->encode();

// Decode from binary
$decoded = ServerNameExtension::decode($encoded);

// ALPN Extension
$alpn = new ALPNExtension();
$alpn->addProtocol(ALPNExtension::PROTOCOL_HTTP_2)
     ->addProtocol(ALPNExtension::PROTOCOL_HTTP_1_1);
```

### Extension Types Enum

```php
use Tourze\TLSExtensionNaming\Extension\ExtensionType;

// Access extension type values
$sniType = ExtensionType::SERVER_NAME->value; // 0x0000
$alpnType = ExtensionType::ALPN->value;       // 0x0010
$keyShareType = ExtensionType::KEY_SHARE->value; // 0x0033

// Get extension label
echo ExtensionType::SERVER_NAME->getLabel(); // "服务器名称指示"

// Get all registered extension types
$registeredTypes = ExtensionFactory::getRegisteredTypes();
```

## Advanced Usage

### Custom Extensions

You can register custom extension types:

```php
use Tourze\TLSExtensionNaming\ExtensionFactory;
use Tourze\TLSExtensionNaming\Extension\AbstractExtension;

class MyCustomExtension extends AbstractExtension {
    public function getType(): int {
        return 0x9999;
    }
    
    public function encode(): string {
        // Implementation
    }
    
    public static function decode(string $data): static {
        // Implementation
    }
}

// Register the custom extension
ExtensionFactory::registerExtension(0x9999, MyCustomExtension::class);
```

### Error Handling

```php
use Tourze\TLSExtensionNaming\Exception\UnknownExtensionTypeException;
use Tourze\TLSExtensionNaming\Exception\ExtensionEncodingException;

try {
    $extension = ExtensionFactory::create(0x9999, $data);
} catch (UnknownExtensionTypeException $e) {
    echo "Unknown extension type: " . $e->getMessage();
} catch (ExtensionEncodingException $e) {
    echo "Encoding error: " . $e->getMessage();
}
```

## Requirements

- PHP 8.2 or higher
- Composer

## Testing

```bash
vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.