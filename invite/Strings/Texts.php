<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 8/1/2017
 * Time: 2:15 PM
 */

namespace Longman\TelegramBot;

class Texts {
    public $START_MESSAGE = "سلام!

این ربات مخصوص دریافت جایزه است!!!
برای دریافت جوایز عااااالی ربات کافیه این کارا رو انجام بدی:

1.اول در کانال ما عضو شو
2.بعد لینکی که ربات بهت میده رو پخش کن تا بقیه هم از طریق ربات در کانال عضو بشن
3.به ازای هر 10 نفری که عضو بشن یه کارت شارژ 2 هزار تومنی دریافت میکنی

پس شک نکن و شروع کن:

<a href='http://telegram.me/joinchat/BRw1fj3E1ND9eW5n2zucTQ'>عضـویت در کانال و شروع دریافت جوایز</a>";

    public $GIVE_LINK = 'دریافت لینک';
    public $CALLBACK_DATA = [
        'GIVE_LINK' => 'GIVE_LINK'
    ];
    public $BOT_START_LINK = 'https://telegram.me/';
    public $GET_STATE = [
        'name' => 'آمار'
    ];
    public $FORWARD_THIS = 'این پیام را برای دوستان خود ارسال کنید' . "\n" .
                            ' و به ازای هر 10 نفری که از طریق این لینک وارد ربات و کانال میشوند' . "\n" .
                            'یک شارژ 2 هزار تومانی رایگان دریافت کنید!';


    public static function GetUserLink($chat_id){
        global $telegram;
        $text = 'شارژ 2 هزار تومنی رایگان!!!'. "\n\n" .
                'فقط با دعوت کردن 10 نفر!' . "\n\n" .
                'عضو شو!  دعوت کن!  شارژ رایگان بگیر!';
        return $text . "\n\n" . Texts::$BOT_START_LINK . $telegram->getBotUsername() . '?start=' . $chat_id;
    }
}