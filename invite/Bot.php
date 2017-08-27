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

    public function handleCallBack(Update $result) {
        $chat_id = $result->getCallbackQuery()->getMessage()->getChat()->getId();
        $callbackData = $result->getCallbackQuery()->getData();
        $data = [];
        $dataTar = [];

        switch($callbackData) {
            case Texts::$CALLBACK_DATA["GIVE_LINK"]:
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::GetUserLink($chat_id),
                    'parse_mode' => 'HTML',
                ];

                $data['reply_markup'] = new Keyboard([Texts::$GET_STATE["name"]]);
                $data['reply_markup']->resize_keyboard = true;

                Request::sendMessage($data);

                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::$FORWARD_THIS,
                    'parse_mode' => 'HTML',
                ];
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

                $allGirls = DB_::selectUsersSex('girl');
                $allBoys = DB_::selectUsersSex('boy');
                $allOnlines = DB_::selectUsersSex('all', 'wait', true);


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

                $keyboard_buttons1 = [
                    new InlineKeyboardButton([
                        'text' => count($allGirls) . ' 👱‍♀️ دختر',
                        'callback_data' => 'test',
                    ]),
                    new InlineKeyboardButton([
                        'text' => count($allBoys) . ' 👦 پسر',
                        'callback_data' => 'test',
                    ]),
                    new InlineKeyboardButton([
                        'text' => count($allOnlines) . ' 🔵 آنلاین',
                        'callback_data' => 'test',
                    ]),
                ];

                $users_in_wait = DB_::selectUsersSex('all', 'boy');
                $users_in_wait2 = DB_::selectUsersSex('all', 'girl');
                $users_in_girl = (count($users_in_wait)) . ' کاربر منتظر پسرن';
                $users_in_boy = (count($users_in_wait2)) . ' کاربر منتظر دخترن';
                $keyboard_buttons2 = [
                    new InlineKeyboardButton([
                        'text' => $users_in_boy,
                        'callback_data' => 'test',
                    ]),
                    new InlineKeyboardButton([
                        'text' => $users_in_girl,
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
                $data["reply_markup"]->inline_keyboard[1] = $keyboard_buttons1;
                $data["reply_markup"]->inline_keyboard[2] = $keyboard_buttons2;
                $data["reply_markup"]->inline_keyboard[3] = $keyboard_buttons3;

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

    public function UsersMessages(Update $result) {
        if(!$result->getMessage()) {
            return false;
        }
        $text = $result->getMessage()->getText();
        $chat_id = $result->getMessage()->getChat()->getId();
        $data = [];

        switch($text) {
            case Texts::$GET_STATE["name"]:
                $addedCount = DB_::getUserAddedCount($chat_id);
                $data = [
                    'chat_id' => $chat_id,
                    'text' => 'تعداد افراد دعوت شده توسط شما:' . "\n\n" . (count($addedCount)>0)?$addedCount[0]["addedCount"]:0,
                    'parse_mode' => 'HTML',
                ];

                break;
        }

        if(isset($data['text'])) {
            return Request::sendMessage($data);
        }
        else {
            return false;
        }
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

                $keyboard_buttons = [
                    new InlineKeyboardButton([
                        'text' => Texts::$GIVE_LINK,
                        'callback_data' => Texts::$CALLBACK_DATA["GIVE_LINK"],
                    ])
                ];

                $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
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