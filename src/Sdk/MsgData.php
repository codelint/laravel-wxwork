<?php namespace Com\Codelint\WxWork\Sdk;

use Illuminate\Support\Arr;

/**
 * MsgData:
 * @date 2022/8/1
 * @time 14:04
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
class MsgData
{
    /**
     * @var string 'news'|'textcard'|'text'
     */
    protected $msg_type;

    protected $bag = [
        'msgtype' => 'text'
    ];

    protected function __construct($type)
    {
        $this->msg_type = match ($type)
        {
            'textcard', 'news' => $type,
            default => 'text',
        };

        $this->bag['msgtype'] = $this->msg_type;
    }

    static function build(string $message, $info): MsgData
    {
        $url = Arr::get($info, 'url', Arr::get($info, 'link', null));

        $image = Arr::get($info, 'image');

        $summary = Arr::get($info, 'summary', '...');

        $btntxt = Arr::get($info, 'btn-txt', 'more');

        if ($url && $image)
        {
            return self::news($message, $url, $summary, $image);
        }

        if ($url)
        {
            return self::textcard($message, $url, $summary, $btntxt);
        }

        return self::text($message);
    }

    static function text($text): MsgData
    {
        $msg = new MsgData('text');
        $msg->msg_type = 'text';
        $msg->bag['text'] = [
            'content' => $text
        ];
        return $msg;
    }

    static function textcard($message, $url, $summary = '', $btntxt = 'more'): MsgData
    {
        $msg = new MsgData('textcard');
        $msg->msg_type = 'textcard';
        $msg->bag['textcard'] = [
            'title' => $message,
            'description' => $summary,
            'url' => $url,
            'btntxt' => $btntxt,
        ];
        return $msg;
    }

    static function news($message, $url, $summary, $pic_url): MsgData
    {
        $msg = new MsgData('news');
        $msg->msg_type = 'news';
        $msg->bag['news'] = [
            'articles' => [
                array(
                    'title' => $message,
                    'description' => $summary,
                    'url' => $url,
                    'picurl' => $pic_url,
                )
            ]
        ];
        return $msg;
    }

    public function __toString(): string
    {
        return json_encode($this->bag);
    }

    public function toArray(): array
    {
        return $this->bag;
    }


}