<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/31/2017
 * Time: 10:40 AM
 */


namespace Longman\TelegramBot;

use Predis\Autoloader;
use Predis\Client;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\DB_;
use function PHPSTORM_META\type;

class Bot {
    protected $telegram;
    private $START_CHAT = 'start chat';
    private $I_AM_BOY = 'i am boy';
    private $I_AM_GIRL = 'i am girl';

    private $START_BOY = 'chat with boy';
    private $START_GIRL = 'chat with girl';

    private $Change_My_Gender = 'change gender';

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
	
	public $DBs = [
        'TelethonWaitLinks' => 'TelethonWaitLinks',
        'TelethonGoodLinks' => 'TelethonGoodLinks',
        "Admins" => 'TelethonAdmins',
        "AllLinks" => 'TelethonAllLinks',
        "ErrorLogs" => 'TelethonErrorLogs',
        "SuperGroups" => 'TelethonSuperGroupsList',
        "Privates" => 'TelethonPrivatesList',
        "CheckLinkLimit" => 'TelethonCheckLinkLimit',
        "JoinLinkLimit" => 'TelethonJoinLinkLimit',
        "AutoJoinLink" => 'TelethonAutoJoinLink',
        "AutoCheckLink" => 'TelethonAutoCheckLink',
        "ForwardMessageGroupsCount" => 'TelethonForwardMessageGroupsCount',
        "ForwardMessagePrivatesCount" => 'TelethonForwardMessagePrivatesCount',
        "ForwardMessageGroups" => 'TelethonForwardMessageGroups',
        "ForwardMessagePrivates" => 'TelethonForwardMessagePrivates',
        "ForwardMessageMsgId" => 'TelethonForwardMessageMsgId',
        "ForwardMessageMsgTxt" => 'TelethonForwardMessageMsgTxt',
        "ForwardMessagePeerId" => 'TelethonForwardMessagePeerId',
        "GetDialogsRequest" => 'TelethonGetDialogsRequest',
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

    public function ConnectChats() {
        $WantChat_to_boy = DB_::selectUsersSex('all', 'boy');
        $WantChat_to_girl = DB_::selectUsersSex('all', 'girl');
        $dataTar = [];
        $data = [];

        $userChatId = '';
        $targetChatId = '';


        print_r("\n");
        print_r('want boy => ' . count($WantChat_to_boy));
        print_r("\n");
        print_r('want girl => ' . count($WantChat_to_girl));

        if(count($WantChat_to_boy) > 0) {
            $index1 = 0;
            foreach($WantChat_to_boy as $want_boy) {
                switch($want_boy['sex']) {
                    case 'boy':
                        $index2 = 0;
                        foreach($WantChat_to_boy as $targetWant_boy) {
                            if($targetWant_boy['chat_id'] !== $want_boy['chat_id'] && $targetWant_boy['sex'] === 'boy') {
                                $data = $this->getStaticMessages($this->StaticMsgs['ChatStarted'], $want_boy['chat_id']);
                                $dataTar = $this->getStaticMessages($this->StaticMsgs['ChatStarted'], $targetWant_boy["chat_id"]);

                                $userChatId = $want_boy['chat_id'];
                                $targetChatId = $targetWant_boy['chat_id'];

                                array_splice($WantChat_to_boy, $index1, 1);
                                array_splice($WantChat_to_boy, $index2, 1);

                                print_r("\n");
                                print_r('boy want boy => target sex => ' . $targetWant_boy['sex']);

                                break;
                            }

                            $index2++;
                        }
                        break;

                    case 'girl':
                        if(count($WantChat_to_girl) > 0) {
                            $index2 = 0;
                            foreach($WantChat_to_girl as $targetWant_girl) {
                                if($targetWant_girl['chat_id'] !== $want_boy['chat_id'] && $targetWant_girl['sex'] === 'boy') {
                                    $data = $this->getStaticMessages($this->StaticMsgs['ChatStarted'], $want_boy['chat_id']);
                                    $dataTar = $this->getStaticMessages($this->StaticMsgs['ChatStarted'], $targetWant_girl["chat_id"]);

                                    $userChatId = $want_boy['chat_id'];
                                    $targetChatId = $targetWant_girl['chat_id'];

                                    array_splice($WantChat_to_boy, $index1, 1);
                                    array_splice($WantChat_to_girl, $index2, 1);

                                    print_r("\n");
                                    print_r('girl want boy => target sex => ' . $targetWant_girl['sex']);
                                    break;
                                }
                                $index2++;
                            }
                        }
                        break;
                }

                if(count($dataTar) > 0) {
                    $sendRes = Request::sendMessage($dataTar);

                    if(count($data) > 0 && ($sendRes->getOk() || $sendRes)) {
                        DB_::updateUserSex($userChatId, $targetChatId);
                        DB_::updateUserSex($targetChatId, $userChatId);
                        Request::sendMessage($data);
                    }
                    else if($sendRes !== true && !$sendRes->getOk()) {
                        DB_::deleteUserSex($dataTar);
                    }
                }


                $index1++;
            }
        }

        if(count($WantChat_to_girl) > 0) {
            $index1 = 0;
            foreach($WantChat_to_girl as $want_girl) {
                switch($want_girl['sex']) {
                    case 'boy':
                        if(count($WantChat_to_boy) > 0) {
                            $index2 = 0;
                            foreach($WantChat_to_boy as $targetWant_boy) {
                                if($targetWant_boy['chat_id'] !== $want_girl['chat_id'] && $targetWant_boy['sex'] === 'girl') {
                                    $data = $this->getStaticMessages($this->StaticMsgs['ChatStarted'], $want_girl['chat_id']);
                                    $dataTar = $this->getStaticMessages($this->StaticMsgs['ChatStarted'], $targetWant_boy["chat_id"]);

                                    $userChatId = $want_girl['chat_id'];
                                    $targetChatId = $targetWant_boy['chat_id'];

                                    array_splice($WantChat_to_girl, $index1, 1);
                                    array_splice($WantChat_to_boy, $index2, 1);

                                    print_r("\n");
                                    print_r('boy want girl => target sex => ' . $targetWant_boy['sex']);

                                    break;
                                }


                                $index2++;
                            }
                        }
                        break;

                    case 'girl':
                        $index2 = 0;
                        foreach($WantChat_to_girl as $targetWant_girl) {
                            if($targetWant_girl['chat_id'] !== $want_girl['chat_id'] && $targetWant_girl['sex'] === 'girl') {
                                $data = $this->getStaticMessages($this->StaticMsgs['ChatStarted'], $want_girl['chat_id']);
                                $dataTar = $this->getStaticMessages($this->StaticMsgs['ChatStarted'], $targetWant_girl["chat_id"]);

                                $userChatId = $want_girl['chat_id'];
                                $targetChatId = $targetWant_girl['chat_id'];

                                array_splice($WantChat_to_girl, $index1, 1);
                                array_splice($WantChat_to_girl, $index2, 1);

                                print_r("\n");
                                print_r('girl want girl => target sex => ' . $targetWant_girl['sex']);

                                break;
                            }

                            $index2++;
                        }
                        break;
                }

                if(count($dataTar) > 0) {
                    $sendRes = Request::sendMessage($dataTar);

                    if(count($data) > 0 && ($sendRes->getOk() || $sendRes)) {
                        DB_::updateUserSex($userChatId, $targetChatId);
                        DB_::updateUserSex($targetChatId, $userChatId);
                        Request::sendMessage($data);
                    }
                    else if($sendRes !== true && !$sendRes->getOk()) {
                        DB_::deleteUserSex($dataTar);
                    }
                }


                $index1++;
            }
        }
    }

    public function Buttons(){
        $data = [];
        $keyboard_buttons = [
            new InlineKeyboardButton([
                'text' => 'Links',
                'callback_data' => 'Links',
            ]),
            new InlineKeyboardButton([
                'text' => 'Super GP',
                'callback_data' => 'SuperGroup',
            ]),
            new InlineKeyboardButton([
                'text' => 'Next Join',
                'callback_data' => 'NextJoin',
            ])
        ];

        $keyboard_buttons2 = [
            new InlineKeyboardButton([
                'text' => 'Bots In This Group',
                'callback_data' => 'BotsInThisGroup',
            ]),
            new InlineKeyboardButton([
                'text' => 'Check Links',
                'callback_data' => 'CheckLinks',
            ])
        ];

        $keyboard_buttons1 = [
            new InlineKeyboardButton([
                'text' => 'Join Off',
                'callback_data' => 'JoinOff',
            ]),
            new InlineKeyboardButton([
                'text' => 'Join On',
                'callback_data' => 'JoinOn',
            ]),
        ];

        $keyboard_buttons0 = [
            new InlineKeyboardButton([
                'text' => 'Restart',
                'callback_data' => 'Restart',
            ]),
            new InlineKeyboardButton([
                'text' => 'Update Code',
                'callback_data' => 'Update Code',
            ])
        ];

        $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
        $data["reply_markup"]->inline_keyboard[1] = $keyboard_buttons2;
        $data["reply_markup"]->inline_keyboard[2] = $keyboard_buttons1;
        $data["reply_markup"]->inline_keyboard[3] = $keyboard_buttons0;

        return $data["reply_markup"];
    }

    public function handleMessages(Update $result) {
        Autoloader::register();

        try {
            $redis = new Client();


            if($result->getMessage()){
                $type = $result->getMessage();
                if($type && $type->getType() === 'command') {
                    return false;
                }

                $data = [];
                $chat_id = $result->getMessage()->getChat()->getId();
                $text = $result->getMessage()->getText();
                switch(Texts::$What_To_Do){
                    case 'JoinOff':
                        if(intval($text) > 0){
                            $redis->del('bot' . $text . 'canJoin');
                            $data = [
                                'chat_id' => $chat_id,
                                'text' => 'عضویت خودکار ربات شماره '. intval($text) .' غیرفعال شد!',
                                'parse_mode' => 'HTML',
                            ];
                        }

                        Texts::$What_To_Do = '';
                        break;

                    case 'JoinOn':
                        if(intval($text) > 0){
                            $redis->set('bot' . $text . 'canJoin', true);
                            $data = [
                                'chat_id' => $chat_id,
                                'text' => 'عضویت خودکار ربات شماره '. intval($text) .' فعال شد!',
                                'parse_mode' => 'HTML',
                            ];
                        }

                        Texts::$What_To_Do = '';
                        break;

                }

                if(isset($data['text'])) {
                    $data['reply_markup'] = $this->Buttons();


                    return Request::sendMessage($data);
                }
            }
            if($result->getCallbackQuery()) {
                $data = [];
                $chat_id = $result->getCallbackQuery()->getMessage()->getChat()->getId();
                $defultBtns = true;


                switch($result->getCallbackQuery()->getData()) {
                    case 'CheckLinkTrue':
                        $redis->srem($this->DBs["TelethonWaitLinks"], Texts::$Link);
                        $redis->sadd($this->DBs["TelethonGoodLinks"], Texts::$Link);


                        $text = $redis->smembers($this->DBs["TelethonWaitLinks"]);
                        $text = $text[0];

                        Texts::$Link = $text;

                        $text = 'https://t.me/joinchat/' . $text;
                        $text .= "\n\n<b>All Links => </b><code>". $this->GetNumberSticker($redis->scard($this->DBs["TelethonWaitLinks"]), true) ."</code>";
                        $text .= "\n\n<b>Checked Links => </b><code>". $this->GetNumberSticker($redis->scard($this->DBs["TelethonGoodLinks"]), true) ."</code>";


                        $data = [
                            'chat_id' => $chat_id,
                            'text' => $text,
                            'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                            'parse_mode' => 'HTML',
                        ];

                        $keyboard_buttons1 = [
                            new InlineKeyboardButton([
                                'text' => '✅️',
                                'callback_data' => 'CheckLinkTrue',
                            ]),
                            new InlineKeyboardButton([
                                'text' => '❌',
                                'callback_data' => 'CheckLinkFalse',
                            ]),
                        ];

                        $data['reply_markup'] = new InlineKeyboard($keyboard_buttons1);

                        $defultBtns = false;

                        break;

                    case 'CheckLinkFalse':
                        $redis->srem($this->DBs["TelethonWaitLinks"], Texts::$Link);


                        $text = $redis->smembers($this->DBs["TelethonWaitLinks"]);
                        $text = $text[0];

                        Texts::$Link = $text;

                        $text = 'https://t.me/joinchat/' . $text;
                        $text .= "\n\n<b>All Links => </b><code>". $this->GetNumberSticker($redis->scard($this->DBs["TelethonWaitLinks"]), true) ."</code>";
                        $text .= "\n\n<b>Checked Links => </b><code>". $this->GetNumberSticker($redis->scard($this->DBs["TelethonGoodLinks"]), true) ."</code>";

                        $data = [
                            'chat_id' => $chat_id,
                            'text' => $text,
                            'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                            'parse_mode' => 'HTML',
                        ];

                        $keyboard_buttons1 = [
                            new InlineKeyboardButton([
                                'text' => '✅️',
                                'callback_data' => 'CheckLinkTrue',
                            ]),
                            new InlineKeyboardButton([
                                'text' => '❌',
                                'callback_data' => 'CheckLinkFalse',
                            ]),
                        ];

                        $data['reply_markup'] = new InlineKeyboard($keyboard_buttons1);

                        $defultBtns = false;
                        break;

                    case 'CheckLinks':
                        $text = $redis->smembers($this->DBs["TelethonWaitLinks"]);
                        $text = $text[0];
                        Texts::$Link = $text;

                        $text = 'https://t.me/joinchat/' . $text;
                        $text .= "\n\n<b>All Links => </b><code>". $this->GetNumberSticker($redis->scard($this->DBs["TelethonWaitLinks"]), true) ."</code>";
                        $text .= "\n\n<b>Checked Links => </b><code>". $this->GetNumberSticker($redis->scard($this->DBs["TelethonGoodLinks"]), true) ."</code>";

                        $data = [
                            'chat_id' => $chat_id,
                            'text' => $text,
                            'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                            'parse_mode' => 'HTML',
                        ];

                        $keyboard_buttons1 = [
                            new InlineKeyboardButton([
                                'text' => '✅️',
                                'callback_data' => 'CheckLinkTrue',
                            ]),
                            new InlineKeyboardButton([
                                'text' => '❌',
                                'callback_data' => 'CheckLinkFalse',
                            ]),
                        ];

                        $data['reply_markup'] = new InlineKeyboard($keyboard_buttons1);

                        $defultBtns = false;


                        break;

                    case 'SuperGroup':
                    case 'Links':
                        $Bots = glob('/root/tabchi/Telethon/bot-*.session');
                        $Links = [];
                        $title = ($result->getCallbackQuery()->getData() === 'Links') ?
                            'Bots Links' : 'Bots Super Groups';
                        $text = '<code>' . $title . '</code>' . "\n";

                        if($result->getCallbackQuery()->getData() === 'Links') {
                            $text .= "\n" . 'Bots all Links ==> ' . $redis->scard($this->DBs["TelethonWaitLinks"]);
                            $text .= "\n\n" . 'Bots Checked Links ==> ' . $redis->scard($this->DBs["TelethonGoodLinks"]);
                        }
                        else {
                            foreach($Bots as $bot) {
                                $botName = basename($bot);
                                preg_match_all('/bot-(.*).session/', $botName, $botNum);
                                print_r('$botNum ==> ' . $botNum);
                                $DBName = $this->DBs["SuperGroups"] + $botNum[1][0];

                                $Links[intval($botNum[0][0])] = $redis->scard($DBName);
                            }
                            ksort($Links);
                            foreach($Links as $index => $link) {
                                $text .= "\n<b>" . $this->GetNumberSticker($index, true) . "</b><code>=> SGP=> </code><b>" . $this->GetNumberSticker($link, true) . "</b>";
                                $text .= "*️⃣<code>join=></code> " . ($redis->get('bot' . $index . 'canJoin') ? '✅️' : '❌');
                            }
                        }

                        $data = [
                            'chat_id' => $chat_id,
                            'text' => $text,
                            'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                            'parse_mode' => 'HTML',
                        ];
                        break;

                    case 'NextJoin':
                        $Bots = glob('/home/ubuntu131/tabchi/newTabchi/tabchi-*.lua');
                        $Links = [];
                        $title = 'Bots Next Join';
                        $text = '<code>' . $title . '</code>' . "\n";
                        foreach($Bots as $bot) {
                            $botName = basename($bot);
                            preg_match_all('!\d+!', $botName, $botNum);
                            $DBName = 'bot' . $botNum[0][0] . 'joinexpire';

                            $Links[intval($botNum[0][0])] = $redis->ttl($DBName);
                        }
                        ksort($Links);
                        foreach($Links as $index => $link) {
                            $text .= "\n<b>" . $this->GetNumberSticker($index, true) . "</b><code>=>Join: </code><b>" . $this->GetNumberSticker($link, true) . '</b>';
                            $text .= '*️⃣<code>Check: </code><b>' . $this->GetNumberSticker($redis->ttl('bot' . $index . 'checkexpire'), true) . '</b>';
                        }

                        $data = [
                            'chat_id' => $chat_id,
                            'text' => $text,
                            'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                            'parse_mode' => 'HTML',
                        ];

                        break;

                    case 'Update Code':
                        $out = shell_exec('cd /root/tabchi/Telethon/ && git pull git master');

                        $data = [
                            'chat_id' => $chat_id,
                            'text' => "Updated: \n" . $out,
                            'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                            'parse_mode' => 'HTML',
                        ];
                        break;

                    case 'Restart':
                        $out = shell_exec('tmux kill-session -t autolaunch');
                        $out += shell_exec('cd /root/tabchi/Telethon/ && tmux new-session -d -s autolaunch ./auto start');

                        $data = [
                            'chat_id' => $chat_id,
                            'text' => "restarted: \n" . $out,
                            'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                            'parse_mode' => 'HTML',
                        ];
                        break;

                    case 'JoinOff':
                    case 'JoinOn':
                        Texts::$What_To_Do = $result->getCallbackQuery()->getData();
                        $data = [
                            'chat_id' => $chat_id,
                            'text' => 'شماره ربات مورد نظر خود را وارد کنید:',
                            'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                            'parse_mode' => 'HTML',
                        ];


                        break;

                    case 'BotsInThisGroup':
                        $res = shell_exec('lua /home/ubuntu131/tabchi/newTabchi/config.lua');
                        $Bots = json_decode($res);
                        $text = '<code>Bots In This Group</code>' . "\n";
                        foreach($Bots as $index => $Bot){
                            $isChatMember = Request::getChatMember([
                                'chat_id' => $chat_id,
                                'user_id' => $Bot
                            ]);
                            $text .= "\n<b>" . $this->GetNumberSticker($index + 1, true) . " => </b>".
                                (($isChatMember->getOk() && $isChatMember->getResult()->status !== 'kicked' && $isChatMember->getResult()->status !== 'left') ? '✅️' : '❌');
                        }

                        $data = [
                            'chat_id' => $chat_id,
                            'text' => $text,
                            'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                            'parse_mode' => 'HTML',
                        ];

                        break;
                }


                if(isset($data['text'])) {
                    $Bots = glob('/home/ubuntu131/tabchi/newTabchi/tabchi-*.lua');
                    if($defultBtns)
                        $data['reply_markup'] = $this->Buttons();
                    $data['text'] .= "\n\n<b>Bots Count => </b>" . $this->GetNumberSticker(count($Bots));

                    return Request::editMessageText($data);
                }
            }

        }
        catch(Exception $e) {
            die($e->getMessage());
        }

        return false;
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
        else if(intval($NumberStr) > 0){
            if(intval($NumberStr) < 10){
                $output .= $zero;
                $output .= $zero;
            }else if(intval($NumberStr) < 100){
                $output .= $zero;
            }
            $NumberStr = strval($NumberStr);
            for ($i=0; $i<strlen($NumberStr); $i++) {
                $output .= $NumberStrickers[intval($NumberStr[$i])];
            }
        }else{
            $output = '❌❌❌';
        }

        return $output;
    }

    public function startChat(Update $result, $chat_with) {
        $chat_id = $result->getCallbackQuery()->getMessage()->getChat()->getId();
        switch($chat_with) {
            case $this->START_BOY:
                $targetGender = 'boy';
                break;
            case $this->START_GIRL:
                $targetGender = 'girl';
                break;
            default:
                $targetGender = 'all';
                break;
        }
        $userGender = DB_::getUsersChatId($chat_id);
        print_r("\n");
        print_r($userGender);
        $userGender = $userGender[0]['sex'];
        print_r("\n");
        print_r("user Gender => $userGender");
        print_r("user Gender => $userGender");

        $Users = DB_::selectUsersSex($targetGender, $userGender);

        $is_user_in_wait = false;
        if(count($Users) > 0) {
            foreach($Users as $user) {
                if($user["chat_id"] === strval($chat_id) || $this->telegram->isAdmin(intval($user["chat_id"]))) {
                    $is_user_in_wait = true;
                    continue;
                }

                print_r("\n");
                print_r($userGender . ' want ' . $targetGender . ' => target sex => ' . $user['sex']);

                DB_::updateUserSex($chat_id, $user["chat_id"]);
                DB_::updateUserSex($user["chat_id"], $chat_id);

                return $user["chat_id"];
            }
        }

        if(!$is_user_in_wait) {
            DB_::updateUserSex($chat_id, $targetGender);
        }
        return false;
    }

    public function endChat($chat_id, $targetChatId) {
        DB_::updateUserSex($chat_id, null);
        DB_::updateUserSex($targetChatId, null);
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

    public function handleCallBack(Update $result) {
        $chat_id = $result->getCallbackQuery()->getMessage()->getChat()->getId();
        $callbackData = $result->getCallbackQuery()->getData();
        $data = [];
        $dataTar = [];

        switch($callbackData) {
            case $this->Change_My_Gender:
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

                break;

            case $this->CNOTACT_US:
                Texts::$ContactUsers[$chat_id] = true;
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
                        'text' => 'Refresh',
                        'callback_data' => $this->REFRESH,
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
                }
                else {
                    $data = [
                        'chat_id' => $chat_id,
                        'text' => "پیام جدیدی وجود ندارد!",
                    ];
                }

                return Request::editMessageText($data);
                break;

            case $this->START_GIRL:
            case $this->START_BOY:
                $isChatMember = Request::getChatMember([
                    'chat_id' => '@Crazy_lol',
                    'user_id' => $chat_id
                ]);
                if($isChatMember->getOk() && $isChatMember->getResult()->status !== 'left') {
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
                else {
                    $text = "سلام به ربات #سکرت_چت خوش آمدید!


✌️ اگه میخوای یه #حرفی رو به یک نفر بگی و روت نمیشه؟!!

😉 اگه میخوای به یک زبون عجیب و غریب صحبت کنی و دوستات نفهمن قضیه چیه!!؟

😋 اگه میخوای به شخصی پیام بدی ولی دوست داری غیرمستقیم پیام و حرفتو بهش بگی !!؟!



این ربات بهترین گزینس 🍉😃


فقط قبلش باید توی کانال زیر عضو شی👇" .
                        "\n\n<a href='http://telegram.me/joinchat/BRw1fj3E1ND9eW5n2zucTQ'>عضـویت در کانال</a>";

                    $data = [
                        'chat_id' => $chat_id,
                        'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                        'text' => $text,
                        'disable_web_page_preview' => true,
                        'parse_mode' => 'HTML',
                    ];

                    return Request::editMessageText($data);
                }

                break;

            case $this->I_AM_BOY:
            case $this->I_AM_GIRL:
                $UsersSex = DB_::getUsersChatId($chat_id);

                if(count($UsersSex) <= 0) {
                    print_r("\n");
                    print_r('new user');

                    $sex = ($callbackData === $this->I_AM_GIRL) ? 'girl' : 'boy';
                    DB_::insertUserSex($chat_id, $sex);
                }
                else {
                    $sex = ($callbackData === $this->I_AM_GIRL) ? 'girl' : 'boy';
                    print_r("\n");
                    print_r('update user gender to => ' . $sex);
                    DB_::updateUserSex2($chat_id, $sex);
                }
                $data = $this->getStaticMessages($this->StaticMsgs['ChooseGender'], $chat_id);
        }

        if(json_decode($callbackData, true)['data']) {
            $callbackData = json_decode($callbackData, true)['data'];
            $messages = DB_::getContact(null, 'no');
            if(isset($callbackData['NextMessage'])) {
                $msg = $messages[$callbackData['NextMessage']]['message'];

                $user = $this->getUser($messages[0]['chat_id']);

                $data = [
                    'chat_id' => $chat_id,
                    'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                    'text' => "پیام کاربر:\n\n" . $user . "\n\n" . $msg,
                ];

                if(count($messages) > 1) {
                    $prev = (intval($callbackData['NextMessage']) === 0) ? 'قبلی (غیرفعال)' : 'قبلی';
                    $next = (intval($callbackData['NextMessage']) === (count($messages) - 1)) ? 'بعدی (غیرفعال)' : 'بعدی';
                    $keyboard_buttons = [
                        new InlineKeyboardButton([
                            'text' => $prev,
                            'callback_data' => json_encode([
                                'data' => [
                                    'NextMessage' => (intval($callbackData['NextMessage']) === 0) ? 0 : (intval($callbackData['NextMessage']) - 1)
                                ]
                            ]),
                        ]),
                        new InlineKeyboardButton([
                            'text' => $next,
                            'callback_data' => json_encode([
                                'data' => [
                                    'NextMessage' => (intval($callbackData['NextMessage']) === (count($messages) - 1)) ? (count($messages) - 1) : (intval($callbackData['NextMessage']) + 1)
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
            else if(isset($callbackData['MarkAsRead'])) {
                DB_::updateContact($messages[$callbackData['MarkAsRead']]["chat_id"],
                    $messages[$callbackData['MarkAsRead']]["message"], 'yes');
            }
            else if(isset($callbackData['AnswerTo'])) {
                DB_::updateContact($messages[$callbackData['AnswerTo']]["chat_id"],
                    $messages[$callbackData['AnswerTo']]["message"], 'answer');

                $data = [
                    'chat_id' => $chat_id,
                    'message_id' => $result->getCallbackQuery()->getMessage()->getMessageId(),
                    'text' => "پاسخ خود را ارسال کنید.",
                ];
            }

            if(count($data)) {
                return Request::editMessageText($data);
            }
            else {
                return false;
            }

        }

        $sendRes = true;
        if(count($dataTar) > 0) {
            $sendRes = Request::sendMessage($dataTar);

            if(count($data) > 0 && ($sendRes->getOk() || $sendRes)) {
                return Request::sendMessage($data);
            }
            else if($sendRes !== true && !$sendRes->getOk()) {
                DB_::deleteUserSex($dataTar);
            }
        }

        if(count($data) > 0 && $sendRes === true) {
            return Request::sendMessage($data);
        }

        return false;
    }

    public function AdminsMessages(Update $result) {
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
        $targetChatId = DB_::getTargetChatId($chat_id);
        $data = [];

        $is_Contact = isset(Texts::$ContactUsers[$chat_id]) ? Texts::$ContactUsers[$chat_id] : false;

        switch($text) {
            case $this->END_CHAT:
                if(count($targetChatId) && $targetChatId[0]['chating_state'] !== $this->CHAT_STATE_WAIT && $targetChatId !== null) {
                    $this->endChat($chat_id, $targetChatId[0]['chating_state']);

                    Request::sendMessage($this->getStaticMessages($this->StaticMsgs['EndChat'], $chat_id));
                    Request::sendMessage($this->getStaticMessages($this->StaticMsgs['EndChat'], $targetChatId[0]['chating_state']));

                    Request::sendMessage($this->getStaticMessages($this->StaticMsgs['StartAgain'], $chat_id));
                    Request::sendMessage($this->getStaticMessages($this->StaticMsgs['StartAgain'], $targetChatId[0]['chating_state']));
                }

                break;

            default:
                if($is_Contact) {
                    Texts::$ContactUsers[$chat_id] = false;

                    DB_::InsertToContact($chat_id, $text);
                    $data = [
                        'chat_id' => $chat_id,
                        'text' => Texts::$Contact_MSG_Received
                    ];

                    break;
                }
                if($result->getMessage()->getChat()->getType() !== 'private') {
                    return false;
                }
                if(count($targetChatId) && intval($targetChatId[0]['chating_state']) > 100 && $targetChatId !== null) {
                    $photo = $result->getMessage()->getPhoto();

                    $data = [
                        'chat_id' => $targetChatId[0]['chating_state'],
                        'text' => $text
                    ];
                    if($photo) {
                        print_r($photo->getFileId());
                        $photo = $photo[0];
                        $data['photo'] = $photo->getFileId();
                    }

                    print_r("\n");
                    print_r("this $chat_id send message to " . $targetChatId[0]['chating_state'] . "  this message => $text");
                }
                else {
                    $data = $this->getStaticMessages($this->MSG_NOT_FOUND, $chat_id);
                }
                break;
        }

        if($text !== '') {
            return Request::sendMessage($data);
        }
        else {
            return false;
        }
    }

    public function getStaticMessages($type, $chat_id) {
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

                break;

            case $this->StaticMsgs['ChooseGender']:
            default:
                $UsersSex = DB_::getUsersChatId($chat_id);

                if(count($UsersSex) <= 0) {
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
                }
                else {
                    if($UsersSex[0]['chating_state'] === null || $UsersSex[0]['chating_state'] === '' || $UsersSex[0]['chating_state'] === $this->CHAT_STATE_WAIT) {
                        $data = [
                            'chat_id' => $chat_id,
                            'text' => Texts::$Welcome,
                            'parse_mode' => 'HTML',
                        ];

                        $keyboard_buttons = $this->staticButtons($this->StaticBtns['Start']);

                        $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
                        $data["reply_markup"]->inline_keyboard[1] = [
                            new InlineKeyboardButton([
                                'text' => Texts::$Change_My_Gender,
                                'callback_data' => $this->Change_My_Gender,
                            ])
                        ];
                    }
                    else {
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