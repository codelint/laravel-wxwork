<?php namespace Com\Codelint\WxWork\Sdk;

use Com\Codelint\WxWork\Sdk\Message\WXMessage;

/**
 * AppAgent:
 * @date 2022/8/1
 * @time 13:59
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
class AppAgent
{
    private string $corp_id;
    private string $agent_id;
    private string $token;
    private string $secret;
    private string $aes_key;
    private WeSDK $sdk;
    private WXBizMsgCrypt $crypt;

    public function __construct($corp_id, $agent_id, $secret, $token, $aes_key)
    {
        $this->corp_id = $corp_id;
        $this->agent_id = $agent_id;
        $this->secret = $secret;
        $this->token = $token;
        $this->aes_key = $aes_key;
        $this->sdk = new WeSDK($this->corp_id, $this->secret);
        $this->crypt = new WXBizMsgCrypt($this->token, $this->aes_key, $this->corp_id);
    }

    public function send($uid, $message, $info): bool
    {
        return $this->sdk->chat($this->agent_id, $uid, $message, $info);
    }

    public function genChannel($channel_name, $user_ids): bool
    {
        return $this->sdk->genChannel($channel_name, $user_ids, "{$this->agent_id}:{$channel_name}");
    }

    public function getChannel($channel_name): array
    {
        return $this->sdk->getChannel("{$this->agent_id}:{$channel_name}");
    }

    public function receive($content, $sig, $timestamp, $nonce): ?WXMessage
    {
        $message = '';
        $code = $this->crypt->decryptMsg($sig, $nonce, $timestamp, $content, $message);
        if ($code)
        {
            return null;
        }

        if ($xml = simplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA))
        {
            $arr = json_decode(json_encode($xml, JSON_UNESCAPED_UNICODE), true);
            return new WXMessage($arr);
        }

        return null;
    }

    public function agent(): static
    {
        return $this;
    }

    public function sdk(): WeSDK
    {
        return $this->sdk;
    }


}