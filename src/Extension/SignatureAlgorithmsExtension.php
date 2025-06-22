<?php

namespace Tourze\TLSExtensionNaming\Extension;

/**
 * 签名算法扩展
 * 
 * 实现 RFC 8446 中定义的 signature_algorithms 扩展
 */
class SignatureAlgorithmsExtension extends AbstractExtension
{
    /**
     * 签名算法常量
     */
    public const RSA_PKCS1_SHA256 = 0x0401;
    public const RSA_PKCS1_SHA384 = 0x0501;
    public const RSA_PKCS1_SHA512 = 0x0601;
    public const ECDSA_SECP256R1_SHA256 = 0x0403;
    public const ECDSA_SECP384R1_SHA384 = 0x0503;
    public const ECDSA_SECP521R1_SHA512 = 0x0603;
    public const RSA_PSS_RSAE_SHA256 = 0x0804;
    public const RSA_PSS_RSAE_SHA384 = 0x0805;
    public const RSA_PSS_RSAE_SHA512 = 0x0806;
    public const ED25519 = 0x0807;
    public const ED448 = 0x0808;
    
    /**
     * @var array<int> 签名算法列表
     */
    protected array $algorithms = [];
    
    /**
     * 构造函数
     *
     * @param array<int> $algorithms 签名算法列表
     */
    public function __construct(array $algorithms = [])
    {
        $this->algorithms = $algorithms;
    }

    public static function decode(string $data): static
    {
        $offset = 0;
        $algorithms = [];

        // 解码算法列表长度
        $listLength = self::decodeUint16($data, $offset);
        $endOffset = $offset + $listLength;

        // 解码算法列表
        while ($offset < $endOffset) {
            $algorithms[] = self::decodeUint16($data, $offset);
        }

        return new static($algorithms);
    }
    
    /**
     * 添加签名算法
     *
     * @param int $algorithm 签名算法
     * @return self
     */
    public function addAlgorithm(int $algorithm): self
    {
        if (!in_array($algorithm, $this->algorithms, true)) {
            $this->algorithms[] = $algorithm;
        }
        return $this;
    }
    
    /**
     * 获取签名算法列表
     *
     * @return array<int>
     */
    public function getAlgorithms(): array
    {
        return $this->algorithms;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getType(): int
    {
        return ExtensionType::SIGNATURE_ALGORITHMS->value;
    }
    
    /**
     * {@inheritdoc}
     */
    public function encode(): string
    {
        $algorithmList = '';

        foreach ($this->algorithms as $algorithm) {
            $algorithmList .= $this->encodeUint16($algorithm);
        }

        // 算法列表长度 (2 字节)
        $listLength = strlen($algorithmList);

        return $this->encodeUint16($listLength) . $algorithmList;
    }
}