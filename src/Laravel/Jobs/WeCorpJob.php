<?php namespace Com\Codelint\WxWork\Laravel\Jobs;

use Com\Codelint\WxWork\Laravel\Facade\Config;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

/**
 * WeCorpJob:
 * @date 2022/7/31
 * @time 11:45
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
class WeCorpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $corp_id;
    protected $agent_id;
    protected $secret;

    public function __construct()
    {
        $this->corp_id = Config::corpId();
        $this->secret = Config::secret();
        $this->agent_id = Config::agentId();
    }

    protected function getMetaData()
    {
        $meta = array();
        $meta['host'] = gethostname();
        $meta['os'] = php_uname();

        if(isset($_SERVER))
        {
            $server_fields = [
                'SERVER_ADDR', 'HTTP_REFERER', 'HTTP_USER_AGENT',
                'REMOTE_ADDR', 'REQUEST_URI', 'HTTP_HOST', 'HTTP_ACCEPT_LANGUAGE',
                'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED_PROTO'
            ];
            foreach($server_fields as $server_field)
            {
                $meta[strtolower($server_field)] = Arr::get($_SERVER, $server_field, '-');
            }
        }
        return $meta;
    }

    protected function getToken()
    {
        $ttl = 7200 - 500;
        $md5 = md5($this->corp_id . $this->secret . $ttl);
        $token_res = cache()->remember('codelint/laravel-wxwork:' . $md5, $ttl, function () {
            $corpId = $this->corp_id;
            $secret = $this->secret;
            return $this->callOnce('https://qyapi.weixin.qq.com/cgi-bin/gettoken', array(
                'corpid' => $corpId,
                'corpsecret' => $secret
            ), 'get');
        });

        return $token_res && isset($token_res['access_token']) ? $token_res['access_token'] : null;
    }

    protected function getMsgData($message, $info = [])
    {
        $url = Arr::get($info, 'url', Arr::get($info, 'link', null));

        $image = Arr::get($info, 'image');

        $summary = Arr::get($info, 'summary', '...');

        $btntxt = Arr::get($info, 'btn-txt', 'more');

        if ($url && $image)
        {
            $data = array(
                'msgtype' => 'news',
                'news' => array(
                    'articles' => [
                        array(
                            'title' => $message,
                            'description' => $summary,
                            'url' => $url,
                            'picurl' => $image,
                        )
                    ]
                ),
            );
        }
        elseif ($url)
        {
            $data = array(
                'msgtype' => 'textcard',
                'textcard' => array(
                    'title' => $message,
                    'description' => $summary,
                    'url' => $url,
                    'btntxt' => $btntxt,
                ),
            );
        }
        else
        {
            $data = array(
                'msgtype' => 'text',
                'text' => array('content' => $message),
            );
        }

        return $data;
    }


    protected function callOnce($url, $args = null, $method = "post", $headers = array(), $withCookie = false, $timeout = 10)
    {
        $ch = curl_init();
        if ($method == "post")
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, is_string($args) ? $args : json_encode($args));
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        else
        {
            $data = $args ? http_build_query($args) : null;
            if ($data)
            {
                if (stripos($url, "?") > 0)
                {
                    $url .= "&$data";
                }
                else
                {
                    $url .= "?$data";
                }
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($headers))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if ($withCookie)
        {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);
        }
        $r = curl_exec($ch);
        curl_close($ch);
        return @json_decode($r, true);
    }
}