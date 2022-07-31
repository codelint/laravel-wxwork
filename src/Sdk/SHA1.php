<?php namespace Com\Codelint\WxWork\Sdk;

/**
 * SHA1:
 * @date 2022/6/6
 * @time 19:13
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
class SHA1 {
    /**
     * 用SHA1算法生成安全签名
     * @param string $token 票据
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     * @param string $encrypt_msg 密文消息
     */
    public function getSHA1(string $token, string $timestamp, string $nonce, string $encrypt_msg)
    {
        //排序
        try
        {
            $array = array($encrypt_msg, $token, $timestamp, $nonce);
            sort($array, SORT_STRING);
            $str = implode($array);
            return array(ErrorCode::$OK, sha1($str));
        } catch (\Exception $e)
        {
            print $e . "\n";
            return array(ErrorCode::$ComputeSignatureError, null);
        }
    }
}
