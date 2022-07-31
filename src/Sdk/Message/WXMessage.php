<?php namespace Com\Codelint\WxWork\Sdk\Message;

/**
 * WXMessage:
 * @date 2022/6/6
 * @time 22:52
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
class WXMessage extends WXAgentMessage {

    CONST MT_VOICE          = 'voice';
    const MT_TEXT           = 'text';
    const MT_EVENT          = 'event';
    const MT_LOCATION       = 'location';
    const MT_LINK           = 'link';


    const EVENT_ENTER_AGENT = 'enter_agent';
    const EVENT_LOCATION = 'LOCATION';

    public function content(): ?string
    {
        return $this->attributes['Content'] ?? null;
    }

    public function msgId()
    {
        return $this->attributes['MsgId'] ?? null;
    }

    public function event()
    {
        return $this->attributes['Event'] ?? null;
    }

    public function latitude()
    {
        return $this->attributes['Latitude'] ?? null;
    }

    public function longitude()
    {
        return $this->attributes['Longitude'] ?? null;
    }

    public function precision()
    {
        return $this->attributes['Precision'] ?? null;
    }

    public function appType()
    {
        return $this->attributes['AppType'] ?? null;
    }

    public function picUrl(): ?string
    {
        return $this->attributes['PicUrl'] ?? null;
    }

    public function mediaId(): ?string
    {
        return $this->attributes['MediaId'] ?? null;
    }

    public function location(): array
    {
        return [$this->attributes['Location_X'], $this->attributes['Location_Y']];
    }

    public function label(): ?string
    {
        return $this->attributes['Label'] ?? null;
    }

    public function format(): ?string
    {
        return $this->attributes['Format'] ?? null;
    }

}
