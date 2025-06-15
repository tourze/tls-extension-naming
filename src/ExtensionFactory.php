<?php

namespace Tourze\TLSExtensionNaming;

use Tourze\TLSExtensionNaming\Extension\ALPNExtension;
use Tourze\TLSExtensionNaming\Extension\ExtensionInterface;
use Tourze\TLSExtensionNaming\Extension\ExtensionType;
use Tourze\TLSExtensionNaming\Extension\KeyShareExtension;
use Tourze\TLSExtensionNaming\Extension\ServerNameExtension;
use Tourze\TLSExtensionNaming\Extension\SignatureAlgorithmsExtension;
use Tourze\TLSExtensionNaming\Extension\SupportedVersionsExtension;

/**
 * TLS 扩展工厂类
 * 
 * 提供创建和解析 TLS 扩展的便捷方法
 */
class ExtensionFactory
{
    /**
     * 扩展类型到类名的映射
     * 
     * @var array<int, class-string<ExtensionInterface>>
     */
    protected static array $extensionMap = [
        ExtensionType::SERVER_NAME->value => ServerNameExtension::class,
        ExtensionType::SUPPORTED_VERSIONS->value => SupportedVersionsExtension::class,
        ExtensionType::ALPN->value => ALPNExtension::class,
        ExtensionType::SIGNATURE_ALGORITHMS->value => SignatureAlgorithmsExtension::class,
        ExtensionType::KEY_SHARE->value => KeyShareExtension::class,
    ];
    
    /**
     * 注册自定义扩展类
     * 
     * @param int $type 扩展类型
     * @param class-string<ExtensionInterface> $className 扩展类名
     */
    public static function registerExtension(int $type, string $className): void
    {
        if (!is_subclass_of($className, ExtensionInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf('Class %s must implement %s', $className, ExtensionInterface::class)
            );
        }
        
        self::$extensionMap[$type] = $className;
    }
    
    /**
     * 根据类型创建扩展实例
     * 
     * @param int $type 扩展类型
     * @param string $data 扩展数据
     * @return ExtensionInterface
     * @throws \RuntimeException 如果扩展类型未注册
     */
    public static function create(int $type, string $data): ExtensionInterface
    {
        if (!isset(self::$extensionMap[$type])) {
            throw new \RuntimeException(sprintf('Unknown extension type: 0x%04X', $type));
        }
        
        $className = self::$extensionMap[$type];
        return $className::decode($data);
    }
    
    /**
     * 创建服务器名称扩展
     * 
     * @param string $serverName 服务器名称
     * @return ServerNameExtension
     */
    public static function createServerName(string $serverName): ServerNameExtension
    {
        return (new ServerNameExtension())->addServerName($serverName);
    }
    
    /**
     * 创建 ALPN 扩展
     * 
     * @param array<string> $protocols 协议列表
     * @return ALPNExtension
     */
    public static function createALPN(array $protocols): ALPNExtension
    {
        return new ALPNExtension($protocols);
    }
    
    /**
     * 创建支持的版本扩展
     * 
     * @param array<int> $versions 版本列表
     * @param bool $isServer 是否为服务器端
     * @return SupportedVersionsExtension
     */
    public static function createSupportedVersions(array $versions, bool $isServer = false): SupportedVersionsExtension
    {
        return new SupportedVersionsExtension($versions, $isServer);
    }
    
    /**
     * 创建签名算法扩展
     * 
     * @param array<int> $algorithms 算法列表
     * @return SignatureAlgorithmsExtension
     */
    public static function createSignatureAlgorithms(array $algorithms): SignatureAlgorithmsExtension
    {
        return new SignatureAlgorithmsExtension($algorithms);
    }
    
    /**
     * 创建密钥共享扩展
     * 
     * @param array<array{group: int, key_exchange: string}> $keyShares 密钥共享列表
     * @return KeyShareExtension
     */
    public static function createKeyShare(array $keyShares): KeyShareExtension
    {
        return new KeyShareExtension($keyShares);
    }
    
    /**
     * 创建 HelloRetryRequest 密钥共享扩展
     * 
     * @param int $selectedGroup 选择的组
     * @return KeyShareExtension
     */
    public static function createHelloRetryRequestKeyShare(int $selectedGroup): KeyShareExtension
    {
        return new KeyShareExtension([], true, $selectedGroup);
    }
    
    /**
     * 获取已注册的扩展类型
     * 
     * @return array<int>
     */
    public static function getRegisteredTypes(): array
    {
        return array_keys(self::$extensionMap);
    }
    
    /**
     * 检查扩展类型是否已注册
     * 
     * @param int $type 扩展类型
     * @return bool
     */
    public static function isTypeRegistered(int $type): bool
    {
        return isset(self::$extensionMap[$type]);
    }
}