<?php

namespace Tourze\TLSExtensionNaming\Extension;

use Tourze\TLSExtensionNaming\Exception\ExtensionEncodingException;

/**
 * 应用层协议协商 (ALPN) 扩展
 *
 * 实现 RFC 7301 中定义的 ALPN 扩展
 */
class ALPNExtension extends AbstractExtension
{
    /**
     * 常见的 ALPN 协议标识符
     */
    public const PROTOCOL_HTTP_1_1 = 'http/1.1';
    public const PROTOCOL_HTTP_2 = 'h2';
    public const PROTOCOL_HTTP_3 = 'h3';
    public const PROTOCOL_SPDY_3_1 = 'spdy/3.1';

    /**
     * @var array<string> 协议列表
     */
    protected array $protocols = [];

    /**
     * 构造函数
     *
     * @param array<string> $protocols 协议列表
     */
    public function __construct(array $protocols = [])
    {
        $this->protocols = $protocols;
    }

    public static function decode(string $data): static
    {
        $offset = 0;
        $protocols = [];

        // 解码协议列表长度
        $listLength = self::decodeUint16($data, $offset);
        $endOffset = $offset + $listLength;

        // 解码协议列表
        while ($offset < $endOffset) {
            // 协议长度
            $protocolLength = ord($data[$offset]);
            $offset++;

            // 协议名称
            $protocol = substr($data, $offset, $protocolLength);
            $offset += $protocolLength;

            $protocols[] = $protocol;
        }

        return new static($protocols);
    }

    /**
     * 添加协议
     *
     * @param string $protocol 协议标识符
     * @return self
     */
    public function addProtocol(string $protocol): self
    {
        if (!in_array($protocol, $this->protocols, true)) {
            $this->protocols[] = $protocol;
        }
        return $this;
    }

    /**
     * 获取协议列表
     *
     * @return array<string>
     */
    public function getProtocols(): array
    {
        return $this->protocols;
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): int
    {
        return ExtensionType::ALPN->value;
    }

    /**
     * {@inheritdoc}
     */
    public function encode(): string
    {
        $protocolList = '';

        foreach ($this->protocols as $protocol) {
            $protocolLength = strlen($protocol);
            if ($protocolLength > 255) {
                throw new ExtensionEncodingException('Protocol name too long');
            }

            // 协议长度 (1 字节) + 协议名称
            $protocolList .= chr($protocolLength) . $protocol;
        }

        // 协议列表长度 (2 字节)
        $listLength = strlen($protocolList);

        return $this->encodeUint16($listLength) . $protocolList;
    }
}
