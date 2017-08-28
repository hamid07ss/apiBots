<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 8/1/2017
 * Time: 2:15 PM
 */

namespace Longman\TelegramBot;

class Texts {
    public static $Girl = 'دختر';
    public static $Boy = 'پسر';

    public static $Chat_With_Girl = 'چت با دختر';
    public static $Chat_With_Boy = 'چت با پسر';

    public static $Contact = 'ارتباط با ما';
    public static $Change_My_Gender = 'تغییر جنیست شما در ربات';

    public static $Welcome = "سلام دوستان به ربات دوستیابی خوش آمدید!\n\nلطفا در چت خصوصی با ربات کار کنید.\n\nو برای شروع چت یکی از دکمه های زیر را لمس کنید:";
    public static $You_Are_In_Chat = "شما در حال چت کردن هستید!\nلطفا جهت اتمام چت دکمه (پایان چت ناشناس) را بزنید.\n\nو یا 'پایان چت ناشناس' را ارسال کنید.";
    public static $You_Are_In_List = "ربات در حال پیدا کردن کاربر ناشناس می باشد!!\nلطفا صبور باشید.";
    public static $Choose_Gender = "سلام دوستان به ربات دوستیابی خوش آمدید!\n\nبرای شروع چت ابتدا جنیست خود را انتخاب کنید:";
    public static $Command_Not_Found = "دستور مورد نظر یافت نشد!\nبرای شروع چت یکی از دکمه های زیر را لمس کنید:";
    public static $Chat_Connected = "شما به یک کاربر ناشناس وصل شدید.\nشروع به چت کردن کنید!";
    public static $Finding_User = "ربات در حال یافتن کاربر ناشناس می باشد...\nلطفا چندلحظه صبر کنید!";
    public static $Chat_Ended = "چت ناسناس به پایان رسید!";
    public static $Start_Again = "برای شروع مجدد چت یکی از دکمه های زیر را لمس کنید:";
    public static $Send_Contact_Message = "پیام خود را ارسال کنید.";
    public static $Contact_MSG_Received = "پیام شما دریافت شد.\n\nاز تماس شما متشکریم.";

    public static $ContactUsers = [];






















    public static $JOINED_START_MESSAGE = "شما در کانال ما عضو هستید!
<a href='http://telegram.me/joinchat/BRw1fj3E1ND9eW5n2zucTQ'>کانال ما</a>
    
برای دریافت لینک خود دکمه زیر را لمس کنید:";
    public static $START_MESSAGE = "سلام!

این ربات مخصوص دریافت جایزه است!!!
برای دریافت جوایز عااااالی ربات کافیه این کارا رو انجام بدی:

1.اول در کانال ما عضو شو
2.بعد لینکی که ربات بهت میده رو پخش کن تا بقیه هم از طریق ربات در کانال عضو بشن
3.هرکس تعداد افراد بیشتری را دعوت کند شارژ رایگان 10 هزار تومانی دریافت خواهد کرد.

پس شک نکن و شروع کن:

<a href='http://telegram.me/joinchat/BRw1fj3E1ND9eW5n2zucTQ'>عضـویت در کانال و شروع دریافت جوایز</a>";

    public static $GIVE_LINK = 'دریافت لینک';
    public static $CALLBACK_DATA = [
        'GIVE_LINK' => 'GIVE_LINK',
        'GIVE_CREDIT_IRANCELL' => 'GIVE_CREDIT_IRANCELL',
        'GIVE_CREDIT_MCI' => 'GIVE_CREDIT_MCI'
    ];
    public static $BOT_START_LINK = 'https://telegram.me/';
    public static $GET_STATE = 'آمار';
    public static $FORWARD_THIS = 'این پیام را برای دوستان خود ارسال کنید' . "\n" .
                            ' و به ازای هر 20 نفری که از طریق این لینک وارد ربات میشن' . "\n" .
                            'شارژ هزار تومانی رایگان دریافت میکنید!!';


    public static function GetUserLink($chat_id){
        global $telegram;
        $text = 'شارژ هزار تومنی رایگان به تعداد نامحدود!!!'. "\n\n" .
                'به ازای دعوت هر 20 نفر از طریق لینکی که ربات به شما میدهد!' . "\n\n" .
                'عضو شو!  دعوت کن!  شارژ رایگان بگیر!';
        return $text . "\n\n" . Texts::$BOT_START_LINK . $telegram->getBotUsername() . '?start=' . $chat_id;
    }
}