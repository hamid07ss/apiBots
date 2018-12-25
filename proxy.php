<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/31/2017
 * Time: 9:46 AM
 */


require __DIR__ . "/predis/autoload.php";
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/proxy/DB/DB_.php';
require __DIR__ . '/proxy/Strings/Texts.php';
require __DIR__ . '/proxy/Bot.php';

$API_KEY = '609103406:AAFN6Jw_lirBYQ3yOlD80jhvQKfMsVMcZSM';
$BOT_NAME = 'IRProxyTel';
$mysql_credentials = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'proxy',
];
$commands_paths = [
    __DIR__ . '/proxy/Commands/Admin',
    __DIR__ . '/proxy/Commands/User'
];

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($API_KEY, $BOT_NAME);
    $bot = new Longman\TelegramBot\Bot();

    // Enable MySQL
    $telegram->enableMySQL($mysql_credentials);
    $telegram->enableAdmins([93077939, 231812624]);
    $telegram->addCommandsPaths($commands_paths);

    // Handle telegram getUpdate request
    do {
        $data = $telegram->handleGetUpdates();
        if(count($data->getResult())) {
            foreach ((array) $data->getResult() as $result) {
                $bot->handleMessages($result);
            }
        }
    } while(true);
}
catch(Longman\TelegramBot\Exception\TelegramException $e) {
    // log telegram errors
    echo $e;
}