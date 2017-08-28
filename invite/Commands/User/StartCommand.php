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

        if(count($inviterChatId) > 0 && $inviterChatId[0] != $chat_id){
            if(count($oldUser) <= 0){
                $AddedDB = DB_::getUserAdded($inviterChatId[0]);
                $addedArr = json_decode($AddedDB[0]["Added"]);
                if(count($addedArr) > 0){
                    $addedArr[] = [
                        'chat_id' => $chat_id
                    ];
                }else{
                    $addedArr = [
                        0 => [
                            'chat_id' => $chat_id
                        ]
                    ];
                }


                $userAddedCount = $AddedDB;
                if(count($userAddedCount) > 0) {
                    $userAddedCount = intval($userAddedCount[0]["addedCount"]);
                }else{
                    $userAddedCount = 0;
                }


                DB_::newAdd($inviterChatId[0], $userAddedCount, $addedArr);
            }else{
                DB_::newAdd($chat_id, 0);
            }
        }else{
            DB_::newAdd($chat_id, 0);
        }

        $data = $bot->getStaticMessages('start', $chat_id);

        return Request::sendMessage($data);
    }
}