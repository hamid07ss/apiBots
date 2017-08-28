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
use Longman\TelegramBot\DB_;
use Longman\TelegramBot\Bot;
use Longman\TelegramBot\Texts;


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

        $inviterChatId = [];

        $message = $this->getMessage();            // Get Message object

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID

        $param = $this->getUpdate();
        $inviter = $param->getMessage()->getText();

        preg_match('/\d+/', $inviter, $inviterChatId);

        $oldUser = DB_::getUserAdded($chat_id);

        if(count($inviterChatId) > 0 && intval($inviterChatId[0]) !== intval($chat_id)) {
            if(count($oldUser) <= 0) {
                $AddedDB = DB_::getUserAdded($inviterChatId[0]);
                $addedArr = json_decode($AddedDB[0]["Added"], true);
                if(count($addedArr) > 0) {
                    $addedArr[$chat_id] = [
                        'chat_id' => $chat_id
                    ];
                }
                else {
                    $addedArr = [
                        $chat_id => [
                            'chat_id' => $chat_id
                        ]
                    ];
                }

                $isChatMember = Request::getChatMember([
                    'chat_id' => '@Crazy_lol',
                    'user_id' => $chat_id
                ]);
                if($isChatMember->getOk() && $isChatMember->getResult()->status !== 'left') {
                    $addedArr[$chat_id]['Joined'] = true;
                    $addedArr[$chat_id]['Before'] = true;
                }


                $userAddedCount = $AddedDB;
                if(count($userAddedCount) > 0) {
                    $userAddedCount = intval($userAddedCount[0]["addedCount"]);
                }
                else {
                    $userAddedCount = 0;
                }


                DB_::newAdd($inviterChatId[0], $userAddedCount, $addedArr);
            }
            else {
                DB_::newAdd($chat_id, 0);
            }
        }
        else if($chat_id > 0) {
            DB_::newAdd($chat_id, 0);
        }


        $data = $bot->getStaticMessages('start', $chat_id);
        Request::sendMessage($data);

        $data = [
            'chat_id' => $chat_id,
            'text' => '',
            'disable_web_page_preview' => true,
            'parse_mode' => 'HTML',
        ];

        $isChatMember = Request::getChatMember([
            'chat_id' => '@Crazy_lol',
            'user_id' => $chat_id
        ]);

        if($isChatMember->getOk() && $isChatMember->getResult()->status !== 'left') {
            $data['text'] = Texts::$JOINED_START_MESSAGE;
            $keyboard_buttons = [
                new InlineKeyboardButton([
                    'text' => Texts::$GIVE_LINK,
                    'callback_data' => Texts::$CALLBACK_DATA["GIVE_LINK"],
                ])
            ];

            $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
        }else{
            $data['text'] = Texts::$NOT_JOINED_START_MESSAGE;
        }

        return Request::sendMessage($data);
    }
}