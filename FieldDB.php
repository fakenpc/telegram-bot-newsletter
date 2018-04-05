<?php

// namespace FakeNPC\FlRuTelegramBot;
// namespace Longman\TelegramBot;

//use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
//use PDO;

class FieldDB extends DB
{
    /**
     * Initialize newsletter table
     */
    public static function initializeField()
    {
        if (!defined('TB_FIELD')) {
            define('TB_FIELD', self::$table_prefix . 'field');
        }
    }

    /**
     * Select a newsletters from the DB
     *
     * @param int   $id
     * @param string   $name
     * @param string   $value
     * @param string   $type
     * @param int   $order
     * @param int|null $limit
     *
     * @return array|bool
     * @throws TelegramException
     */
    public static function selectField($id = null, $name = null, $value = null, $type = null, $order = null, $limit = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . TB_FIELD . '`
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

            if($name !== null) {
                $where[] = '`name` = :name';
            }

            if($value !== null) {
                $where[] = '`value` = :value';
            }


            if($type !== null) {
                $where[] = '`type` = :type';
            }


            if($order !== null) {
                $where[] = '`order` = :order';
            }

            if(count($where)) {
                $sql .= ' WHERE '.join(' AND ', $where);
            }

            $sql .= ' ORDER BY `order` ';

            if ($limit !== null) {
                $sql .= ' LIMIT :limit';
            }

            $sth = self::$pdo->prepare($sql);

            if($id !== null) {
                $sth->bindValue(':id', $id);
            }

            if($name !== null) {
                $sth->bindValue(':name', $name);
            }

            if($value !== null) {
                $sth->bindValue(':value', $value);
            }

            if($type !== null) {
                $sth->bindValue(':type', $type);
            }

            if($order !== null) {
                $sth->bindValue(':order', $order);
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
     * Insert the newsletter in the database
     *
     * @param string $name
     * @param string $description
     * @param int $sending_timestamp
     * @param int $disabling_timestamp
     * @param int $value
     *
     * @return string last insert id
     * @throws TelegramException
     */
    public static function insertField($name, $value, $type, $order)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('INSERT INTO `' . TB_FIELD . '`
                (`name`, `value`, `type`, `order`)
                VALUES
                (:name, :value, :type, :order)
            ');

            // $date = self::getTimestamp();

            $sth->bindValue(':name', $name);
            $sth->bindValue(':value', $value);
            $sth->bindValue(':type', $type);
            $sth->bindValue(':order', $order);

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
    public static function updateField(array $fields_values, array $where_fields_values)
    {
        return self::update(TB_FIELD, $fields_values, $where_fields_values);
    }

    public function deleteField($id) 
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('DELETE FROM `' . TB_FIELD . '`
                WHERE `id` = :id
            ');

            $sth->bindValue(':id', $id);

            return $sth->execute();
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }
}
