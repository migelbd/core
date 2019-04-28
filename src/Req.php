<?php /** @noinspection ALL */


namespace Longman\TelegramBot;


use Longman\TelegramBot\Entities\ChatMember;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Exception\TelegramException;
use MongoDB\Client;

class Req
{
    public const HTML = 'html';
    public const MARKDOWN = 'markdown';
    public const S_DEFAULT = 'default';

    /**
     * @var int
     */
    protected $chat_id;
    /**
     * @var Message
     */
    protected $reply_to_message;
    /**
     * @var InlineKeyboard
     */
    protected $reply_markup;
    /**
     * @var string
     */
    protected $parse_mode;

    protected $db;


    /**
     * Req constructor.
     *
     * @param $chat_id
     * @param Telegram $telegram
     * @param Client $db
     */
    public function __construct($chat_id, $telegram, $db = null)
    {
        $this->chat_id = $chat_id;
        $this->parse_mode = self::HTML;
        $this->db = $db;

        try {
            Request::initialize($telegram);
        } catch ( TelegramException $e ) {
        }
    }


    /**
     * @param Message $msg
     *
     * @return \TgBot\Utils\Req
     */
    public function ReplyTo($msg): self
    {
        if ( $msg instanceof Message ) {
            $this->reply_to_message = $msg->getMessageId();
        }
        else {
            $this->reply_to_message = $msg;
        }

        return $this;
    }


    /**
     * @param string $parse_mode
     *
     * @return $this
     */
    public function Mode($parse_mode = self::HTML): self
    {
        $this->parse_mode = $parse_mode;

        return $this;
    }


    /**
     * @param InlineKeyboard|Keyboard $reply_mark
     *
     * @return $this
     */
    public function Keys($reply_mark): self
    {
        $this->reply_markup = $reply_mark;

        return $this;
    }


    /**
     * @param string $action
     *
     * @return ServerResponse
     */
    public function SendAction($action = ChatAction::TYPING): ServerResponse
    {
        return Request::sendChatAction(['chat_id' => $this->chat_id, 'action' => $action]);
    }


    /**
     * @param $text
     * @param bool $dis_notif
     * @param bool $dis_web
     *
     * @return ServerResponse
     * @throws TelegramException
     *
     */
    public function SendText($text, $dis_notif = false, $dis_web = false): ServerResponse
    {
        $data = ['chat_id' => $this->chat_id, 'text' => $text];
        $data['parse_mode'] = $this->parse_mode;
        if ( $dis_web ) {
            $data['disable_web_page_preview'] = $dis_web;
        }
        if ( $dis_notif ) {
            $data['disable_notification'] = $dis_notif;
        }
        if ( $this->reply_to_message ) {
            $data['reply_to_message_id'] = $this->reply_to_message;
        }
        if ( $this->reply_markup ) {
            $data['reply_markup'] = $this->reply_markup;
        }

        return Request::sendMessage($data);
    }


    public function ForwardMessage($from_chat, $from_msg_id, $dis_notif = false): ServerResponse
    {
        $data = ['chat_id' => $this->chat_id, 'from_chat_id' => $from_chat, 'message_id' => $from_msg_id];
        if ( $dis_notif ) {
            $data['disable_notification'] = $dis_notif;
        }
        return Request::forwardMessage($data);
    }


    public function SendPhoto($photo_path, $text = false, $dis_notif = false, $dis_web = false)
    {
        try {
            $data = ['chat_id' => $this->chat_id];
            [$width, $height] = getimagesize($photo_path);
//            $tmp =
            $data['photo'] = Request::encodeFile($photo_path);
            if ( $text ) {
                $data['caption'] = $text;
            }
            $data['parse_mode'] = $this->parse_mode;
            if ( $dis_web ) {
                $data['disable_web_page_preview'] = $dis_web;
            }
            if ( $dis_notif ) {
                $data['disable_notification'] = $dis_notif;
            }
            if ( $this->reply_to_message ) {
                $data['reply_to_message_id'] = $this->reply_to_message;
            }
            if ( $this->reply_markup ) {
                $data['reply_markup'] = $this->reply_markup;
            }

            return Request::sendPhoto($data);
        } catch ( TelegramException $e ) {
            TelegramLog::error($e);

            return false;
        }
    }


    public function SendPhotoFileId($photo_id, $text = false, $dis_notif = false, $dis_web = false): ServerResponse
    {
        $data = ['chat_id' => $this->chat_id];
        $data['photo'] = $photo_id;
        if ( $text ) {
            $data['caption'] = $text;
        }
        $data['parse_mode'] = $this->parse_mode;
        if ( $dis_web ) {
            $data['disable_web_page_preview'] = $dis_web;
        }
        if ( $dis_notif ) {
            $data['disable_notification'] = $dis_notif;
        }
        if ( $this->reply_to_message ) {
            $data['reply_to_message_id'] = $this->reply_to_message;
        }
        if ( $this->reply_markup ) {
            $data['reply_markup'] = $this->reply_markup;
        }

        return Request::sendPhoto($data);
    }


    public static function Callback($call_id, $text, $show_alert = false): ServerResponse
    {
        $data = [
            'callback_query_id' => $call_id,
            'text'              => $text,
            'show_alert'        => $show_alert,
            'cache_time'        => 5,
        ];

        return Request::answerCallbackQuery($data);
    }


    /**
     * @param $msg_id
     * @param $text
     * @param bool $dis_web
     *
     * @return ServerResponse
     */
    public function EditTextMsg($msg_id, $text, $dis_web = false): ServerResponse
    {
        $data = [
            'chat_id'                  => $this->chat_id,
            'message_id'               => $msg_id,
            'parse_mode'               => $this->parse_mode,
            'text'                     => $text,
            'disable_web_page_preview' => $dis_web,
            'reply_markup'             => $this->reply_markup,
        ];

        return Request::editMessageText($data);
    }


    /**
     * @param $msg_id
     * @param $text
     *
     * @return ServerResponse
     */
    public function EditCaptionMsg($msg_id, $text): ServerResponse
    {
        $data = [
            'chat_id'      => $this->chat_id,
            'message_id'   => $msg_id,
            'parse_mode'   => $this->parse_mode,
            'caption'      => $text,
            'reply_markup' => $this->reply_markup,
        ];

        return Request::editMessageCaption($data);
    }


    public function EditKeys($msg_id): ServerResponse
    {
        $data = [
            'chat_id'      => $this->chat_id,
            'message_id'   => $msg_id,
            'reply_markup' => $this->reply_markup,
        ];

        return Request::editMessageReplyMarkup($data);
    }


    public function Restrict($user_id, $until_date = null, $can_send_messages = null, $can_send_media_messages = null, $can_send_other_messages = null, $can_add_web_page_previews = null): ServerResponse
    {
        $data = [
            'chat_id'    => $this->chat_id,
            'user_id'    => $user_id,
            'until_date' => $until_date,
        ];
        if ( is_bool($can_send_messages) ) {
            $data['can_send_messages'] = $can_send_messages;
        }
        if ( is_bool($can_send_media_messages) ) {
            $data['can_send_media_messages'] = $can_send_media_messages;
        }
        if ( is_bool($can_send_other_messages) ) {
            $data['can_send_other_messages'] = $can_send_other_messages;
        }
        if ( is_bool($can_add_web_page_previews) ) {
            $data['can_add_web_page_previews'] = $can_add_web_page_previews;
        }

        return Request::restrictChatMember($data);
    }


    public function Kick($user_id, $until_date = null): ServerResponse
    {
        $data = [
            'chat_id'    => $this->chat_id,
            'user_id'    => $user_id,
            'until_date' => $until_date,
        ];

        return Request::kickChatMember($data);
    }


    public function Delete($msg, $time = null, $options = ['kick' => false])
    {
        if ( $time === null ) {
            $data = [
                'chat_id' => $this->chat_id,
            ];
            if ( $msg instanceof Message ) {
                $data['message_id'] = $msg->getMessageId();
            }
            else {
                $data['message_id'] = $msg;
            }

            return Request::deleteMessage($data);
        }

        return $this->db->statchat->msg_del->insertOne(
            [
                'chat_id' => $this->chat_id,
                'msg'     => $msg,
                'options' => $options,
                'time'    => strtotime('+' . $time . ' seconds'),
            ]
        );
    }


    public function pinChatMessage($msg, $dis_notif = null): ServerResponse
    {
        $data = [
            'chat_id' => $this->chat_id,
        ];

        if ( $msg instanceof Message ) {
            $data['message_id'] = $msg->getMessageId();
        }
        else {
            $data['message_id'] = $msg;
        }

        if ( $dis_notif !== null ) {
            $data['disable_notification'] = $dis_notif;
        }
        return Request::pinChatMessage($data);
    }


    public function unpinChatMessage(): ServerResponse
    {
        $data = [
            'chat_id' => $this->chat_id,
        ];

        return Request::unpinChatMessage($data);
    }


    public function getFile($file_id): ServerResponse
    {
        return Request::getFile(['file_id' => $file_id]);
    }


    /**
     * @param User|int $user
     *
     * @return bool|ChatMember
     */
    public function getChatMember($user)
    {
        $data = [
            'user_id' => $user instanceof User ? $user->getId() : $user,
            'chat_id' => $this->chat_id,
        ];
        $res = Request::getChatMember($data);

        return $res->isOk() ? $res->getResult() : false;
    }


    public function leaveChat(): ServerResponse
    {
        $data = [
            'chat_id' => $this->chat_id,
        ];

        return Request::leaveChat($data);
    }
}
