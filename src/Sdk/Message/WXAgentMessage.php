<?php namespace Com\Codelint\WxWork\Sdk\Message;

/**
 * WXAgentMessage:
 * @date 2022/6/6
 * @time 22:53
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
abstract class WXAgentMessage extends WXAbstractMessage {

    public function agentID()
    {
        return $this->attributes['AgentID'] ?? null;
    }

}
