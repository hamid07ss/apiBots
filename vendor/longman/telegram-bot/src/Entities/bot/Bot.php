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
use Longman\TelegramBot\DB_;
use function PHPSTORM_META\type;

class Bot {
    protected $telegram;
    private $START_CHAT = 'start chat';
    private $I_AM_BOY = 'i am boy';
    private $I_AM_GIRL = 'i am girl';

    private $START_BOY = 'chat with boy';
    private $START_GIRL = 'chat with girl';

    public $VIEW_MESSAGES = 'view messages';

    private $CNOTACT_US = 'contact us';
    public $REFRESH = 'REFRESH';

    private $END_CHAT = 'پایان چت ناشناس';

    private $MSG_NOT_FOUND = 'msg not found';

    private $CHAT_STATE_WAIT = 'wait';

    public $StaticMsgs = [
        'ChooseGender' => 'ChooseGender',
        'StartChat' => 'StartChat',
        'ChatStarted' => 'ChatStarted',
        'EndChat' => 'EndChat',
        'StartAgain' => 'StartAgain',
        'AddToWaitList' => 'AddToWaitList',
        'SendContactMessage' => 'SendContactMessage'
    ];

    public $StaticBtns = [
        'Start' => 'Start'
    ];

    public function __construct() {
        global $telegram;
        $this->telegram = $telegram;

        /*if(!class_exists(\DB_::class)){
            die("CLASS DB_ not found");
        }*/
    }

    public function handleMessages(Update $result) {
        $type = $result->getMessage();
        if ($type && $type->getType() === 'command') {
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
        else if($result->getCallbackQuery()){
            $this->handleCallBack($result);
        }
    }

    public function startChat(Update $result, $chat_with){
        $chat_id = $result->getCallbackQuery()->getMessage()->getChat()->getId();
        switch($chat_with){
            case $this->START_BOY:
                $Users = 'boy';
                break;
            case $this->START_GIRL:
                $Users = 'girl';
                break;
            default:
                $Users = 'all';
                break;
        }

        $Users = DB_::selectUsersSex($Users, $this->CHAT_STATE_WAIT);

        $is_user_in_wait = false;
        if(count($Users) > 0){
            foreach($Users as $user){
                if($user["chat_id"] === strval($chat_id) || $this->telegram->isAdmin(intval($user["chat_id"]))){
                    $is_user_in_wait = true;
                    continue;
                }

                DB_::updateUserSex($chat_id, $user["chat_id"]);
                DB_::updateUserSex($user["chat_id"], $chat_id);

                return $user["chat_id"];
            }
        }

        if(!$is_user_in_wait){
            DB_::updateUserSex($chat_id, $this->CHAT_STATE_WAIT);
        }
        return false;
    }

    public function endChat($chat_id, $targetChatId){
        DB_::updateUserSex($chat_id, $this->CHAT_STATE_WAIT);
        DB_::updateUserSex($targetChatId, $this->CHAT_STATE_WAIT);
    }

    public function handleCallBack(Update $result) {
        $chat_id = $result->getCallbackQuery()->getMessage()->getChat()->getId();
        $callbackData = $result->getCallbackQuery()->getData();
        $data = [];
        $dataTar = [];

        switch($callbackData) {
            case $this->CNOTACT_US:
                DB_::InsertToContact($chat_id);
                $data = $this->getStaticMessages($this->StaticMsgs['SendContactMessage'], $chat_id);

                break;

            case $this->REFRESH:
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
                $date= date('Y-m-d h-i-s') ;
                $text = "<code>------------پنل مدیریتی ربات------------</code>



$date";

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
                        'text' => count($allGirls) . ' دختر',
                        'callback_data' => 'test',
                    ]),
                    new InlineKeyboardButton([
                        'text' => count($allBoys) . ' پسر',
                        'callback_data' => 'test',
                    ]),
                    new InlineKeyboardButton([
                        'text' => count($allOnlines) . ' آنلاین',
                        'callback_data' => 'test',
                    ]),
                ];

                $keyboard_buttons2 = [
                    new InlineKeyboardButton([
                        'text' => 'Refresh',
                        'callback_data' => $this->REFRESH,
                    ]),
                ];

                $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
                $data["reply_markup"]->inline_keyboard[1] = $keyboard_buttons1;
                $data["reply_markup"]->inline_keyboard[2] = $keyboard_buttons2;

                return Request::editMessageText($data);

                break;

            case $this->VIEW_MESSAGES:
                $messages = DB_::getContact(null, 'no');
                if(count($messages)){
                    $msg = $messages[0]['message'];
                    $data = [
                        'chat_id' => $chat_id,
                        'text' => "پیام کاربر:
                    
                    
                    $msg
                    ",
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

                    if(count($messages) > 1){
                        $keyboard_buttons = [
                            new InlineKeyboardButton([
                                'text' => 'قبلی(غیر فعال)',
                                'callback_data' => 'NO',
                            ]),
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
                }else{
                    $data = [
                        'chat_id' => $chat_id,
                        'text' => "پیام جدیدی وجود ندارد!",
                    ];
                }

                break;

            case $this->START_GIRL:
            case $this->START_BOY:
                $chatWith = ($callbackData === $this->START_GIRL)?$this->START_GIRL:$this->START_BOY;
                $targetChatId = $this->startChat($result, $chatWith);
                if($targetChatId){
                    $data = $this->getStaticMessages($this->StaticMsgs['ChatStarted'], $chat_id);
                    $dataTar = $this->getStaticMessages($this->StaticMsgs['ChatStarted'], $targetChatId);
                }else{
                    $data = $this->getStaticMessages($this->StaticMsgs['AddToWaitList'], $chat_id);
                }

                break;

            case $this->I_AM_BOY:
            case $this->I_AM_GIRL:
                $sex = ($callbackData === $this->I_AM_GIRL) ? 'girl' : 'boy';
                DB_::insertUserSex($chat_id, $sex);
                $data = $this->getStaticMessages($this->StaticMsgs['ChooseGender'], $chat_id);
        }

        if(json_decode($callbackData, true)['data']){
            $callbackData = json_decode($callbackData, true)['data'];
            $messages = DB_::getContact(null, 'no');
            if(isset($callbackData['NextMessage'])){
                $msg = $messages[$callbackData['NextMessage']]['message'];
                $data = [
                    'chat_id' => $chat_id,
                    'text' => "پیام کاربر:
                    
                    
                    $msg
                    ",
                ];

                if(count($messages) > 1){
                    $keyboard_buttons = [
                        new InlineKeyboardButton([
                            'text' => 'قبلی',
                            'callback_data' => json_encode([
                                'data' => [
                                    'NextMessage' => intval($callbackData['NextMessage']) - 1
                                ]
                            ]),
                        ]),
                        new InlineKeyboardButton([
                            'text' => 'بعدی',
                            'callback_data' => json_encode([
                                'data' => [
                                    'NextMessage' => intval($callbackData['NextMessage']) + 1
                                ]
                            ]),
                        ])
                    ];
                    $keyboard_buttons1 = [
                        new InlineKeyboardButton([
                            'text' => 'مشاهده شد',
                            'callback_data' => json_encode([
                                'data' => [
                                    'MarkAsRead' => $callbackData['NextMessage']
                                ]
                            ]),
                        ]),
                        new InlineKeyboardButton([
                            'text' => 'پاسخ',
                            'callback_data' => json_encode([
                                'data' => [
                                    'AnswerTo' => $callbackData['NextMessage']
                                ]
                            ]),
                        ]),
                    ];

                    $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
                    $data["reply_markup"]->inline_keyboard[1] = $keyboard_buttons1;
                }
            }
            else if(isset($callbackData['MarkAsRead'])){
                DB_::InsertToContact($messages[$callbackData['MarkAsRead']]["chat_id"],
                    $messages[$callbackData['MarkAsRead']]["message"], 'yes');
            }
            else if(isset($callbackData['AnswerTo'])){
                DB_::InsertToContact($messages[$callbackData['AnswerTo']]["chat_id"],
                    $messages[$callbackData['AnswerTo']]["message"], 'answer');

                $data = [
                    'chat_id' => $chat_id,
                    'text' => "پاسخ خود را ارسال کنید.",
                ];
            }
        }

        $sendRes = true;
        if(count($dataTar) > 0){
            $sendRes = Request::sendMessage($dataTar);
        }

        if(count($data) > 0 && ($sendRes->getOk() || $sendRes)){
            return Request::sendMessage($data);
        }else if($sendRes !== true && !$sendRes->getOk()){
            DB_::deleteUserSex($dataTar);
        }

        return false;
    }

    public function AdminsMessages(Update $result) {
        $text = $result->getMessage()->getText();
        $answerTo = DB_::getContact(null, 'answer');
        if(count($answerTo) > 0){
            $chat_id = $answerTo[0]['chat_id'];

            $data = [
                'chat_id' => $chat_id,
                'text' => "پیامی که شما فرستادید:
                
                ". $answerTo[0]['message'] ."
                
                پاسخ شما:
                
                ". $text ."
                ",
            ];

            Request::sendMessage($data);

            $data = [
                'chat_id' => $result->getMessage()->getChat()->getId(),
                'text' => "پاسخ شما ارسال شد.",
            ];
            Request::sendMessage($data);

            DB_::InsertToContact($chat_id,
                $answerTo[0]['message'], 'answered');
        }
    }

    public function UsersMessages(Update $result) {
        $text = $result->getMessage()->getText();
        $chat_id = $result->getMessage()->getChat()->getId();
        $targetChatId = DB_::getTargetChatId($chat_id);
        $data = [];

        $is_Contact = DB_::getContact($chat_id);
        if(count($is_Contact)){
            $is_Contact = $is_Contact[0]["message"];
        }else{
            $is_Contact = false;
        }

        $is_Contact = ($is_Contact === 'wait');

        switch($text){
            case $this->END_CHAT:
                if(count($targetChatId) && $targetChatId[0]['chating_state'] !== $this->CHAT_STATE_WAIT && $targetChatId !== null){
                    $this->endChat($chat_id, $targetChatId[0]['chating_state']);

                    Request::sendMessage($this->getStaticMessages($this->StaticMsgs['EndChat'], $chat_id));
                    Request::sendMessage($this->getStaticMessages($this->StaticMsgs['EndChat'], $targetChatId[0]['chating_state']));

                    Request::sendMessage($this->getStaticMessages($this->StaticMsgs['StartAgain'], $chat_id));
                    Request::sendMessage($this->getStaticMessages($this->StaticMsgs['StartAgain'], $targetChatId[0]['chating_state']));
                }

                break;

            case $is_Contact:
                DB_::InsertToContact($chat_id, $text);
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::$Contact_MSG_Received
                ];

                break;

            default:
                if(count($targetChatId) && $targetChatId[0]['chating_state'] !== $this->CHAT_STATE_WAIT && $targetChatId !== null){
                    $data = [
                        'chat_id' => $targetChatId[0]['chating_state'],
                        'text' => $text
                    ];
                }else{
                    $data = $this->getStaticMessages($this->MSG_NOT_FOUND, $chat_id);
                }
                break;
        }

        return Request::sendMessage($data);
    }

    public function getStaticMessages($type, $chat_id){
        switch($type) {
            case $this->StaticMsgs['SendContactMessage']:
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

                $keyboard_buttons =  $this->staticButtons($this->StaticBtns['Start']);

                $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
                break;

            case $this->StaticMsgs['StartAgain']:
                $data = [
                    'chat_id' => $chat_id,
                    'text' => Texts::$Start_Again,
                    'parse_mode' => 'HTML',
                ];

                $keyboard_buttons =  $this->staticButtons($this->StaticBtns['Start']);

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

                $keyboard_buttons =  $this->staticButtons($this->StaticBtns['Start']);

                $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);

                break;

            case $this->StaticMsgs['ChooseGender']:
            default:
                $UsersSex = DB_::getUsersChatId($chat_id);

                if(!$UsersSex){
                    $data = [
                        'chat_id' => $chat_id,
                        'text' => Texts::$Choose_Gender,
                        'parse_mode' => 'HTML',
                    ];

                    $keyboard_buttons = [
                        new InlineKeyboardButton([
                            'text' => Texts::$Boy,
                            'callback_data' => $this->I_AM_BOY,
                        ]),
                        new InlineKeyboardButton([
                            'text' => Texts::$Girl,
                            'callback_data' => $this->I_AM_GIRL,
                        ])
                    ];

                    $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
                }else {
                    print_r($UsersSex);
                    if($UsersSex[0]['chating_state'] === null || $UsersSex[0]['chating_state'] === $this->CHAT_STATE_WAIT) {
                        $data = [
                            'chat_id' => $chat_id,
                            'text' => Texts::$Welcome,
                            'parse_mode' => 'HTML',
                        ];

                        $keyboard_buttons = $this->staticButtons($this->StaticBtns['Start']);

                        $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
                    }else{
                        $data = [
                            'chat_id' => $chat_id,
                            'text' => Texts::$You_Are_In_Chat,
                            'parse_mode' => 'HTML',
                        ];
                    }
                }
                break;
        }

        return $data;
    }

    public function staticButtons($type){
        $keyboard_buttons = [];
        switch($type){
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