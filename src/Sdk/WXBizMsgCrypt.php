<?php namespace Com\Codelint\WxWork\Sdk;

/**
 * WXBizMsgCrypt:
 * @date 2022/6/6
 * @time 19:23
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
class WXBizMsgCrypt {

    private string $m_sToken;
    private string $m_sEncodingAesKey;
    private ?string $m_sReceiveId;

    /**
     * 构造函数
     * @param $token string 开发者设置的token
     * @param $encodingAesKey string 开发者设置的EncodingAESKey
     * @param $receiveId null|string, 不同应用场景传不同的id
     */
    public function __construct(string $token, string $encodingAesKey, ?string $receiveId)
    {
        $this->m_sToken = $token;
        $this->m_sEncodingAesKey = $encodingAesKey;
        $this->m_sReceiveId = $receiveId;
    }

    /**
     * @param $msg_signature string
     * @param $timestamp string
     * @param $nonce string
     * @param $encrypt_msg string
     * @return int
     */
    private function verify(string $msg_signature, string $timestamp, string $nonce, string $encrypt_msg): int
    {
        if (strlen($this->m_sEncodingAesKey) != 43)
        {
            return ErrorCode::$IllegalAesKey;
        }

        //verify msg_signature
        $sha1 = new SHA1;
        $array = $sha1->getSHA1($this->m_sToken, $timestamp, $nonce, $encrypt_msg);
        $ret = $array[0];

        if ($ret != 0)
        {
            return $ret;
        }
        else
        {
            $signature = $array[1];
            if ($signature != $msg_signature)
            {
                return ErrorCode::$ValidateSignatureError;
            }
            else
            {
                return ErrorCode::$OK;
            }
        }
    }

    /**
     * @param $sMsgSignature string
     * @param $sTimeStamp string
     * @param $sNonce string
     * @param $sEchoStr string
     * @return array
     */
    private function verifyWithDecrypt(string $sMsgSignature, string $sTimeStamp, string $sNonce, string $sEchoStr): array
    {
        if ($code = $this->verify($sMsgSignature, $sTimeStamp, $sNonce, $sEchoStr))
        {
            return [$code, null];
        }
        $crypt = new PRPCrypt($this->m_sEncodingAesKey);
        return $crypt->decrypt($sEchoStr, $this->m_sReceiveId);
    }

    /*
    *验证URL
    *@param sMsgSignature: 签名串，对应URL参数的msg_signature
    *@param sTimeStamp: 时间戳，对应URL参数的timestamp
    *@param sNonce: 随机串，对应URL参数的nonce
    *@param sEchoStr: 随机串，对应URL参数的echostr
    *@param sReplyEchoStr: 解密之后的echostr，当return返回0时有效
    *@return：成功0，失败返回对应的错误码
    */
    public function verifyURL($sMsgSignature, $sTimeStamp, $sNonce, $sEchoStr, &$sReplyEchoStr)
    {
        list($code, $result) = $this->verifyWithDecrypt($sMsgSignature, $sTimeStamp, $sNonce, $sEchoStr);
        if ($code)
        {
            return $code;
        }

        $sReplyEchoStr = $result;

        return ErrorCode::$OK;
    }

    /**
     * 将公众平台回复用户的消息加密打包.
     * <ol>
     *    <li>对要发送的消息进行AES-CBC加密</li>
     *    <li>生成安全签名</li>
     *    <li>将消息密文和安全签名打包成xml格式</li>
     * </ol>
     *
     * @param $reply_msg string 公众平台待回复用户的消息，xml格式的字符串
     * @param $nonce string 随机串，可以自己生成，也可以用URL参数的nonce
     * @param &$encrypt_msg string 加密后的可以直接回复用户的密文，包括msg_signature, timestamp, nonce, encrypt的xml格式的字符串,
     *                      当return返回0时有效
     *
     * @param string|null $timestamp string 时间戳，可以自己生成，也可以用URL参数的timestamp
     * @return int 成功0，失败返回对应的错误码
     */
    public function encryptMsg(string &$reply_msg, string $nonce, string &$encrypt_msg, string $timestamp = null): int
    {
        $pc = new PRPCrypt($this->m_sEncodingAesKey);

        //加密
        // $array = $pc->encrypt($sReplyMsg, $this->m_sReceiveId);
        list($code, $encrypt) = $pc->encrypt($reply_msg, $this->m_sReceiveId);
        if ($code)
        {
            return $code;
        }

        $timestamp = $timestamp ?: time();

        //生成安全签名
        $sha1 = new SHA1;
        list($code, $signature) = $sha1->getSHA1($this->m_sToken, $timestamp, $nonce, $encrypt);
        if ($code)
        {
            return $code;
        }

        //生成发送的xml
        $parser = new XMLParser;
        $encrypt_msg = $parser->generate($encrypt, $signature, $timestamp, $nonce);

        return ErrorCode::$OK;
    }


    /**
     * 检验消息的真实性，并且获取解密后的明文.
     * <ol>
     *    <li>利用收到的密文生成安全签名，进行签名验证</li>
     *    <li>若验证通过，则提取xml中的加密消息</li>
     *    <li>对消息进行解密</li>
     * </ol>
     *
     * @param $msg_signature string 签名串，对应URL参数的msg_signature
     * @param $timestamp string 时间戳 对应URL参数的timestamp
     * @param $nonce string 随机串，对应URL参数的nonce
     * @param $post_data string 密文，对应POST请求的数据
     * @param &$message string 解密后的原文，当return返回0时有效
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptMsg(string $msg_signature, string $nonce, string $timestamp, string $post_data, string &$message): int
    {
        if (strlen($this->m_sEncodingAesKey) != 43)
        {
            return ErrorCode::$IllegalAesKey;
        }

        $pc = new PRPCrypt($this->m_sEncodingAesKey);

        //提取密文
        $parser = new XMLParser;
        list($code, $encrypt) = $parser->extract($post_data);
        if ($code)
        {
            return $code;
        }

        if ($code = $this->verify($msg_signature, $timestamp, $nonce, $encrypt))
        {
            return $code;
        }

        list($code, $msg) = $pc->decrypt($encrypt, $this->m_sReceiveId);
        if ($code)
        {
            return $code;
        }
        $message = $msg;

        return ErrorCode::$OK;
    }
}
