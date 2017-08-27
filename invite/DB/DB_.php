<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 7/31/2017
 * Time: 4:36 PM
 */

namespace Longman\TelegramBot;



use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\ChosenInlineResult;
use Longman\TelegramBot\Entities\InlineQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ReplyToMessage;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Exception\TelegramException;
use PDO;
use PDOException;

class DB_ extends DB{
    public static $TB_USER_SEX = 'user_sex';
    public static $TB_CONTACT = 'contact';

    /**
     * Insert users and save their connection to chats
     *
     * @param $chat_id
     * @param $addedCount string girl or boy
     * @return bool If the insert was successful
     * @throws TelegramException
     */
    public static function newAdd($chat_id, $addedCount)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('
                INSERT INTO `AddedDB`
                (`chat_id`, `addedCount`)
                VALUES
                (:chat_id, :addedCount)
                ON DUPLICATE KEY UPDATE
                    `chat_id`        = VALUES(`chat_id`),
                    `addedCount`     = VALUES(`addedCount`)
            ');

            $sth->bindParam(':chat_id', $chat_id, PDO::PARAM_STR);
            $sth->bindParam(':addedCount', $addedCount, PDO::PARAM_STR, 255);

            $status = $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }

        return $status;
    }

    public static function getUserAddedCount($chat_id)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $query = "SELECT * FROM `AddedDB` WHERE `chat_id` = '$chat_id'";

            $sth = self::$pdo->prepare($query);
            $sth->execute();

            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
    }
}