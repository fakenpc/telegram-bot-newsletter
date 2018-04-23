<?php

// namespace FakeNPC\FlRuTelegramBot;
// namespace Longman\TelegramBot;

//use Exception;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
//use PDO;

class SubscriptionDB extends DB
{
    /**
     * Initialize subscription table
     */
    public static function initializeSubscription()
    {
        if (!defined('TB_SUBSCRIPTION')) {
            define('TB_SUBSCRIPTION', self::$table_prefix . 'subscription');
        }
    }

    /**
     * Select a subscriptions from the DB
     *
     * @param int   $id
     * @param int   $sended
     * @param int|null $limit
     *
     * @return array|bool
     * @throws TelegramException
     */
    public static function selectSubscription($id = null, $newsletter_category_id = null, $limit = null)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sql = '
              SELECT *
              FROM `' . TB_SUBSCRIPTION . '`
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
            
            if ($newsletter_category_id !== null) {
                $where[] = '`newsletter_category_id` = :newsletter_category_id';
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
     * Insert the subscription in the database
     *
     * @param int $id
     * @param int $sended
     *
     * @return bool
     * @throws TelegramException
     */
    public static function insertSubscription($name, $newsletter_category_id, $duration, $price)
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('INSERT INTO `' . TB_SUBSCRIPTION . '`
                (`name`, `newsletter_category_id`, `duration`, `price`)
                VALUES
                (:name, :newsletter_category_id, :duration, :price)
            ');

            // $date = self::getTimestamp();

            $sth->bindValue(':name', $name);
            $sth->bindValue(':newsletter_category_id', $newsletter_category_id);
            $sth->bindValue(':duration', $duration);
            $sth->bindValue(':price', $price);

            return $sth->execute();
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
    public static function updateSubscription(array $fields_values, array $where_fields_values)
    {
        return self::update(TB_SUBSCRIPTION, $fields_values, $where_fields_values);
    }

    public function deleteSubscription($id) 
    {
        if (!self::isDbConnected()) {
            return false;
        }

        try {
            $sth = self::$pdo->prepare('DELETE FROM `' . TB_SUBSCRIPTION . '`
                WHERE `id` = :id
            ');

            $sth->bindValue(':id', $id);

            return $sth->execute();
        } catch (Exception $e) {
            throw new TelegramException($e->getMessage());
        }
    }
}
