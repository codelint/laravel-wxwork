<?php namespace App\Providers;

use Com\Codelint\WxWork\Laravel\Providers\WeWorkProvider;
use Com\Codelint\WxWork\Sdk\Config;

/**
 * TestProvider:
 * @date 2022/8/1
 * @time 16:49
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
class TestProvider extends WeWorkProvider
{

    function config(): Config
    {
        return new Config(
            env('CORP_ID', ''),
            env('SECRET', ''),
            env('AES_KEY', ''),
            env('TOKEN', ''),
            env('AGENT_ID', ''));
    }
}