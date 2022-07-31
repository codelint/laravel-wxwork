<?php namespace Com\Codelint\WxWork\Sdk;

/**
 * CallbackVerify:
 * @date 2022/6/6
 * @time 19:56
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
class WXCallbackVerify {

    private string $token;
    private string $encodingAesKey;
    private ?string $receiveId;


    public function __construct(string $token, string $aesKey, ?string $receiveId = null)
    {
        $this->token = $token;
        $this->encodingAesKey = $aesKey;
        $this->receiveId = $receiveId;
    }

    /**
     * Get the verified string to response to wx server
     * @return string
     */
    public function verify($msg_signature, $timestamp, $nonce, $echo_str): string
    {
        // 假设企业号在公众平台上设置的参数如下
        $encodingAesKey = $this->encodingAesKey;
        $token = $this->token;
        $rid = $this->receiveId;

        $crypt = new WXBizMsgCrypt($token, $encodingAesKey, $rid);

        $sEchoStr = "";

        $errCode = $crypt->verifyURL($msg_signature, $timestamp, $nonce, $echo_str, $sEchoStr);

        if ($errCode == 0)
        {
            return $sEchoStr . "\n";
        }
        else
        {
            return("ERR: " . $errCode . "\n\n");
        }
    }
}
