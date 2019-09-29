<?php


namespace Longman\TelegramBot;


use Longman\TelegramBot\Entities\InlineKeyboard;

class Keys
{

    public const URL = 'url';
    public const CALLBACK_DATA = 'callback_data';
    public const LOGIN_URL = 'login_url';

    protected $keys;

    protected $buttons = [];
    /**
     * Keys constructor.
     * @param  array  $data
     */
    public function __construct($data = [])
    {
        $this->keys = new InlineKeyboard($data);
    }

    public function getKeys(): InlineKeyboard
    {
        return $this->keys;
    }

    public function btn($text, $data, $type = self::CALLBACK_DATA): Keys
    {
        $this->buttons[] = [
            'text' => $text,
            $type => $data
        ];
        return $this;
    }

    public function loginBtn($text, $url, $write_access = true): Keys
    {
        return $this->btn($text, ['url' => $url, 'request_write_access' => $write_access], self::LOGIN_URL);
    }

    public function url($text, $url): Keys
    {
        return $this->btn($text, $url, self::URL);
    }

    public function endRow(): Keys
    {
        $this->keys->addRow(...$this->buttons);
        $this->buttons = [];
        return $this;
    }


}
