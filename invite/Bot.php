<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/31/2017
 * Time: 10:40 AM
 */

namespace Longman\TelegramBot;

use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\DB_;
use function PHPSTORM_META\type;

class Bot {
    public static $minScore = 20;

    public function __construct() {
        global $telegram;
        $this->telegram = $telegram;
    }

    public function handleMessages(Update $result) {
        $type = $result->getMessage();
        if($type && $type->getType() === 'command') {
            return false;
        }

        if(!$result->getCallbackQuery()) {
            if($this->telegram->isAdmin()) {
                $this->AdminsMessages($result);
            }
            else {
                $this->UsersMessages($result);
            }
        }
        else if($result->getCallbackQuery()) {
            $this->handleCallBack($result);
        }
    }

    public function reloadBot() {
        $allUsers = DB::selectChats([
            'groups' => false,
            'supergroups' => false,
            'channels' => false,
            'users' => true,
        ]);
        $allGroups = DB::selectChats([
            'groups' => true,
            'supergroups' => false,
            'channels' => false,
            'users' => false,
        ]);
        $allSupergroups = DB::selectChats([
            'groups' => false,
            'supergroups' => true,
            'channels' => false,
            'users' => false,
        ]);

        foreach($allUsers as $User) {
            $res = Request::getChat([
                'chat_id' => $User["chat_id"]
            ]);
            $isOk = $res->getOk();
            if(!$isOk && $res->getErrorCode() === 400) {
                DB_::removeChat($User["chat_id"]);
            }
        }
        foreach($allGroups as $Group) {
            $res = Request::getChat([
                'chat_id' => $Group["chat_id"]
            ]);
            $isOk = $res->getOk();
            if(!$isOk && $res->getErrorCode() === 400) {
                DB_::removeChat($Group["chat_id"]);
            }
        }
        foreach($allSupergroups as $Supergroup) {
            $res = Request::getChat([
                'chat_id' => $Supergroup["chat_id"]
            ]);
            $isOk = $res->getOk();
            if(!$isOk && $res->getErrorCode() === 400) {
                DB_::removeChat($Supergroup["chat_id"]);
            }
        }
    }

    public function getCredit($type){
        $credit = false;
        $cre_type = '';
        switch($type){
            case Texts::$CALLBACK_DATA["GIVE_CREDIT_IRANCELL"]:
                $cre_type = 'irancell';
                $credit = DB_::getCredit($cre_type);
                if(count($credit) > 0){
                    $credit = $credit[0]['code'];
                }
                break;

            case Texts::$CALLBACK_DATA["GIVE_CREDIT_MCI"]:
                $cre_type = 'mci';
                $credit = DB_::getCredit($cre_type);
                if(count($credit) > 0){
                    $credit = $credit[0]['code'];
                }
                break;
        }

        if($credit){
            DB_::insertCredit($cre_type, $credit, 1);
        }

        return $credit;
    }

    public function handleCallBack(Update $result) {
        $chat_id = $result->getCallbackQuery()->getMessage()->getChat()->getId();
        if($chat_id < 0){
            $data = [
                'chat_id' => $chat_id,
                'text' => 'لطفا در پیوی پیام بدهید!!!',
                'parse_mode' => 'HTML',
            ];

            return Request::sendMessage($data);
        }
        $callbackData = $result->getCallbackQuery()->getData();
        $data = [];

        switch($callbackData) {
            case Texts::$CALLBACK_DATA["GIVE_CREDIT_IRANCELL"]:
            case Texts::$CALLBACK_DATA["GIVE_CREDIT_MCI"]:
                $AddedDb = DB_::getUserAdded($chat_id);
                $text = '';
                if(count($AddedDb) > 0 && (intval($AddedDb[0]['addedCount']) >= (self::$minScore * (intval($AddedDb[0]['gived_credit']) + 1)))){
                    $credit = $this->getCredit($callbackData);
                    if(!$credit){
                        $text = 'دریافت شارژ با مشکل مواجه شد.' . "\n" . "حداکثر تا 2 ساعت دیگر مشکل برطرف خواهد شد." . "\n\n" .
                            "لطفا بعد از این زمان مجددا برای دریافت شارژ اقدام کنید.";
                    }else{
                        $text = 'دریافت شارژ با موفقیت انجام شد.' . "\n" . "کد شارژ:" . "\n\n" . $credit;

                        DB_::updateCreditCount($chat_id, $AddedDb[0]['gived_credit'] + 1);
                    }
                }
                $data = [
                    'chat_id' => $chat_id,
                    'text' => $text,
                    'parse_mode' => 'HTML',
                ];

                break;

            case Texts::$CALLBACK_DATA["GIVE_LINK"]:
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::GetUserLink($chat_id),
                    'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                    'parse_mode' => 'HTML',
                ];

                Request::editMessageText($data);

                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::$FORWARD_THIS,
                    'parse_mode' => 'HTML',
                ];
                $data['reply_markup'] = new Keyboard([Texts::$GET_STATE, Texts::$GIVE_LINK]);
                $data['reply_markup']->resize_keyboard = true;

                print_r($data);
                break;

            /*case $this->CNOTACT_US:
                Texts::$ContactUsers[$chat_id] = true;
                $data = $this->getStaticMessages($this->StaticMsgs['SendContactMessage'], $chat_id);

                break;

            */
            case $this->RELOAD:
            case $this->REFRESH:
                if($callbackData == $this->RELOAD) {
                    $this->reloadBot();
                }

                $allUsers = DB::selectChats([
                    'groups' => false,
                    'supergroups' => false,
                    'channels' => false,
                    'users' => true,
                ]);
                $allGroups = DB::selectChats([
                    'groups' => true,
                    'supergroups' => false,
                    'channels' => false,
                    'users' => false,
                ]);
                $allSupergroups = DB::selectChats([
                    'groups' => false,
                    'supergroups' => true,
                    'channels' => false,
                    'users' => false,
                ]);


                date_default_timezone_set('Asia/Tehran');
                $date = date('Y-m-d h-i-s');
                $text = "<code>------------پنل مدیریتی ربات------------</code>" . "\n\n\n" . $date;

                $data = [
                    'chat_id' => $chat_id,
                    'text' => $text,
                    'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                    'parse_mode' => 'HTML',
                ];

                $keyboard_buttons = [
                    new InlineKeyboardButton([
                        'text' => count($allGroups) . ' گروه',
                        'callback_data' => 'test',
                    ]),
                    new InlineKeyboardButton([
                        'text' => count($allSupergroups) . ' سوپرگروه',
                        'callback_data' => 'test',
                    ]),
                    new InlineKeyboardButton([
                        'text' => count($allUsers) . ' کاربر',
                        'callback_data' => 'test',
                    ]),
                ];

                $keyboard_buttons3 = [
                    new InlineKeyboardButton([
                        'text' => '♻️ Refresh',
                        'callback_data' => $this->REFRESH,
                    ]),
                    new InlineKeyboardButton([
                        'text' => '♻️ Reload',
                        'callback_data' => $this->RELOAD,
                    ]),
                ];

                $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
                $data["reply_markup"]->inline_keyboard[1] = $keyboard_buttons3;

                return Request::editMessageText($data);

                break;

            case $this->VIEW_MESSAGES:
                $messages = DB_::getContact(null, 'no');
                if(count($messages)) {
                    $msg = $messages[0]['message'];

                    $user = $this->getUser($messages[0]['chat_id']);

                    $data = [
                        'chat_id' => $chat_id,
                        'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                        'text' => "پیام کاربر:\n\n" . $user . "\n\n" . $msg,
                    ];

                    $keyboard_buttons = [
                        new InlineKeyboardButton([
                            'text' => 'مشاهده شد',
                            'callback_data' => json_encode([
                                'data' => [
                                    'MarkAsRead' => 0
                                ]
                            ]),
                        ]),
                        new InlineKeyboardButton([
                            'text' => 'پاسخ',
                            'callback_data' => json_encode([
                                'data' => [
                                    'AnswerTo' => 0
                                ]
                            ]),
                        ]),
                    ];
                    $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);

                    if(count($messages) > 1) {
                        $keyboard_buttons = [
                            new InlineKeyboardButton([
                                'text' => 'بعدی',
                                'callback_data' => json_encode([
                                    'data' => [
                                        'NextMessage' => 1
                                    ]
                                ]),
                            ])
                        ];
                        $keyboard_buttons1 = [
                            new InlineKeyboardButton([
                                'text' => 'مشاهده شد',
                                'callback_data' => json_encode([
                                    'data' => [
                                        'MarkAsRead' => 0
                                    ]
                                ]),
                            ]),
                            new InlineKeyboardButton([
                                'text' => 'پاسخ',
                                'callback_data' => json_encode([
                                    'data' => [
                                        'AnswerTo' => 0
                                    ]
                                ]),
                            ]),
                        ];

                        $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
                        $data["reply_markup"]->inline_keyboard[1] = $keyboard_buttons1;
                    }
                }
                else {
                    $data = [
                        'chat_id' => $chat_id,
                        'text' => "پیام جدیدی وجود ندارد!",
                    ];
                }

                return Request::editMessageText($data);
                break;
            /*
            case $this->START_GIRL:
            case $this->START_BOY:
                $UsersSex = DB_::getUsersChatId($chat_id);

                if(count($UsersSex) <= 0 || $UsersSex[0]['sex'] === 'null') {
                    $data = $this->getStaticMessages('def', $chat_id);

                    return Request::sendMessage($data);
                }


                $isChatMember = Request::getChatMember([
                    'chat_id' => '@Crazy_lol',
                    'user_id' => $chat_id
                ]);
                if($isChatMember->getOk() && $isChatMember->getResult()->status !== 'left') {
                    $UsersSex = DB_::getUsersChatId($chat_id);
                    if($UsersSex[0]['chating_state'] === 'boy' || $UsersSex[0]['chating_state'] === 'girl') {
                        $data = [
                            'chat_id' => $chat_id,
                            'text' => Texts::$You_Are_In_List,
                            'parse_mode' => 'HTML',
                        ];
                    }
                    else if($UsersSex[0]['chating_state'] !== null && $UsersSex[0]['chating_state'] !== 'null' && $UsersSex[0]['chating_state'] !== '' && $UsersSex[0]['chating_state'] !== $this->CHAT_STATE_WAIT) {
                        $data = [
                            'chat_id' => $chat_id,
                            'text' => Texts::$You_Are_In_Chat,
                            'parse_mode' => 'HTML',
                        ];
                    }
                    else {
                        $chatWith = ($callbackData === $this->START_GIRL) ? $this->START_GIRL : $this->START_BOY;
                        $targetChatId = $this->startChat($result, $chatWith);
                        if($targetChatId) {
                            $data = $this->getStaticMessages($this->StaticMsgs['ChatStarted'], $chat_id);
                            $dataTar = $this->getStaticMessages($this->StaticMsgs['ChatStarted'], $targetChatId);
                        }
                        else {
                            $data = $this->getStaticMessages($this->StaticMsgs['AddToWaitList'], $chat_id);
                        }
                    }
                }
                else {
                    $text = "سلام 🤳\n\nاول تمرکـز کن ! 😌\n\nباید بدونـی ، عزیزتریـن آدمهای اطرافمون ، یه روزی غریبـه بودن 👁\n\nآماده ای تا وصل شی به یک ناشناس؟!\n\n👱‍♀👱\n\nپس ابتدا، توی کانال زیر عضو شو، تا بتونیم شروع کنیم 👇" .
                        "\n\nبعداز عضویت در کانال یکی از دکمه های زیر را لمس کنید.\n\n<a href='http://telegram.me/joinchat/BRw1fj3E1ND9eW5n2zucTQ'>عضـویت در کانال و شروع چت</a>";

                    $data = [
                        'chat_id' => $chat_id,
                        'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                        'text' => $text,
                        'disable_web_page_preview' => true,
                        'parse_mode' => 'HTML',
                    ];

                    $keyboard_buttons = $this->staticButtons($this->StaticBtns['Start']);
                    $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);

                    return Request::editMessageText($data);
                }

                break;

            case $this->I_AM_BOY:
            case $this->I_AM_GIRL:
                $UsersSex = DB_::getUsersChatId($chat_id);

                if(count($UsersSex) <= 0) {
                    $sex = ($callbackData === $this->I_AM_GIRL) ? 'girl' : 'boy';
                    DB_::insertUserSex($chat_id, $sex);
                }
                else {
                    $sex = ($callbackData === $this->I_AM_GIRL) ? 'girl' : 'boy';
                    DB_::updateUserSex2($chat_id, $sex);
                }
                $data = $this->getStaticMessages($this->StaticMsgs['ChooseGender'], $chat_id);*/
        }

        if(count($data) > 0) {
            return Request::sendMessage($data);
        }

        return false;
    }

    public function AdminsMessages(Update $result) {
        if(!$result->getMessage()) {
            return false;
        }
        $text = $result->getMessage()->getText();
        $answerTo = DB_::getContact(null, 'answer');
        if(count($answerTo) > 0) {
            $chat_id = $answerTo[0]['chat_id'];

            $data = [
                'chat_id' => $chat_id,
                'text' => "پیامی که شما فرستادید:
                
                " . $answerTo[0]['message'] . "
                
                پاسخ شما:
                
                " . $text . "
                ",
            ];

            Request::sendMessage($data);

            $data = [
                'chat_id' => $result->getMessage()->getChat()->getId(),
                'text' => "پاسخ شما ارسال شد.",
            ];
            Request::sendMessage($data);

            DB_::updateContact($chat_id,
                $answerTo[0]['message'], 'answered');
        }
    }

    public function getUser($chat_id) {
        $chat = null;
        $created_at = null;
        $updated_at = null;
        $result = null;
        $text = null;
        $results = DB::selectChats([
            'groups' => true,
            'supergroups' => true,
            'channels' => true,
            'users' => true,
            'chat_id' => $chat_id, //Specific chat_id to select
        ]);
        if($results[0]['chat_id']) {
//            $result['id']       = $result[0]['chat_id'];
//            $result['username'] = $result[0]['chat_username'];

            $created_at = $results[0]['chat_created_at'];
            $updated_at = $results[0]['chat_updated_at'];

            $text = 'User ID: ' . $chat_id . PHP_EOL;
            $text .= 'Name: ' . $results[0]['first_name'] . ' ' . $results[0]['last_name'] . PHP_EOL;

            $username = $results[0]['username'];
            if($username !== null && $username !== '') {
                $text .= 'Username: @' . $username . PHP_EOL;
            }

            $text .= 'First time seen: ' . $created_at . PHP_EOL;
            $text .= 'Last activity: ' . $updated_at . PHP_EOL;
        }

        if($text) {
            return $text;
        }
        else {
            return '';
        }
    }

    public function UpdateUserScore($chat_id){
        $AddedDb = DB_::getUserAdded($chat_id);
        if(count($AddedDb) > 0){
            $Added = json_decode($AddedDb[0]["Added"], true);
            $AddedCount = $AddedDb[0]["addedCount"];
            $index = 0;
            foreach($Added as $user){
//                print_r($user);
                if(isset($user["Joined"]) && ($user["Joined"] === true || $user["Joined"] === 'leave')){
                    if(isset($user["Before"]) && $user["Before"] === true){
                        // User Was In Channel Before Invite And Score Of This User Not Increased So Score Not Decrease Now
                    }else if($user["Joined"] !== 'leave'){
                        print_r('isset joined else' . PHP_EOL);
                        $isChatMember = Request::getChatMember([
                            'chat_id' => '@Crazy_lol',
                            'user_id' => $user["chat_id"]
                        ]);
                        if(!$isChatMember->getOk() || $isChatMember->getResult()->status === 'left') {
                            $Added[$user["chat_id"]]['Joined'] = 'leave';
                            $AddedCount = intval($AddedCount) - 1;
                            $AddedCount = ($AddedCount>0)?$AddedCount:0;
                        }
                    }

                    print_r('isset joined continue' . PHP_EOL);

                    $index++;
                    continue;
                }

                $isChatMember = Request::getChatMember([
                    'chat_id' => '@Crazy_lol',
                    'user_id' => $user["chat_id"]
                ]);
                if($isChatMember->getOk() && $isChatMember->getResult()->status !== 'left') {
                    print_r('$isChatMember increase ' . $chat_id . PHP_EOL);
                    $Added[$user["chat_id"]]['Joined'] = true;
                    $AddedCount = intval($AddedCount) + 1;
                }

                $index++;
            }

            DB_::newAdd($chat_id, $AddedCount, $Added);
        }
    }

    public function UsersMessages(Update $result) {
        if(!$result->getMessage()) {
            return false;
        }
        $text = $result->getMessage()->getText();
        $chat_id = $result->getMessage()->getChat()->getId();
        $data = [];

        if($text === 'report'){
            if($chat_id === 93077939 || $chat_id === 231812624){
                $AddedDB = DB_::getUserAdded('report');
                $usersCount = count($AddedDB);
                $maxScore = 0;
                $allInvited = 0;
                $maxScore_Chat_id = 0;
                foreach($AddedDB as $user){
                    $maxScore_Chat_id = intval($user["addedCount"])>$maxScore?$user["chat_id"]:$maxScore_Chat_id;
                    $maxScore = intval($user["addedCount"])>$maxScore?intval($user["addedCount"]):$maxScore;
                    if(intval($user["addedCount"]) > 0){
                        $allInvited = $allInvited + intval($user["addedCount"]);
                    }
                }

                $userInfo = self::getUser($maxScore_Chat_id);

                $data = [
                    'chat_id' => $chat_id,
                    'text' => 'تعداد کل کاربرا: ' . $usersCount . "\n\n" .
                        'بیشترین امتیاز: ' . $maxScore . " => ". $maxScore_Chat_id ."\n\n" .
                        "تعداد کل دعوت شده ها: " . $allInvited . "\n\n\nنفر اول:\n\n" . $userInfo,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML',
                ];
                return Request::sendMessage($data);

            }
        }

        $is_Contact = isset(Texts::$ContactUsers[$chat_id]) ? Texts::$ContactUsers[$chat_id] : false;


        switch($text) {
            case Texts::$Contact:
                Texts::$ContactUsers[$chat_id] = true;
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::$Send_Contact_Message,
                    'parse_mode' => 'HTML',
                ];

                break;

            case Texts::$GIVE_LINK:
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::GetUserLink($chat_id),
                    'parse_mode' => 'HTML',
                ];
                Request::sendMessage($data);

                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::$FORWARD_THIS,
                    'parse_mode' => 'HTML',
                ];

                break;

            case Texts::$GET_STATE:
                $this->UpdateUserScore($chat_id);
                /*$allAddedCount = DB_::getAllAddedCount();
                $index = 0;
                $text = '';
                foreach($allAddedCount as $item) {
                    $index++;

                    $medal = '';
                    $cup = '';
                    if($index == 1){$medal = "🥇";$cup = '🏆🏆';}
                    if($index == 2){$medal = "🥈";}
                    if($index == 3){$medal = "🥉";}
                    if($item['chat_id'] == $chat_id){
                        if($index > 4){$text .= "\n...\n...";$medal = $index;}
                        $text .= "\n" . "$medal : " . $this->GetNumberSticker($item['addedCount']) . " ==> شما " . $cup;
                    }
                    else if($index < 4){
                        $text .= "\n" . "$medal : " . $this->GetNumberSticker($item['addedCount']) . " $cup";
                    }
                }*/
                $AddedDb = DB_::getUserAdded($chat_id);
                $text = (count($AddedDb) > 0)?$AddedDb[0]["addedCount"]:0;

                $data = [
                    'chat_id' => $chat_id,
                    'text' => '⚜️ امتیاز شما: ' . $text . "\n\n" .
                        "⚜️ تعداد شارژ دریافتی شما: " . ((count($AddedDb) > 0)?intval($AddedDb[0]["gived_credit"]):0) . "\n\n" .
                        "به ازای دعوت کردن هر 20 تفر یک شارژ هزار تومانی جایزه بگیرید 🔊",
                    'parse_mode' => 'HTML',
                ];
                if((count($AddedDb) > 0 && (intval($text) >= (self::$minScore * ($AddedDb[0]['gived_credit'] + 1))))){
                    $data["text"] .= "\n\nامتیاز شما بیشتر از 20 میباشد" . "\n" .
                                        "برای دریافت شارژ هزار تومانی رایگان دکمه زیر را لمس کنید:";
                    $keyboard_buttons = [
                        new InlineKeyboardButton([
                            'text' => 'شارژ ایرانسل',
                            'callback_data' => Texts::$CALLBACK_DATA["GIVE_CREDIT_IRANCELL"],
                        ]),
                        new InlineKeyboardButton([
                            'text' => 'شارژ همراه اول',
                            'callback_data' => Texts::$CALLBACK_DATA["GIVE_CREDIT_MCI"],
                        ])
                    ];

                    $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
                }

                break;

            default:
                if($is_Contact){
                    Texts::$ContactUsers[$chat_id] = false;

                    DB_::InsertToContact($chat_id, $text);
                    $data = [
                        'chat_id' => $chat_id,
                        'text' => Texts::$Contact_MSG_Received
                    ];

                    break;
                }

                break;
        }

        if(isset($data['text'])) {
            return Request::sendMessage($data);
        }
        else {
            return false;
        }
    }

    public function GetNumberSticker($NumberStr, $str = false){
        $NumberStrickers = ['0️⃣', '1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣'];
        $zero = '0️⃣';
        $output = '';
        $NumberStr = intval($NumberStr);

        if($str){
            $output = strval($NumberStr);
            if($NumberStr < 0){
                $output = '~~~';
            }
            else if($NumberStr < 10){
                $output = '~~'.$output;
            }else if($NumberStr < 100){
                $output = '~'.$output;
            }
        }
        else {
            if(intval($NumberStr) < 10) {
                $output .= $zero;
                $output .= $zero;
            }else if(intval($NumberStr) < 100){
                $output .= $zero;
            }

            $NumberStr = strval($NumberStr);
            for ($i=0; $i<strlen($NumberStr); $i++) {
                $output .= $NumberStrickers[intval($NumberStr[$i])];
            }
        }


        return $output;
    }

    public function getStaticMessages($type, $chat_id) {
        switch($type) {
            /*case $this->StaticMsgs['SendContactMessage']:
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::$Send_Contact_Message,
                    'parse_mode' => 'HTML',
                ];
                break;

            case $this->StaticMsgs['StartChat']:
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::$Welcome,
                    'parse_mode' => 'HTML',
                ];

                $keyboard_buttons = $this->staticButtons($this->StaticBtns['Start']);

                $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
                break;

            case $this->StaticMsgs['StartAgain']:
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::$Start_Again,
                    'parse_mode' => 'HTML',
                ];

                $keyboard_buttons = $this->staticButtons($this->StaticBtns['Start']);

                $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);

                break;

            case $this->StaticMsgs['EndChat']:
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::$Chat_Ended,
                    'parse_mode' => 'HTML',
                ];

                $data['reply_markup'] = Keyboard::remove();
                break;

            case $this->StaticMsgs['AddToWaitList']:
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::$Finding_User,
                    'parse_mode' => 'HTML',
                ];
                break;

            case $this->StaticMsgs['ChatStarted']:
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::$Chat_Connected,
                    'parse_mode' => 'HTML',
                ];

                $data['reply_markup'] = new Keyboard([$this->END_CHAT]);
                $data['reply_markup']->resize_keyboard = true;

                break;

            case $this->MSG_NOT_FOUND:
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::$Command_Not_Found,
                    'parse_mode' => 'HTML',
                ];

                $keyboard_buttons = $this->staticButtons($this->StaticBtns['Start']);

                $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);

                break;*/

            case 'start':
            default:
                $text = Texts::$START_MESSAGE;

                $data = [
                    'chat_id' => $chat_id,
                    'text' => $text,
                    'disable_web_page_preview' => true,
                    'parse_mode' => 'HTML',
                ];
                break;
        }

        return $data;
    }

    public function staticButtons($type) {
        $keyboard_buttons = [];
        switch($type) {
            case $this->StaticBtns['Start']:
                $keyboard_buttons = [
                    new InlineKeyboardButton([
                        'text' => Texts::$Chat_With_Girl,
                        'callback_data' => $this->START_GIRL,
                    ]),
                    new InlineKeyboardButton([
                        'text' => Texts::$Chat_With_Boy,
                        'callback_data' => $this->START_BOY,
                    ]),
                    new InlineKeyboardButton([
                        'text' => Texts::$Contact,
                        'callback_data' => $this->CNOTACT_US,
                    ])
                ];
                break;
        }

        return $keyboard_buttons;
    }
}