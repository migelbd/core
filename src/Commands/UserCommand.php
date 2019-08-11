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

abstract class UserCommand extends Command
{
    protected $name = false;
    protected $show_in_help = false;
    protected $usage = false;
    protected $delete = false;
    protected $description = '';


    public function __construct(Telegram $telegram, Update $update = null)
    {
        if ( !$this->name ) {
            $this->name = strtolower(str_replace('Command', '', (new \ReflectionClass($this))->getShortName()));
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

        $response = Request::emptyResponse();


        if ( $this->all_chat ) {
            $response = $this->allChat($msg, $chat, $msg->getFrom());
        }
        else {
            if ( $chat->isSuperGroup() ) {
                $response = $this->superChat($msg, $chat, $msg->getFrom());
            }

            if ( $chat->isGroupChat() ) {
                $response = $this->groupChat($msg, $chat, $msg->getFrom());
            }

            if ( $chat->isPrivateChat() ) {
                $response = $this->privateChat($msg, $chat, $msg->getFrom());
            }
        }
        if ( $this->delete ) {
            $this->deleteCmdMessage();
        }
        return $response;
    }


    public function deleteCmdMessage()
    {
        $msg = $this->getMessage();
        return Request::deleteMessage(
            [
                'chat_id'    => $msg->getChat()->getId(),
                'message_id' => $msg->getMessageId(),
            ]
        );
    }

}
