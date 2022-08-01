<?php namespace Com\Codelint\WxWork\Sdk;

use Illuminate\Support\Arr;

/**
 * WeSDK:
 * @date 2022/8/1
 * @time 11:22
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
class WeSDK
{
    use ApiCall;

    protected string $corp_id;
    protected string $secret;

    protected function getCorpId(): string
    {
        return $this->corp_id;
    }

    protected function getSecret(): string
    {
        return $this->secret;
    }

    public function __construct($corp_id, $secret)
    {
        $this->corp_id = $corp_id;
        $this->secret = $secret;
    }

    /**
     * @desc broadcast message to channel[channel_id]
     * @param $channel_id
     * @param $message
     * @param $info
     * @return bool
     */
    public function broadcast($channel_id, $message, $info): bool
    {
        $params = MsgData::build($message, $info);
        // print_r($params);
        $params['chatid'] = $channel_id;
        $params['safe'] = 0;

        $res = $this->wePost('https://qyapi.weixin.qq.com/cgi-bin/appchat/send', $params);
        // {"errcode":0,"errmsg":"ok"}
        if ($res && $res['errmsg'] == 'ok')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public function chat($agent_id, $uid, $message, $info): bool
    {
        $token = $this->getToken();

        if ($token && $agent_id)
        {
            $url = 'https://qyapi.weixin.qq.com/cgi-bin/message/send';

            $params = MsgData::build($message, $info);
            // print_r($params);
            $params['touser'] = $uid;
            $params['safe'] = 0;
            $params['enable_duplicate_check'] = 1;
            $params['duplicate_check_interval'] = 60;
            $params['agentid'] = $agent_id;

            $this->wePost($url, $params, 'post');
        }
    }

    public function getUsers($department_id = 0)
    {
        $ret = $this->weGet('https://qyapi.weixin.qq.com/cgi-bin/user/list', ['department_id' => $department_id]);

        return $ret['userlist'] ?? [];
    }

    public function getUserInfo($uid)
    {
        $ret = $this->weGet('https://qyapi.weixin.qq.com/cgi-bin/user/get', ['userid' => $uid]);

        if ($ret && ($ret['errmsg'] ?? null) === 'ok')
        {
            return Arr::except($ret, ['errcode', 'errmsg']);
        }

        return null;
    }

    public function departments($parent_id = null)
    {
        $ret = $this->weGet('https://qyapi.weixin.qq.com/cgi-bin/department/list', $parent_id ? [] : ['id' => $parent_id]);
        return $ret['department'] ?? [];
    }

    /**
     * user id to open id
     * @param $uid
     * @return mixed|null
     */
    public function u2o($uid)
    {
        $ret = $this->wePost('https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_openid', ['userid' => $uid]);
        return $ret['openid'] ?? null;
    }

    /**
     * open id to user id
     * @param $oid
     * @return mixed|null
     */
    public function o2u($oid)
    {
        $ret = $this->wePost('https://qyapi.weixin.qq.com/cgi-bin/user/convert_to_userid', ['openid' => $oid]);
        return $ret['userid'] ?? null;
    }

    public function m2u($mobile)
    {
        $ret = $this->wePost('https://qyapi.weixin.qq.com/cgi-bin/user/getuserid', ['mobile' => $mobile]);
        return $ret['userid'] ?? null;
    }

    public function e2u($email)
    {
        $ret = $this->wePost('https://qyapi.weixin.qq.com/cgi-bin/user/get_userid_by_email', [
            'email' => $email,
            'email_type' => 1
        ]);
        return $ret['userid'] ?? null;
    }

    public function getChannel($channel_id)
    {
        $res = $this->weGet('https://qyapi.weixin.qq.com/cgi-bin/appchat/get', array(
            'chatid' => $channel_id
        ));

        if ($res && $res['errmsg'] == 'ok')
        {
            return $res['chat_info'] ?? null;
        }
        return null;
    }

    public function genChannel($channel_name, $user_ids, $channel_id = null)
    {
        $channel_id = $channel_id ?: $channel_name;
        $owner_id = $user_ids[0];

        if($this->getChannel($channel_id))
        {
            return true;
        }

        $res = $this->wePost('https://qyapi.weixin.qq.com/cgi-bin/appchat/create', array(
            'name' => $channel_name,
            'owner' => $owner_id,
            'userlist' => $user_ids,
            'chatid' => $channel_id,
        ));

//        if (!($res && $res['errmsg'] == 'ok'))
//        {
//            Log::error(json_encode($res));
//        }

        return $res && $res['errmsg'] == 'ok';
    }
}