<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/31/2017
 * Time: 9:46 AM
 */


require "/root/phpBot/predis/autoload.php";
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/panel/DB/DB_.php';
require __DIR__ . '/panel/Strings/Texts.php';
require __DIR__ . '/panel/Bot.php';

$API_KEY = '441613748:AAE6fA7F0pEMOwlhkIsFty2cvxPMefbVMAU';
$BOT_NAME = 'Tabchi_Panel_bot';
$mysql_credentials = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '123',
    'database' => 'panel',
];
$commands_paths = [
    __DIR__ . '/panel/Commands/Admin'
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