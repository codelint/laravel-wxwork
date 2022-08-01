<?php namespace Com\Codelint\WxWork\Laravel\Jobs;

use Com\Codelint\WxWork\Laravel\Facade\Config;
use Com\Codelint\WxWork\Sdk\ApiCall;
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
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ApiCall;

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
        return $this->apiCall($url, $args, $method, $headers, $withCookie, $timeout);
    }

    protected function getCorpId(): string
    {
        return $this->corp_id ?? '';
    }

    protected function getSecret(): string
    {
        return $this->secret ?? '';
    }
}