<?php

// namespace FakeNPC\FlRuTelegramBot;
// namespace Longman\TelegramBot;

//use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
//use PDO;

class NewsletterCategoryDB extends DB
{
    /**
     * Initialize newsletter_category table
     */
    public static function initializeNewsletterCategory()
    {
        if (!defined('TB_NEWSLETTER_CATEGORY')) {
            define('TB_NEWSLETTER_CATEGORY', self::$table_prefix . 'newsletter_category');
        }
    }

    /**
     * Select a newsletter_categories from the DB
     *
     * @param int   $id
     * @param int|null $limit
     *
     * @return array|bool
     * @throws TelegramException
     */
    public static function selectNewsletterCategory($id = null, $limit = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . TB_NEWSLETTER_CATEGORY . '`
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
     * Insert the newsletter_category in the database
     *
     * @param string $name
     * @param string $description
     *
     * @return string last insert id
     * @throws TelegramException
     */
    public static function insertNewsletterCategory($name, $description)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('INSERT INTO `' . TB_NEWSLETTER_CATEGORY . '`
                (`name`, `description`)
                VALUES
                (:name, :description)
            ');

            // $date = self::getTimestamp();

            $sth->bindValue(':name', $name);
            $sth->bindValue(':description', $description);

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
    public static function updateNewsletterCategory(array $fields_values, array $where_fields_values)
    {
        return self::update(TB_NEWSLETTER_CATEGORY, $fields_values, $where_fields_values);
    }

    public static function deleteNewsletterCategory($id) 
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('DELETE FROM `' . TB_NEWSLETTER_CATEGORY . '`
                WHERE `id` = :id
            ');

            $sth->bindValue(':id', $id);

            return $sth->execute();
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }
}
