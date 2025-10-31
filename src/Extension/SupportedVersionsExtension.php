<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionNaming\Extension;

use Tourze\TLSExtensionNaming\Exception\ExtensionEncodingException;

/**
 * 支持的版本扩展 (TLS 1.3)
 *
 * 实现 RFC 8446 中定义的 supported_versions 扩展
 */
final class SupportedVersionsExtension extends AbstractExtension
{
    /**
     * TLS 版本常量
     */
    public const TLS_1_0 = 0x0301;
    public const TLS_1_1 = 0x0302;
    public const TLS_1_2 = 0x0303;
    public const TLS_1_3 = 0x0304;

    /**
     * @var array<int> 支持的版本列表
     */
    protected array $versions = [];

    /**
     * @var bool 是否为服务器端扩展
     */
    protected bool $isServerExtension = false;

    /**
     * 构造函数
     *
     * @param array<int> $versions          支持的版本列表
     * @param bool       $isServerExtension 是否为服务器端扩展
     */
    public function __construct(array $versions = [], bool $isServerExtension = false)
    {
        $this->versions = $versions;
        $this->isServerExtension = $isServerExtension;
    }

    public static function decode(string $data): static
    {
        $offset = 0;
        $versions = [];

        // 检查是服务器端还是客户端扩展
        if (2 === strlen($data)) {
            // 服务器端扩展：只有一个版本
            [$version, $offset] = self::decodeUint16($data, $offset);

            return new self([$version], true);
        }

        // 客户端扩展：版本列表
        $listLength = ord($data[$offset]);
        ++$offset;

        $endOffset = $offset + $listLength;
        while ($offset < $endOffset) {
            [$version, $offset] = self::decodeUint16($data, $offset);
            $versions[] = $version;
        }

        return new self($versions, false);
    }

    /**
     * 添加支持的版本
     *
     * @param int $version TLS版本
     */
    public function addVersion(int $version): self
    {
        if (!in_array($version, $this->versions, true)) {
            $this->versions[] = $version;
        }

        return $this;
    }

    /**
     * 获取支持的版本列表
     *
     * @return array<int>
     */
    public function getVersions(): array
    {
        return $this->versions;
    }

    /**
     * 是否为服务器端扩展
     */
    public function isServerExtension(): bool
    {
        return $this->isServerExtension;
    }

    public function getType(): int
    {
        return ExtensionType::SUPPORTED_VERSIONS->value;
    }

    public function encode(): string
    {
        if ($this->isServerExtension) {
            // 服务器端只编码一个选定的版本
            if (0 === count($this->versions)) {
                throw new ExtensionEncodingException('Server extension must have exactly one selected version');
            }

            return $this->encodeUint16($this->versions[0]);
        }

        // 客户端编码版本列表
        $versionList = '';
        foreach ($this->versions as $version) {
            $versionList .= $this->encodeUint16($version);
        }

        // 版本列表长度 (1 字节)
        $listLength = strlen($versionList);
        if ($listLength > 254) {
            throw new ExtensionEncodingException('Version list too long');
        }

        return chr($listLength) . $versionList;
    }

    public function isApplicableForVersion(string $tlsVersion): bool
    {
        // 此扩展仅适用于 TLS 1.3 及以上版本
        return version_compare($tlsVersion, '1.3', '>=');
    }
}
