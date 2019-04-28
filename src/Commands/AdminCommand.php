<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands;

use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

abstract class AdminCommand extends Command
{
    /**
     * @var bool
     */
    protected $private_only = true;

    protected $name = false;
    protected $show_in_help = false;
    protected $usage = false;


    public function __construct(Telegram $telegram, Update $update = null)
    {
        if ( !$this->name ) {
            $this->name = strtolower(rtrim(self::class, 'Command'));
            $this->description = self::class;
            $this->usage = '/' . $this->name;
        }
        parent::__construct($telegram, $update);
    }


    public function preExecute()
    {
        if ( $this->isPrivateOnly() && $this->removeNonPrivateMessage() ) {
            $message = $this->getMessage();

            if ( $user = $message->getFrom() ) {
                return Request::sendMessage(
                    [
                        'chat_id'    => $user->getId(),
                        'parse_mode' => 'Markdown',
                        'text'       => sprintf(
                            "/%s комманда доступна только в ЛС бота.\n(`%s`)",
                            $this->getName(),
                            $message->getText()
                        ),
                    ]
                );
            }

            return Request::emptyResponse();
        }

        return $this->execute();
    }
}
