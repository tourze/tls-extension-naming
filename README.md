# TLS Extension Naming Package

This package provides comprehensive TLS extension naming and handling functionality for PHP applications, implementing various TLS extensions according to RFC specifications.

## Features

- Full implementation of common TLS extensions
- Type-safe extension handling with PHP 8.1+ enums
- Easy-to-use factory methods for creating extensions
- Encoding and decoding support for binary TLS data
- Support for both TLS 1.2 and TLS 1.3 extensions

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

## Usage

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
```

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

## Testing

```bash
vendor/bin/phpunit
```

## Requirements

- PHP 8.1 or higher
- Composer

## License

MIT