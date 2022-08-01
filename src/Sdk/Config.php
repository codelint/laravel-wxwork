<?php namespace Com\Codelint\WxWork\Sdk;

/**
 * Config:
 * @date 2022/7/31
 * @time 14:07
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
class Config
{
    private string $corp_id;
    private string $secret;
    private string $aes_key;
    private string $token;
    private string $agent_id;

    public function __construct($corp_id, $secret, $aes_key, $token, $agent_id) {

        $this->corp_id = $corp_id;
        $this->secret = $secret;
        $this->aes_key = $aes_key;
        $this->token = $token;
        $this->agent_id = $agent_id;
    }

    public function corpId(): string
    {
        return $this->corp_id;
    }

    public function secret(): string
    {
        return $this->secret;
    }

    public function agentId(): string
    {
        return $this->agent_id;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function aes_key(): string
    {
        return $this->aes_key;
    }

    public function config(): Config
    {
        return $this;
    }
}