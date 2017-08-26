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

        $message = $this->getMessage();            // Get Message object

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID

        $isChatMember = Request::getChatMember([
            'chat_id' => '@Crazy_lol',
            'user_id' => $chat_id
        ]);
        print_r($isChatMember);
        if(!$isChatMember->getOk() || $isChatMember->getResult()->status === 'left') {
            $text = "سلام به ربات #سکرت_چت خوش آمدید!


✌️ اگه میخوای یه #حرفی رو به یک نفر بگی و روت نمیشه؟!!

😉 اگه میخوای به یک زبون عجیب و غریب صحبت کنی و دوستات نفهمن قضیه چیه!!؟

😋 اگه میخوای به شخصی پیام بدی ولی دوست داری غیرمستقیم پیام و حرفتو بهش بگی !!؟!



این ربات بهترین گزینس 🍉😃


فقط قبلش باید توی کانال زیر عضو شی👇".
                "\n\n<a href='http://telegram.me/joinchat/BRw1fj3E1ND9eW5n2zucTQ'>عضـویت در کانال</a>";

            $data = [
                'chat_id' => $chat_id,
                'text' => $text,
                'disable_web_page_preview' => true,
                'parse_mode' => 'HTML',
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