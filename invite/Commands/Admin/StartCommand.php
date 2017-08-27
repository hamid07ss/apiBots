<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/31/2017
 * Time: 11:12 AM
 */

namespace Longman\TelegramBot\Commands\AdminCommands;

use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\DB_;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\InlineKeyboard;


class StartCommand extends AdminCommand {
    protected $name = 'start';                      // Your command's name
    protected $description = 'Panel Of Bot For Admin'; // Your command description
    protected $usage = '/start';                    // Usage of your command
    protected $version = '1.0.0';                  // Version of your command

    /**
     * Execute command
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute() {
        global $telegram, $bot;

        $message = $this->getMessage();            // Get Message object

        $chat_id = $message->getChat()->getId();   // Get the current Chat ID

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

        $text = "<code>------------پنل مدیریتی ربات------------</code>";

        /*.
        "\n\n" . "تعداد کاربران:" .
        "\n" . count($allUsers) .
        "\n\n" . "تعداد گروه ها:" .
        "\n" . count($allGroups) .
        "\n\n" . "تعداد سوپرگروه ها:" .
        "\n" . count($allSupergroups)  .
        "\n\n" . "تعداد کاربران پسر:" .
        "\n" . count($allBoys)   .
        "\n\n" . "تعداد کاربران دختر:" .
        "\n" . count($allGirls) */

        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
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

        $keyboard_buttons2 = [
            new InlineKeyboardButton([
                'text' => '♻️ Refresh',
                'callback_data' => $bot->REFRESH,
            ]),
            new InlineKeyboardButton([
                'text' => '♻️ Reload',
                'callback_data' => $bot->RELOAD,
            ]),
        ];

        $data['reply_markup'] = new InlineKeyboard($keyboard_buttons);
        $data["reply_markup"]->inline_keyboard[1] = $keyboard_buttons1;
        $data["reply_markup"]->inline_keyboard[2] = $keyboard_buttons2;

        return Request::sendMessage($data);
    }
}