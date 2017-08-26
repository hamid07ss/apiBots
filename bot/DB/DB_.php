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
     * @param $sex string girl or boy
     * @param null $chat_state
     * @return bool If the insert was successful
     * @throws TelegramException
     */
    public static function insertUserSex($chat_id, $sex, $chat_state = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare("
                INSERT INTO `" . self::$TB_USER_SEX . "`
                (`chat_id`, `sex`, `chating_state`)
                VALUES
                ($chat_id, '$sex', '$chat_state')
            ");

            $status = $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }

        return $status;
    }

    public static function updateUserSex($chat_id, $state){
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            if(!$chat_id || $chat_id === 'boy' || $chat_id === 'girl' || $chat_id === 'null'){
                return false;
            }
//            $state = ($state === '')?'null':$state;
            $state = ($state === '' || $state === null)?'null':$state;


            $query = 'UPDATE `' . self::$TB_USER_SEX . '`
SET `chating_state` = \''.$state.'\'
WHERE `chat_id` = '.$chat_id;
            $sth = self::$pdo->prepare($query);

            $data1 = [
                'chat_id' => '@HamidLog',
                'text' => "<code>Change State:</code>\n\n".
                    "<code>$chat_id => $state\n\n\n"
                    .''.$query.'</code>',
                'parse_mode' => 'HTML'
            ];

            //Request::sendMessage($data1);

            $status = $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }

        return $status;
    }

    public static function updateUserSex2($chat_id, $sex){
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare("
                UPDATE `" . self::$TB_USER_SEX . "`
                SET `sex` = '$sex'
                WHERE `chat_id` = $chat_id
            ");

            $status = $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }

        return $status;
    }

    public static function deleteUserSex($chat_id){
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare("
                DELETE FROM `" . self::$TB_USER_SEX . "`
                WHERE `chat_id` = $chat_id
            ");

            $status = $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }

        return $status;
    }

    /**
     * Select Groups, Supergroups, Channels and/or single user Chats (also by ID or text)
     *
     * @param $sex
     * @param null $chat_state
     * @return array|bool
     * @throws TelegramException
     * @internal param $select_chats_params
     */
    public static function selectUsersSex($sex, $chat_state = null, $getOnlines = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            if($chat_state){
                $query = "SELECT * FROM ". self::$TB_USER_SEX ." WHERE `sex` = '$sex'
                     && `chating_state` = '$chat_state'";
                if($sex === 'all'){
                    $query = "SELECT * FROM ". self::$TB_USER_SEX ." WHERE `chating_state` = '$chat_state'";
                }
            }else{
                $query = "SELECT * FROM ". self::$TB_USER_SEX ." WHERE `sex` = '$sex'";
                if($sex === "all"){
                    $query = "SELECT * FROM ". self::$TB_USER_SEX;
                }
            }

            if($getOnlines !== null){
                $query = "SELECT * FROM ". self::$TB_USER_SEX ." WHERE `chating_state` <> 'wait' AND `chating_state` <> 'null' AND `chating_state` <> ''".
                    " AND `chating_state` <> 'boy' AND `chating_state` <> 'girl'";
            }

//            $query .= ' desc';
            $sth = self::$pdo->prepare($query);
            $sth->execute();

            return array_reverse($sth->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
    }

    public static function removeChat($chat_id){
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $query = "SET foreign_key_checks = 0;DELETE FROM `chat` WHERE id=$chat_id";

            $sth = self::$pdo->prepare($query);

            return $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
    }

    public static function getTargetChatId($chat_id)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $query = "SELECT `chating_state` FROM ". self::$TB_USER_SEX ." WHERE `chat_id` = '$chat_id'";

            $sth = self::$pdo->prepare($query);
            $sth->execute();

            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
    }

    public static function getUsersChatId($chat_id = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $query = "SELECT * FROM ". self::$TB_USER_SEX;
            if($chat_id){
                $query .= " WHERE `chat_id` = '$chat_id'";
            }

            $sth = self::$pdo->prepare($query);
            $sth->execute();

            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
    }


    public static function updateContact($chat_id, $message = null, $checked = null){
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $message = ($message === null)?'wait':$message;
            $checked = ($checked === null)?'no':$checked;

            $query = "UPDATE `" . self::$TB_CONTACT . "`
                        SET `message` = '$message',
                        `checked` = '$checked'
                        WHERE `chat_id` = $chat_id";

            $sth = self::$pdo->prepare($query);

            return $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
    }


    public static function InsertToContact($chat_id, $message = null, $checked = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $message = ($message === null)?'wait':$message;
            $checked = ($checked === null)?'no':$checked;

            $query = "INSERT INTO ". self::$TB_CONTACT ."
            (`chat_id`, `message`, `checked`)
            VALUES
            ($chat_id, '$message', '$checked')";

            $sth = self::$pdo->prepare($query);

            return $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
    }

    public static function getContact($chat_id = null, $answer = null){
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $query = "SELECT * FROM ". self::$TB_CONTACT;
            if($chat_id){
                $query .= " WHERE `chat_id` = $chat_id";
                if($answer){
                    $query .= " AND `checked` = '$answer'";
                }
            }
            else if($answer){
                $query .= " WHERE `checked` = '$answer'";
            }

            $sth = self::$pdo->prepare($query);
            $sth->execute();

            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new TelegramException($e->getMessage());
        }
    }
}