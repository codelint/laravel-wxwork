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
            'ww84ac99edc8914b81',
            '_nOyJeY6Ex655ck10s0fQ9ScN_rWglZtb7IOZg_JPKs',
            'vtjNFXJ6ONNl5MLdyaToRdDkqEVJYqaCc6jYIx2mZPz',
            '0q76LOd7p45nBHV9bm',
            '1000002');
    }
}