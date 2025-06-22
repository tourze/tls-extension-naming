<?php

namespace Tourze\TLSExtensionNaming\Extension;

/**
 * 密钥共享扩展 (TLS 1.3)
 * 
 * 实现 RFC 8446 中定义的 key_share 扩展
 */
class KeyShareExtension extends AbstractExtension
{
    /**
     * 命名组常量 (Named Groups)
     */
    public const GROUP_SECP256R1 = 0x0017;
    public const GROUP_SECP384R1 = 0x0018;
    public const GROUP_SECP521R1 = 0x0019;
    public const GROUP_X25519 = 0x001D;
    public const GROUP_X448 = 0x001E;
    public const GROUP_FFDHE2048 = 0x0100;
    public const GROUP_FFDHE3072 = 0x0101;
    public const GROUP_FFDHE4096 = 0x0102;
    
    /**
     * @var array<array{group: int, key_exchange: string}> 密钥共享条目
     */
    protected array $keyShares = [];
    
    /**
     * @var bool 是否为 HelloRetryRequest 扩展
     */
    protected bool $isHelloRetryRequest = false;
    
    /**
     * @var int|null HelloRetryRequest 中选择的组
     */
    protected ?int $selectedGroup = null;
    
    /**
     * 构造函数
     *
     * @param array<array{group: int, key_exchange: string}> $keyShares 密钥共享条目
     * @param bool $isHelloRetryRequest 是否为 HelloRetryRequest
     * @param int|null $selectedGroup HelloRetryRequest 中选择的组
     */
    public function __construct(
        array $keyShares = [],
        bool $isHelloRetryRequest = false,
        ?int $selectedGroup = null
    ) {
        $this->keyShares = $keyShares;
        $this->isHelloRetryRequest = $isHelloRetryRequest;
        $this->selectedGroup = $selectedGroup;
    }

    public static function decode(string $data): static
    {
        // HelloRetryRequest 格式 (只有2字节)
        if (strlen($data) === 2) {
            $offset = 0;
            $selectedGroup = self::decodeUint16($data, $offset);
            return new static([], true, $selectedGroup);
        }

        // ClientHello/ServerHello 格式
        $offset = 0;
        $keyShares = [];

        // 解码密钥共享列表长度
        $listLength = self::decodeUint16($data, $offset);
        $endOffset = $offset + $listLength;

        // 解码密钥共享列表
        while ($offset < $endOffset) {
            // 组
            $group = self::decodeUint16($data, $offset);

            // 密钥交换数据长度
            $keyExchangeLength = self::decodeUint16($data, $offset);

            // 密钥交换数据
            $keyExchange = substr($data, $offset, $keyExchangeLength);
            $offset += $keyExchangeLength;

            $keyShares[] = ['group' => $group, 'key_exchange' => $keyExchange];
        }

        return new static($keyShares);
    }
    
    /**
     * 添加密钥共享
     *
     * @param int $group 命名组
     * @param string $keyExchange 密钥交换数据
     * @return self
     */
    public function addKeyShare(int $group, string $keyExchange): self
    {
        $this->keyShares[] = ['group' => $group, 'key_exchange' => $keyExchange];
        return $this;
    }
    
    /**
     * 获取密钥共享列表
     *
     * @return array<array{group: int, key_exchange: string}>
     */
    public function getKeyShares(): array
    {
        return $this->keyShares;
    }
    
    /**
     * 是否为 HelloRetryRequest
     *
     * @return bool
     */
    public function isHelloRetryRequest(): bool
    {
        return $this->isHelloRetryRequest;
    }
    
    /**
     * 获取选择的组 (HelloRetryRequest)
     *
     * @return int|null
     */
    public function getSelectedGroup(): ?int
    {
        return $this->selectedGroup;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getType(): int
    {
        return ExtensionType::KEY_SHARE->value;
    }
    
    /**
     * {@inheritdoc}
     */
    public function encode(): string
    {
        // HelloRetryRequest 格式
        if ($this->isHelloRetryRequest) {
            if ($this->selectedGroup === null) {
                throw new \RuntimeException('HelloRetryRequest must have a selected group');
            }
            return $this->encodeUint16($this->selectedGroup);
        }

        // ClientHello/ServerHello 格式
        $keyShareList = '';

        foreach ($this->keyShares as $keyShare) {
            // 组 (2 字节)
            $keyShareList .= $this->encodeUint16($keyShare['group']);

            // 密钥交换数据长度 (2 字节)
            $keyExchangeLength = strlen($keyShare['key_exchange']);
            $keyShareList .= $this->encodeUint16($keyExchangeLength);

            // 密钥交换数据
            $keyShareList .= $keyShare['key_exchange'];
        }

        // 密钥共享列表长度 (2 字节)
        $listLength = strlen($keyShareList);

        return $this->encodeUint16($listLength) . $keyShareList;
    }
    
    /**
     * {@inheritdoc}
     */
    public function isApplicableForVersion(string $tlsVersion): bool
    {
        // 此扩展仅适用于 TLS 1.3 及以上版本
        return version_compare($tlsVersion, '1.3', '>=');
    }
}