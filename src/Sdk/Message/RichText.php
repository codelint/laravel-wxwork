<?php namespace Com\Codelint\WxWork\Sdk\Message;

/**
 * RichText:
 * @date 2022/8/9
 * @time 11:54
 * @author Ray.Zhang <codelint@foxmail.com>
 **/
class RichText
{
    const HIGHLIGHT = 'highlight';
    const NORMAL = 'normal';
    const GRAY = 'gray';

    private string $text;
    private string $color;

    public function __construct($text, $color = self::NORMAL)
    {
        $this->text = $text;
        $this->color = $color;
    }

    public function __toString(): string
    {
        return "<div class=\"{$this->color}\">{$this->text}</div>";
    }


}