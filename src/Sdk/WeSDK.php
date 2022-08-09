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

    public function getCorpId(): string
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

    public function multicast($channel_id, $message, $info): bool
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

    /**
     * @desc broadcast message to channel[channel_id]
     * @param $channel_id
     * @param $message
     * @param $info
     * @return bool
     */
    public function broadcast($channel_id, $message, $info): bool
    {
        return $this->multicast($channel_id, $message, $info);
    }

    public function chat($agent_id, $uid, $message, $info): bool
    {
        $token = $this->getToken();

        if ($token && $agent_id)
        {
            $url = 'https://qyapi.weixin.qq.com/cgi-bin/message/send';

            $params = MsgData::build($message, $info)->toArray();
            // print_r($params);
            $params['touser'] = $uid;
            $params['safe'] = 0;
            $params['enable_duplicate_check'] = 1;
            $params['duplicate_check_interval'] = 60;
            $params['agentid'] = $agent_id;

            $ret = $this->wePost($url, $params);
            return ($ret['errmsg'] ?? '') === 'ok';
        }
        return false;
    }

    public function getUsers($department_id = 0)
    {
        $ret = $this->weGet('https://qyapi.weixin.qq.com/cgi-bin/user/list', ['department_id' => $department_id]);

        return $ret['userlist'] ?? [];
    }

    public function getDepartmentUsers($department_id = null)
    {
        $departments = collect($this->departments($department_id));

        $users = $departments->count() > 0 ? $this->getUsers($departments[0]['id']) : [];
        $users = collect($users)->mapWithKeys(function ($v) use ($departments) {
            $v['departments'] = [$departments[0]];
            return array($v['userid'] => $v);
        });

        if ($departments->count() <= 1)
        {
            return $users->values();
        }

        $children = $departments->slice(1);
        foreach ($children as $child)
        {
            $sub_users = $this->getDepartmentUsers($child['id']);
            foreach ($sub_users as $sub_user)
            {
                $sub_user['departments'] = isset($users[$sub_user['userid']]) ? $users[$sub_user['userid']]['departments'] : [];

                $sub_user['departments'][] = $child;
                $users[$sub_user['userid']] = $sub_user;
            }
        }

        return collect($users)->values();
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
        $ret = $this->weGet('https://qyapi.weixin.qq.com/cgi-bin/department/list', !$parent_id ? [] : ['id' => $parent_id]);
        return $ret['department'] ?? [];
    }

    public function department($department_id)
    {
        $ret = $this->weGet('https://qyapi.weixin.qq.com/cgi-bin/department/get', ['id' => $department_id]);

        return $ret['department'] ?? null;
    }

    public function top_department()
    {
        return collect($this->departments())->where('parentid', 0)->first();
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

    /**
     * @param $code
     * @return array list($open_id, $user_id, $device_id)
     */
    public function oauth($code)
    {
        $ret = $this->weGet('https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo', ['code' => $code]);

        if ($did = $ret['DeviceId'] ?? null)
        {
            $uid = $ret['UserId'] ?? null;
            $oid = $uid ? $this->u2o($uid) : ($ret['OpenId'] ?? null);
            return [$oid, $uid, $did];
        }
        return [null, null, null];
    }

}