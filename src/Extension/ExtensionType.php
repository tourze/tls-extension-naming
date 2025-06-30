<?php

namespace Tourze\TLSExtensionNaming\Extension;

use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * TLS扩展类型枚举
 *
 * 定义了RFC规范中的TLS扩展类型常量
 */
enum ExtensionType: int implements Itemable, Labelable, Selectable
{
    use ItemTrait;
    use SelectTrait;
    /**
     * 服务器名称指示扩展
     */
    case SERVER_NAME = 0x0000;
    
    /**
     * 最大分片长度扩展
     */
    case MAX_FRAGMENT_LENGTH = 0x0001;
    
    /**
     * 客户端证书URL扩展
     */
    case CLIENT_CERTIFICATE_URL = 0x0002;
    
    /**
     * 受信任的CA密钥扩展
     */
    case TRUSTED_CA_KEYS = 0x0003;
    
    /**
     * 截断HMAC扩展
     */
    case TRUNCATED_HMAC = 0x0004;
    
    /**
     * 状态请求扩展
     */
    case STATUS_REQUEST = 0x0005;
    
    /**
     * 安全重协商信息扩展
     * 参考 RFC 5746
     */
    case RENEGOTIATION_INFO = 0x00FF;
    
    /**
     * 支持的组扩展（椭圆曲线组等）
     * 在TLS 1.2中称为"elliptic_curves"
     */
    case SUPPORTED_GROUPS = 0x000A;
    
    /**
     * 椭圆曲线点格式扩展
     */
    case EC_POINT_FORMATS = 0x000B;
    
    /**
     * 签名算法扩展
     */
    case SIGNATURE_ALGORITHMS = 0x000D;
    
    /**
     * 使用SRTP扩展
     */
    case USE_SRTP = 0x000E;
    
    /**
     * 心跳扩展
     */
    case HEARTBEAT = 0x000F;
    
    /**
     * 应用层协议协商扩展
     */
    case ALPN = 0x0010;
    
    /**
     * 签名证书时间戳扩展
     */
    case SIGNED_CERTIFICATE_TIMESTAMP = 0x0012;
    
    /**
     * 客户端证书类型扩展
     */
    case CLIENT_CERTIFICATE_TYPE = 0x0013;
    
    /**
     * 服务器证书类型扩展
     */
    case SERVER_CERTIFICATE_TYPE = 0x0014;
    
    /**
     * 填充扩展
     */
    case PADDING = 0x0015;
    
    /**
     * 先加密后MAC扩展
     */
    case ENCRYPT_THEN_MAC = 0x0016;
    
    /**
     * 扩展主密钥扩展
     */
    case EXTENDED_MASTER_SECRET = 0x0017;
    
    /**
     * 会话票据扩展
     */
    case SESSION_TICKET = 0x0023;
    
    /**
     * 预共享密钥扩展 (TLS 1.3)
     */
    case PRE_SHARED_KEY = 0x0029;
    
    /**
     * 早期数据指示扩展 (TLS 1.3)
     */
    case EARLY_DATA = 0x002A;
    
    /**
     * 支持的版本扩展 (TLS 1.3)
     */
    case SUPPORTED_VERSIONS = 0x002B;
    
    /**
     * Cookie扩展 (TLS 1.3)
     */
    case COOKIE = 0x002C;
    
    /**
     * PSK密钥交换模式扩展 (TLS 1.3)
     */
    case PSK_KEY_EXCHANGE_MODES = 0x002D;
    
    /**
     * 证书授权机构扩展 (TLS 1.3)
     */
    case CERTIFICATE_AUTHORITIES = 0x002F;
    
    /**
     * OID过滤器扩展 (TLS 1.3)
     */
    case OID_FILTERS = 0x0030;
    
    /**
     * 后握手认证扩展 (TLS 1.3)
     */
    case POST_HANDSHAKE_AUTH = 0x0031;
    
    /**
     * 证书签名算法扩展 (TLS 1.3)
     */
    case SIGNATURE_ALGORITHMS_CERT = 0x0032;
    
    /**
     * 密钥共享扩展 (TLS 1.3)
     */
    case KEY_SHARE = 0x0033;
    
    /**
     * 获取扩展类型的标签
     */
    public function getLabel(): string
    {
        return match($this) {
            self::SERVER_NAME => '服务器名称指示',
            self::MAX_FRAGMENT_LENGTH => '最大分片长度',
            self::CLIENT_CERTIFICATE_URL => '客户端证书URL',
            self::TRUSTED_CA_KEYS => '受信任的CA密钥',
            self::TRUNCATED_HMAC => '截断HMAC',
            self::STATUS_REQUEST => '状态请求',
            self::RENEGOTIATION_INFO => '安全重协商信息',
            self::SUPPORTED_GROUPS => '支持的组',
            self::EC_POINT_FORMATS => '椭圆曲线点格式',
            self::SIGNATURE_ALGORITHMS => '签名算法',
            self::USE_SRTP => '使用SRTP',
            self::HEARTBEAT => '心跳',
            self::ALPN => '应用层协议协商',
            self::SIGNED_CERTIFICATE_TIMESTAMP => '签名证书时间戳',
            self::CLIENT_CERTIFICATE_TYPE => '客户端证书类型',
            self::SERVER_CERTIFICATE_TYPE => '服务器证书类型',
            self::PADDING => '填充',
            self::ENCRYPT_THEN_MAC => '先加密后MAC',
            self::EXTENDED_MASTER_SECRET => '扩展主密钥',
            self::SESSION_TICKET => '会话票据',
            self::PRE_SHARED_KEY => '预共享密钥',
            self::EARLY_DATA => '早期数据指示',
            self::SUPPORTED_VERSIONS => '支持的版本',
            self::COOKIE => 'Cookie',
            self::PSK_KEY_EXCHANGE_MODES => 'PSK密钥交换模式',
            self::CERTIFICATE_AUTHORITIES => '证书授权机构',
            self::OID_FILTERS => 'OID过滤器',
            self::POST_HANDSHAKE_AUTH => '后握手认证',
            self::SIGNATURE_ALGORITHMS_CERT => '证书签名算法',
            self::KEY_SHARE => '密钥共享',
        };
    }
} 