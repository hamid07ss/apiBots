<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/31/2017
 * Time: 11:12 AM
 */

namespace Longman\TelegramBot\Commands\AdminCommands;

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\DB_;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboard;


class MessagesCommand extends AdminCommand {
    protected $name = 'messages';                      // Your command's name
    protected $description = 'List Of Contact Us Messages'; // Your command description
    protected $usage = '/messages';                    // Usage of your command
    protected $version = '1.0.0';                  // Version of your command

    /**
     * Execute command
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute() {
        global $bot;

        $message = $this->getMessage();            // Get Message object
        $chat_id = $message->getChat()->getId();   // Get the current Chat ID

        $Messages = DB_::getContact();
        $MessagesNotView = DB_::getContact(null, 'no');

        $text = "تعداد کل پیام ها:
        ". count($Messages) .
        "\n\n" . 
        "تعداد پیام های مشاهده نشده:" . "\n\n" .
            count($MessagesNotView);

        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if(count($Messages) > 0){
            $keyboard_buttons = [
                new InlineKeyboardButton([
                    'text' => 'مشاهده پیام ها',
                    'callback_data' => $bot->VIEW_MESSAGES,
                ])
            ];

            $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
        }

        return Request::sendMessage($data);
    }
}