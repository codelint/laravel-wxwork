<?php namespace Com\Codelint\WxWork\Laravel\Jobs;

/**
 * SendCorpChatMessageJob:
 * @date 2022/7/31
 * @time 14:27
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
class SendCorpChatMessageJob extends WeCorpJob
{
    protected string $message;
    protected array $detail;
    protected array $meta;

    protected string $uid;

    public function __construct($uid, $message, $info = [])
    {
        parent::__construct();
        $this->uid = $uid;
        $this->message = $message;
        $this->detail = $info;
        $this->meta = $this->getMetaData();
    }

    public function handle()
    {

        $token = $this->getToken();

        if ($token && $this->agent_id)
        {
            $url = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=' . $token;

            $params = $this->getMsgData($this->message, $this->detail);
            // print_r($params);
            $params['touser'] = $this->uid;
            $params['safe'] = 0;
            $params['enable_duplicate_check'] = 1;
            $params['duplicate_check_interval'] = 60;
            $params['agentid'] = $this->agent_id;

            $this->callOnce($url, $params, 'post');
        }

    }
}