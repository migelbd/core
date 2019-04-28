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

use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;

abstract class SystemCommand extends Command
{
    protected $name = false;
    protected $show_in_help = false;
    protected $usage = false;
    protected $need_mysql = false;


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


    public function execute()
    {
        $msg = $this->getMessage();
        $chat = $msg->getChat();

        if ( $chat->isSuperGroup() ) {
            return $this->superChat($msg, $chat, $msg->getFrom());
        }

        if ( $chat->isGroupChat() ) {
            return $this->groupChat($msg, $chat, $msg->getFrom());
        }

        if ( $chat->isPrivateChat() ) {
            return $this->privateChat($msg, $chat, $msg->getFrom());
        }

        return parent::execute();
    }


    /**
     * Super Group
     * @param Message $msg
     * @param Chat $chat
     * @param User $from
     * @return mixed
     * @throws TelegramException
     */
    public function superChat($msg, $chat, $from)
    {
        return Request::emptyResponse();
    }


    /**
     * Group Chat
     * @param Message $msg
     * @param Chat $chat
     * @param User $from
     * @return mixed
     * @throws TelegramException
     */
    public function groupChat($msg, $chat, $from)
    {
        return Request::emptyResponse();
    }


    /** Private Chat
     * @param Message $msg
     * @param Chat $chat
     * @param User $from
     * @return mixed
     * @throws TelegramException
     */
    public function privateChat($msg, $chat, $from)
    {
        return Request::emptyResponse();
    }
}
