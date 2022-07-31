<?php namespace Com\Codelint\WxWork\Sdk\Message;

/**
 * Message:
 * @date 2022/6/6
 * @time 22:43
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
abstract class WXAbstractMessage {

    protected array $attributes;

    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }

    public function from()
    {
        return $this->attributes['FromUserName'] ?? null;
    }

    public function to()
    {
        return $this->attributes['ToUserName'] ?? null;
    }

    public function createdAt(): string
    {
        return date('Y-m-d H:i:s', $this->attributes['CreateTime']);
    }

    public function msgType(): ?string
    {
        return $this->attributes['MsgType'] ?? null;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

}
