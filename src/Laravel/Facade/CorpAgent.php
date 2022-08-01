<?php namespace Com\Codelint\WxWork\Laravel\Facade;

use Com\Codelint\WxWork\Sdk\AppAgent;
use Com\Codelint\WxWork\Sdk\Message\WXMessage;
use Com\Codelint\WxWork\Sdk\WeSDK;
use Illuminate\Support\Facades\Facade;

/**
 * CorpAgent:
 * @date 2022/8/1
 * @time 16:16
 * @author Ray.Zhang <codelint@foxmail.com>
 * @method static AppAgent agent()
 * @method static bool send($uid, $message, $info)
 * @method static null|WXMessage receive($content, $sig, $timestamp, $nonce)
 * @method static WeSDK sdk()
 **/
class CorpAgent extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'wx-work.sdk.agent';
    }

}