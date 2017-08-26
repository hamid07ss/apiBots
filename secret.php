<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/31/2017
 * Time: 9:46 AM
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/secret/DB/DB_.php';
require __DIR__ . '/secret/Strings/Texts.php';
require __DIR__ . '/secret/Bot.php';

$API_KEY = '351537792:AAHlrKuvPW0UTBExkYpVSpf1vF_hrJNDacQ';
$BOT_NAME = 'SecretChat_Robot';
$mysql_credentials = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '123',
    'database' => 'secret',
];
$commands_paths = [
    __DIR__ . '/secret/Commands/User',
    __DIR__ . '/secret/Commands/Admin'
];

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram($API_KEY, $BOT_NAME);
    $bot = new Longman\TelegramBot\Bot();

    // Enable MySQL
    $telegram->enableMySQL($mysql_credentials);
	//, 231812624
    $telegram->enableAdmins([93077939]);
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