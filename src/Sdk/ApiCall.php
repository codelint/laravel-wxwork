<?php namespace Com\Codelint\WxWork\Sdk;

use Illuminate\Support\Str;

/**
 * ApiCall:
 * @date 2022/8/1
 * @time 13:44
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
trait ApiCall
{

    abstract protected function getCorpId(): string;
    abstract protected function getSecret(): string;

    protected function weGet($url, $args = [])
    {
        $args['access_token'] = $this->getToken();
        // $url = $url . (Str::contains($url, '?') ? '&' : '?') . http_build_query($args);
        return $this->apiCall($url, $args, 'get');
    }

    protected function wePost($url, $data = [], $args = [])
    {
        $args['access_token'] = $this->getToken();
        $url = $url . (Str::contains($url, '?') ? '&' : '?') . http_build_query($args);
        return $this->apiCall($url, $data, 'post');
    }

    protected function getToken()
    {
        $ttl = 7200 - 500;
        $md5 = md5($this->corp_id . $this->secret . $ttl);
        $token_res = cache()->remember('codelint/laravel-wxwork:' . $md5, $ttl, function () {
            $corpId = $this->getCorpId();
            $secret = $this->getSecret();
            return $this->apiCall('https://qyapi.weixin.qq.com/cgi-bin/gettoken', array(
                'corpid' => $corpId,
                'corpsecret' => $secret
            ), 'get');
        });

        return $token_res && isset($token_res['access_token']) ? $token_res['access_token'] : null;
    }

    protected function apiCall($url, $args = null, $method = "post", $headers = array(), $withCookie = false, $timeout = 10)
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