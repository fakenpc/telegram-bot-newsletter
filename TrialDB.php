<?php

// namespace FakeNPC\FlRuTelegramBot;
// namespace Longman\TelegramBot;

//use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
//use PDO;

class TrialDB extends DB
{
    /**
     * Initialize trial table
     */
    public static function initializeTrial()
    {
        if (!defined('TB_TRIAL')) {
            define('TB_TRIAL', self::$table_prefix . 'trial');
        }
    }

    /**
     * Select a trial from the DB
     *
     * @param int   $id
     * @param int   $user_id
     * @param int   $used
     * @param int|null $limit
     *
     * @return array|bool
     * @throws TelegramException
     */
    public static function selectTrial($id = null, $user_id = null, $used = null, $limit = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . TB_TRIAL . '`
            ';

            $where = array();

            if($id !== null) {
                if(is_array($id)) {
                    // 
                }
                else {
                    $where[] = '`id` = :id';
                }
            }

            if($user_id !== null) {
                $where[] = '`user_id` = :user_id';
            }

            if($used !== null) {
                $where[] = '`used` = :used';
            }

            if(count($where)) {
                $sql .= ' WHERE '.join(' AND ', $where);
            }

            if ($limit !== null) {
                $sql .= ' LIMIT :limit';
            }

            $sth = self::$pdo->prepare($sql);

            if($id !== null) {
                $sth->bindValue(':id', $id);
            }

            if($user_id !== null) {
                $sth->bindValue(':user_id', $user_id);
            }

            if($used !== null) {
                $sth->bindValue(':used', $used);
            }

            if ($limit !== null) {
                $sth->bindValue(':limit', $limit);
            }

            $sth->execute();

            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }

    /**
     * Insert the trial in the database
     *
     * @param string $name
     * @param string $description
     * @param int $sending_timestamp
     * @param int $disabling_timestamp
     * @param int $used
     *
     * @return string last insert id
     * @throws TelegramException
     */
    public static function insertTrial($user_id, $used)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('INSERT INTO `' . TB_TRIAL . '`
                (`user_id`, `used`)
                VALUES
                (:user_id, :used)
            ');

            // $date = self::getTimestamp();

            $sth->bindValue(':user_id', $user_id);
            $sth->bindValue(':used', $used);

            $sth->execute();

            return self::$pdo->lastInsertId();
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }

    /**
     * Update a specific run
     *
     * @param array $fields_values
     * @param array $where_fields_values
     *
     * @return bool
     * @throws TelegramException
     */
    public static function updateTrial(array $fields_values, array $where_fields_values)
    {
        return self::update(TB_TRIAL, $fields_values, $where_fields_values);
    }

    public function deleteTrial($id) 
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('DELETE FROM `' . TB_TRIAL . '`
                WHERE `id` = :id
            ');

            $sth->bindValue(':id', $id);

            return $sth->execute();
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }
}
