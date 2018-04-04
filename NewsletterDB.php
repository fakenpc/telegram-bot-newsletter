<?php

// namespace FakeNPC\FlRuTelegramBot;
// namespace Longman\TelegramBot;

//use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
//use PDO;

class NewsletterDB extends DB
{
    /**
     * Initialize newsletter table
     */
    public static function initializeNewsletter()
    {
        if (!defined('TB_NEWSLETTER')) {
            define('TB_NEWSLETTER', self::$table_prefix . 'newsletter');
        }
    }

    /**
     * Select a newsletters from the DB
     *
     * @param int   $id
     * @param int   $newsletter_category_id
     * @param int   $sended
     * @param int|null $limit
     *
     * @return array|bool
     * @throws TelegramException
     */
    public static function selectNewsletter($id = null, $newsletter_category_id = null, $sended = null, $limit = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . TB_NEWSLETTER . '`
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

            if($newsletter_category_id !== null) {
                $where[] = '`newsletter_category_id` = :newsletter_category_id';
            }

            if($sended !== null) {
                $where[] = '`sended` = :sended';
            }

            if(count($where)) {
                $sql .= ' WHERE '.join(' AND ', $where);
            }

            if ($limit !== null) {
                $sql .= ' LIMIT :limit';
            }

            $sth = self::$pdo->prepare($sql);

            if($id !== null) {
                $sth->bindValue(':id', $id, PDO::PARAM_INT);
            }

            if($newsletter_category_id !== null) {
                $sth->bindValue(':newsletter_category_id', $newsletter_category_id, PDO::PARAM_INT);
            }

            if($sended !== null) {
                $sth->bindValue(':sended', $sended, PDO::PARAM_INT);
            }

            if ($limit !== null) {
                $sth->bindValue(':limit', $limit, PDO::PARAM_INT);
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
     * @param int $sended
     *
     * @return string last insert id
     * @throws TelegramException
     */
    public static function insertNewsletter($newsletter_category_id, $name, $description, $sending_timestamp, $disabling_timestamp, $sended)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('INSERT INTO `' . TB_NEWSLETTER . '`
                (`newsletter_category_id`, `name`, `description`, `sending_timestamp`, `disabling_timestamp`, `sended`)
                VALUES
                (:newsletter_category_id, :name, :description, :sending_timestamp, :disabling_timestamp, :sended)
            ');

            // $date = self::getTimestamp();

            $sth->bindValue(':newsletter_category_id', $newsletter_category_id);
            $sth->bindValue(':name', $name);
            $sth->bindValue(':description', $description);
            $sth->bindValue(':sending_timestamp', $sending_timestamp);
            $sth->bindValue(':disabling_timestamp', $disabling_timestamp);
            $sth->bindValue(':sended', $sended);

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
    public static function updateNewsletter(array $fields_values, array $where_fields_values)
    {
        return self::update(TB_NEWSLETTER, $fields_values, $where_fields_values);
    }

    public function deleteNewsletter($id) 
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('DELETE FROM `' . TB_NEWSLETTER . '`
                WHERE `id` = :id
            ');

            $sth->bindValue(':id', $id);

            return $sth->execute();
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }
}
