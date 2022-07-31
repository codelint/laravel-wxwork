<?php namespace Com\Codelint\WxWork\Sdk;

use DOMDocument;

/**
 * XMLParse:
 * @date 2022/6/6
 * @time 19:15
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
class XMLParser {

    /**
     * 提取出xml数据包中的加密消息
     * @param string $xml_text 待提取的xml字符串
     * @return array 提取出的加密消息字符串
     */
    public function extract(string $xml_text): array
    {
        try
        {
            $xml = new DOMDocument();
            $xml->loadXML($xml_text);
            $array_e = $xml->getElementsByTagName('Encrypt');
            $encrypt = $array_e->item(0)->nodeValue;
            return array(0, $encrypt);
        } catch (\Exception $e)
        {
            print $e . "\n";
            return array(ErrorCode::$ParseXmlError, null);
        }
    }

    /**
     * 生成xml消息
     * @param string $encrypt 加密后的消息密文
     * @param string $signature 安全签名
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     */
    public function generate(string $encrypt, string $signature, string $timestamp, string $nonce): string
    {
        $format = "<xml>
   <Encrypt><![CDATA[%s]]></Encrypt>
   <MsgSignature><![CDATA[%s]]></MsgSignature>
   <TimeStamp>%s</TimeStamp>
   <Nonce><![CDATA[%s]]></Nonce>
   </xml>";
        return sprintf($format, $encrypt, $signature, $timestamp, $nonce);
    }

    public static function xml2array($text)
    {
        if ($xml = simplexml_load_string($text, 'SimpleXMLElement', LIBXML_NOCDATA))
        {
            return json_decode(json_encode($xml, JSON_UNESCAPED_UNICODE), true);
        }
        else
        {
            return null;
        }
    }
}
