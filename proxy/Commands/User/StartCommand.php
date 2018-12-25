<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/31/2017
 * Time: 11:12 AM
 */

namespace Longman\TelegramBot\Commands\UserCommands;


use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\DB_;
use Longman\TelegramBot\Bot;


class StartCommand extends UserCommand {
    protected $name = 'start';                      // Your command's name
    protected $description = 'Start Bot'; // Your command description
    protected $usage = '/start';                    // Usage of your command
    protected $version = '1.0.0';               // Version of your command

    /**
     * Execute command
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute() {
        global $bot;
        print("UserCommand Commands");

        $message = $this->getMessage();            // Get Message object

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID

        $isChatMember = Request::getChatMember([
            'chat_id' => '@Crazy_lol',
            'user_id' => $chat_id
        ]);
        print_r($isChatMember);
        if(!$isChatMember->getOk() || $isChatMember->getResult()->status === 'left') {
            $text = "سلام به ربات خوش آمدید!";

            $keyboard_buttons = [
                new InlineKeyboardButton([
                    'text' => 'Connect Proxy',
                    'url' => 'https://t.me/IRProxyTel',
                ]),
            ];
            $data = [
                'chat_id' => $chat_id,
                'text' => $text,
                'disable_web_page_preview' => true,
                'reply_markup' => new InlineKeyboard($keyboard_buttons),
                'parse_mode' => 'Markdown',
            ];

            return Request::sendMessage($data);
        }else{
            $text = "سلام، هروقت خواستی با من کار کنی\n\nدکمه (شروع) رو بزن";

            $data = [
                'chat_id' => $chat_id,
                'text' => $text,
                'parse_mode' => 'HTML',
            ];
            $data['reply_markup'] = new Keyboard(['شروع']);
            $data['reply_markup']->resize_keyboard = true;
            Request::sendMessage($data);

            $text = "الان میخوایی چیکار کنی؟";

            $data = [
                'chat_id' => $chat_id,
                'text' => $text,
                'parse_mode' => 'HTML',
            ];

            $keyboard_buttons = [
                new InlineKeyboardButton([
                    'text' => 'رمزگذاری',
                    'callback_data' => 'lock',
                ]),
                new InlineKeyboardButton([
                    'text' => 'رمزگشایی',
                    'callback_data' => 'open',
                ])
            ];

            $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);

            return Request::sendMessage($data);
        }
    }
}