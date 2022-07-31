<?php namespace Com\Codelint\WxWork\Laravel\Http\Controllers;

use Com\Codelint\WxWork\Sdk\Config;
use Com\Codelint\WxWork\Sdk\Message\WXMessage;
use Com\Codelint\WxWork\Sdk\WXBizMsgCrypt;
use Com\Codelint\WxWork\Sdk\WXCallbackVerify;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * WeCorpController:
 * @date 2022/7/31
 * @time 11:18
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
abstract class WeCorpController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    abstract protected function getConfig(): Config;

    protected function getWeCorpId(): string
    {
        return $this->getConfig()->corpId();
    }
    protected function getToken(): string
    {
        return $this->getConfig()->token();
    }
    protected function getAesKey(): string
    {
        return $this->getConfig()->aes_key();
    }

    abstract protected function onMessage($msg): void;

    public function echoStr()
    {
        // $content = request()->getContent();
        $token = $this->getToken();
        $aseKey = $this->getAesKey();

        $res = (new WXCallbackVerify($token, $aseKey, $this->getWeCorpId()))
            ->verify(request('msg_signature', ''), request('timestamp', ''), request('nonce', ''), request('echostr', ''));
        return response($res, 200);
    }

    public function receive()
    {
        if (request()->has('echostr'))
        {
            return $this->echoStr();
        }

        $content = request()->getContent();
        if (!$content)
        {
            return response('ok');
        }
        /**
         * @var $crypt WXBizMsgCrypt
         */
        $crypt = app('wx-crypt');
        $sig = request('msg_signature', '');
        $nonce = request('nonce', '');
        $tis = request('timestamp', '');
        $message = '';

        $code = $crypt->decryptMsg($sig, $nonce, $tis, $content, $message);
        if ($code)
        {
            return response('ok');
        }

        if ($xml = simplexml_load_string($message, 'SimpleXMLElement', LIBXML_NOCDATA))
        {
            $arr = json_decode(json_encode($xml, JSON_UNESCAPED_UNICODE), true);
            $this->onMessage(new WXMessage($arr));
            return response('ok');
        }

        return response('ok');
    }
}