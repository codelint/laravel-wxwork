<?php namespace Com\Codelint\WxWork\Laravel\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Config:
 * @date 2022/7/31
 * @time 14:16
 * @author Ray.Zhang <codelint@foxmail.com>
 * @method static string corpId()
 * @method static string secret()
 * @method static string agentId()
 * @method static string token()
 * @method static string aes_key()
 * @method static \Com\Codelint\WxWork\Sdk\Config config()
 **/
class Config extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'wx-work.sdk.config';
    }

}