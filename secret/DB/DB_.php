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

    public static function insertMd5($MD5, $text)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare("
                INSERT INTO `md_locked`
                (`md5`, `text`)
                VALUES
                ('$MD5', '$text')
            ");

            $status = $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }

        return $status;
    }

    public static function getMd5($md5)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $query = "SELECT `text` FROM `md_locked` WHERE `md5` = '$md5'";

            $sth = self::$pdo->prepare($query);
            $sth->execute();

            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
    }
}