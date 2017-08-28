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






















    public static $JOINED_START_MESSAGE = "شما در <a href='http://telegram.me/joinchat/BRw1fj3E1ND9eW5n2zucTQ'>کانال ما</a> عضو هستید!
    
برای دریافت لینک خود دکمه زیر را لمس کنید:";
    public static $START_MESSAGE = "سلامی دوبـاره:)

#شارژ_ایرانسل_همراه_اول
#دریافت_جایزه_رایگان


تـیم بزرگ برنامه نویسی Crazy_stars ، با بیش از ۲ سال سابقه و اعتماد در بین خانواده های ایرانی🇮🇷

این بار شارژ رایگان ایرانسل و همراه اول ❣️


مراحل گرفتن شارژ که خیلی راحته :

۱ـ ابتدا در این کانال عضو شوید.
۲ ـ سپس لینکی دریافت میکنید که مخصوص خودتان است، باید آن لینک را برای دوستان خود ارسال کنید و آنها را دعوت کنید تا وارد ربات شوند،
۳ ـ به ازای هر ۳۰ نفری که شما دعوت میکنید یک شارژ (ایرانسل یا همراه اول) دریافت میکنید 



کافیه فقط یک بار امتحان کنی و به جمع بزرگ ما بپیوندی🙂

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