<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/31/2017
 * Time: 9:46 AM
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/invite/DB/DB_.php';
require __DIR__ . '/invite/Strings/Texts.php';
require __DIR__ . '/invite/Bot.php';

$API_KEY = '423140927:AAHIIkAi695WNzlPLGw0q1jhfVrTfGeUML8';
$BOT_NAME = 'doostyabi_free_bot';
$mysql_credentials = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '123',
    'database' => 'bot',
];
$commands_paths = [
    __DIR__ . '/invite/Commands/User',
    __DIR__ . '/invite/Commands/Admin'
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
        $data = $telegram->handleGetUpdates(100, 0);
        print_r(count($data->getResult()));
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