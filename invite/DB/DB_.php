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
     * @param $addedArr array
     * @return bool If the insert was successful
     * @throws TelegramException
     */
    public static function newAdd($chat_id, $addedCount, $addedArr = [])
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $addedArr = json_encode($addedArr);
            $sth = self::$pdo->prepare('
                INSERT INTO `AddedDB`
                (`chat_id`, `addedCount`, `Added`)
                VALUES
                (:chat_id, :addedCount, :Added)
                ON DUPLICATE KEY UPDATE
                    `chat_id`        = VALUES(`chat_id`),
                    `addedCount`     = VALUES(`addedCount`),
                    `Added`          = VALUES(`Added`)
            ');

            $sth->bindParam(':chat_id', $chat_id, PDO::PARAM_STR);
            $sth->bindParam(':addedCount', $addedCount, PDO::PARAM_INT);
            $sth->bindParam(':Added', $addedArr, PDO::PARAM_STR);

            $status = $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }

        return $status;
    }

    public static function getUserAdded($chat_id)
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

    public static function getCredit($type, $used = 0)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $query = "SELECT * FROM `Credits` WHERE `type` = '$type' AND `used` = $used";

            $sth = self::$pdo->prepare($query);
            $sth->execute();

            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
    }

    public static function insertCredit($type, $code, $used)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('
                INSERT INTO `Credits`
                (`type`, `code`, `used`)
                VALUES
                (:cre_type, :code, :used)
                ON DUPLICATE KEY UPDATE
                    `type` = VALUES(`type`),
                    `code` = VALUES(`code`),
                    `used` = VALUES(`used`)
            ');

            $sth->bindParam(':cre_type', $type, PDO::PARAM_STR);
            $sth->bindParam(':code', $code, PDO::PARAM_INT);
            $sth->bindParam(':used', $used, PDO::PARAM_INT);

            $status = $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }

        return $status;
    }

    public static function getAllAddedCount()
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $query = "SELECT * FROM `AddedDB` ORDER BY `addedCount` DESC";

            $sth = self::$pdo->prepare($query);
            $sth->execute();

            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
    }

    public static function updateCreditCount($chat_id, $credit_count)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare("UPDATE `AddedDB`
                SET `gived_credit` = $credit_count
                WHERE `chat_id` = $chat_id
            ");

            $status = $sth->execute();

            return $status;
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
    }
}