<?php namespace Com\Codelint\WxWork\Laravel\Providers;

use Com\Codelint\WxWork\Sdk\AppAgent;
use Com\Codelint\WxWork\Sdk\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * WeWorkProvider:
 * @date 2022/7/31
 * @time 11:21
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
abstract class WeWorkProvider extends ServiceProvider
{

    public function ns(): string
    {
        return 'wx-work';
    }

    abstract function config(): Config;

    protected function base_dir($path): string
    {
        return __DIR__ . '/../../../' . $path;
    }

    public function register()
    {
        parent::register();

    }

    public function boot()
    {
        app()->singleton('wx-work.sdk.config', function(){
            return $this->config();
        });

        $this->app->singleton('wx-work.sdk.crypt', function () {
            $token = $this->config()->token();
            $aseKey = $this->config()->aes_key();
            return new \Com\Codelint\WxWork\Sdk\WXBizMsgCrypt($token, $aseKey, $this->config()->corpId());
        });

        $this->app->singleton('wx-work.sdk.agent', function(){
            return new AppAgent(
                $this->config()->corpId(),
                $this->config()->agentId(),
                $this->config()->secret(),
                $this->config()->token(),
                $this->config()->aes_key()
            );
        });
        // $this->mapWebRoutes();
    }

    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace('Com\Codelint\WxWork\Laravel\Http\Controllers')
            ->group($this->base_dir('routes/web.php'));
    }
}