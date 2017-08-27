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

        $param = $this->getUpdate();
        $inviter = $param->getMessage()->getText();
        preg_match('/\d+/', $inviter, $inviterChatId);
        if(count($inviterChatId) > 0){
            $userAddedCount = DB_::getUserAddedCount($inviterChatId);
            if(count($userAddedCount) > 0) {
                $userAddedCount = intval($userAddedCount) + 1;
                print_r($inviterChatId . "===>" . $userAddedCount);
                DB_::newAdd($inviterChatId, $userAddedCount);
            }else{
                print_r($inviterChatId . "===>" . $userAddedCount);
                DB_::newAdd($inviterChatId, 0);
            }
        }

        $message = $this->getMessage();            // Get Message object

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID

        $data = $bot->getStaticMessages('start', $chat_id);

        return Request::sendMessage($data);
    }
}