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
use Longman\TelegramBot\Texts;
use function PHPSTORM_META\type;

class Bot
{
    public function __construct()
    {
        global $telegram;
        $this->telegram = $telegram;
    }

    public function handleMessages(Update $result)
    {
        Autoloader::register();
        if ($result->getMessage()) {
            if ($this->telegram->isAdmin()) {
                $this->AdminsMessages($result);
            } else {
                $this->UsersMessages($result);
            }
        }
        if ($result->getCallbackQuery()) {
            $this->handleCallBack($result);
        }

        return false;
    }

    public function GetNumberSticker($NumberStr, $str = false)
    {
        $NumberStrickers = ['0️⃣', '1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣'];
        $zero = '0️⃣';
        $output = '';
        $NumberStr = intval($NumberStr);

        if ($str) {
            $output = strval($NumberStr);
            if ($NumberStr < 0) {
                $output = '~~~';
            } else if ($NumberStr < 10) {
                $output = '~~' . $output;
            } else if ($NumberStr < 100) {
                $output = '~' . $output;
            }
        } else if (intval($NumberStr) > 0) {
            if (intval($NumberStr) < 10) {
                $output .= $zero;
                $output .= $zero;
            } else if (intval($NumberStr) < 100) {
                $output .= $zero;
            }
            $NumberStr = strval($NumberStr);
            for ($i = 0; $i < strlen($NumberStr); $i++) {
                $output .= $NumberStrickers[intval($NumberStr[$i])];
            }
        } else {
            $output = '❌❌❌';
        }

        return $output;
    }

    public function handleCallBack(Update $result)
    {
        $chat_id = $result->getCallbackQuery()->getMessage()->getChat()->getId();
        $callbackData = json_decode($result->getCallbackQuery()->getData());

        switch ($callbackData->action) {
            case "SendProxy":
                print("SendProxy");
                $data = [];
                $data['chat_id'] = "@IRProxyTel";
                $link = $this->createProxyLink($callbackData);
                $data['text'] = $this->ProxyText($link);
                $data['reply_markup'] = new InlineKeyboard([
                    new InlineKeyboardButton([
                        'text' => 'Connect to Proxy',
                        'url' => $link,
                    ]),
                ]);
                Request::sendMessage($data);

                break;
        }

        return false;
    }

    public function ProxyText($link)
    {
        $url = parse_url($link);
        parse_str($url['query'], $params);
        return "*New Proxy:*\n\n" .
            "*Server*: `" . $params['server'] . "`" .
            "\n*Port*: `" . $params['port'] . "`" .
            "\n*Secret*: `" . $params['secret'] . "`" .
            "\n\n• *Click to Connect*: [Proxy]($link) | [Channel](https://t.me/IRProxyTel)";
    }

    public function createProxyLink($params)
    {
        $link = 'https://t.me/proxy?server='. $params['server'] .'&port='. $params['port'] .'&secret='. $params['secret'];
        return $link;
    }

    public function ProxyParams($link)
    {
        $url = parse_url($link);
        parse_str($url['query'], $params);
        return [
            'server' => $params['server'],
            'port' => $params['port'],
            'secret' => $params['secret'],
        ];
    }

    public function isProxy($link)
    {
        $link = parse_url($link);
        parse_str($link['query'], $params);
        if ($params["server"] && $params["secret"]) {
            return true;
        }

        return false;
    }

    public function AdminsMessages(Update $result)
    {
        print("This is Admin");
        $message = $result->getMessage()->getText();
        $chat_id = $result->getMessage()->getChat()->getId();
        if ($this->isProxy($message)) {
            $proxy = $message;
            $proxyP = $this->ProxyParams($message);
            $text = $this->ProxyText($proxy);

            $buttons = [
                new InlineKeyboardButton([
                    'text' => 'Connect to Proxy',
                    'url' => $message,
                ]),
            ];

            $data = [
                'chat_id' => $chat_id,
                'text' => $text,
                'disable_web_page_preview' => true,
                'parse_mode' => "Markdown",
                'reply_markup' => new InlineKeyboard($buttons),
            ];
            $data["reply_markup"]->inline_keyboard[1] = [
                new InlineKeyboardButton([
                    'text' => 'Send To Channel',
                    'callback_data' => $proxyP['secret'],
                    /*'callback_data' => json_encode([
                        'action' => "SendProxy",
                        'server' => $proxyP['server'],
                        'port' => $proxyP['port'],
                        'secret' => $proxyP['secret'],
                    ]),*/
                ])
            ];

            var_dump($proxyP['secret']);
            var_dump(Request::sendMessage($data));
            return true;
        }

        $data = [
            'chat_id' => $chat_id,
            'text' => 'What????',
        ];
        return Request::sendMessage($data);
    }

    public function UsersMessages(Update $result)
    {
        $chat_id = $result->getMessage()->getChat()->getId();
        $text = "سلام به ربات خوش آمدید!";

        $keyboard_buttons = [
            new InlineKeyboardButton([
                'text' => 'Connect Proxy',
                'url' => 'https://t.me/IRProxyTel',
            ]),
        ];
        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
            'disable_web_page_preview' => true,
            'reply_markup' => new InlineKeyboard($keyboard_buttons),
            'parse_mode' => 'Markdown',
        ];

        return Request::sendMessage($data);
    }
}