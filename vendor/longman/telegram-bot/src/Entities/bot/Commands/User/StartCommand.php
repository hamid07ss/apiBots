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

        $message = $this->getMessage();            // Get Message object

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID

        /*$allUsers = DB::selectChats([
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
        $allChannels = DB::selectChats([
            'groups' => false,
            'supergroups' => false,
            'channels' => true,
            'users' => false,
        ]);*/

        $data = $bot->getStaticMessages('detect', $chat_id);

        return Request::sendMessage($data);
    }
}