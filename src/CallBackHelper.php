<?php


namespace Longman\TelegramBot;


use Longman\TelegramBot\Entities\CallbackQuery;

class CallBackHelper
{

    public const DELIMITER = ';';
    /**
     * @var string
     */
    protected $data;

    /**
     * CallBackHelper constructor.
     * @param  array  $args
     * @throws \Exception
     */
    public function __construct(...$args)
    {
        $this->data = implode(self::DELIMITER, $args);
        $this->isValid($this->data);
    }

    /**
     * @param $str
     * @throws \Exception
     */
    protected function isValid($str): void
    {
        if (mb_strlen($str) > 64){
            throw new \Exception('Callback must be 1-64 bytes');
        }
    }

    /**
     * @param  CallbackQuery  $query
     * @return CallBackHelper
     * @throws \Exception
     */
    public static function fromQuery(CallbackQuery $query): CallBackHelper
    {
        $str = $query->getData();

        $data = explode(self::DELIMITER, $str);

        return new static(...$data);
    }
}
