<?php

declare(strict_types=1);

namespace Tourze\TLSExtensionNaming\Extension;

/**
 * 服务器名称指示 (SNI) 扩展
 *
 * 实现 RFC 6066 中定义的 server_name 扩展
 */
final class ServerNameExtension extends AbstractExtension
{
    /**
     * 服务器名称类型：主机名
     */
    public const NAME_TYPE_HOST_NAME = 0;

    /**
     * @var array<int, string> 服务器名称列表
     */
    protected array $serverNames = [];

    /**
     * 构造函数
     *
     * @param array<int, string> $serverNames 服务器名称列表
     */
    public function __construct(array $serverNames = [])
    {
        $this->serverNames = $serverNames;
    }

    public static function decode(string $data): static
    {
        $offset = 0;
        $serverNames = [];

        // 解码服务器名称列表长度
        [$listLength, $offset] = self::decodeUint16($data, $offset);
        $endOffset = $offset + $listLength;

        // 解码服务器名称列表
        while ($offset < $endOffset) {
            // 名称类型
            $nameType = ord($data[$offset]);
            ++$offset;

            // 服务器名称长度
            [$nameLength, $offset] = self::decodeUint16($data, $offset);

            // 服务器名称
            $serverName = substr($data, $offset, $nameLength);
            $offset += $nameLength;

            $serverNames[$nameType] = $serverName;
        }

        return new self($serverNames);
    }

    /**
     * 添加服务器名称
     *
     * @param string $serverName 服务器名称
     * @param int    $nameType   名称类型
     */
    public function addServerName(string $serverName, int $nameType = self::NAME_TYPE_HOST_NAME): self
    {
        $this->serverNames[$nameType] = $serverName;

        return $this;
    }

    /**
     * 获取服务器名称列表
     *
     * @return array<int, string>
     */
    public function getServerNames(): array
    {
        return $this->serverNames;
    }

    public function getType(): int
    {
        return ExtensionType::SERVER_NAME->value;
    }

    public function encode(): string
    {
        $serverNameList = '';

        foreach ($this->serverNames as $nameType => $serverName) {
            // 名称类型 (1 字节)
            $serverNameList .= chr($nameType);

            // 服务器名称长度 (2 字节)
            $nameLength = strlen($serverName);
            $serverNameList .= $this->encodeUint16($nameLength);

            // 服务器名称
            $serverNameList .= $serverName;
        }

        // 服务器名称列表长度 (2 字节)
        $listLength = strlen($serverNameList);

        return $this->encodeUint16($listLength) . $serverNameList;
    }
}
