<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Conversation;

require_once __DIR__.'/../SubscriberDB.php';
require_once __DIR__.'/../SubscriptionDB.php';
require_once __DIR__.'/../NewsletterCategoryDB.php';
require_once __DIR__.'/../NewsletterDB.php';
require_once __DIR__.'/../FieldDB.php';
use SubscriberDB;
use SubscriptionDB;
use NewsletterCategoryDB;
use NewsletterDB;
use FieldDB;

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */
class CallbackqueryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'callbackquery';

    /**
     * @var string
     */
    protected $description = 'Reply to callback query';

    /**
     * @var string
     */
    protected $version = '1.0.0';
    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $callback_query = $this->getCallbackQuery();
        $callback_query_id = 0;
        $callback_data = '';

        if($callback_query) {
            $message = $callback_query->getMessage();    
            $callback_query_id = $callback_query->getId();
            $callback_data     = $callback_query->getData();
            $user = $callback_query->getFrom();
        } else {
            $message = $this->getMessage();
            $user = $message->getFrom();
        }
        
        $chat = $message->getChat();
        $text = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        @list($command, $command_data) = explode(" ", $callback_data, 2);
        $result = Request::emptyResponse();
        
        SubscriberDB::initializeSubscriber();
        NewsletterDB::initializeNewsletter();
        SubscriptionDB::initializeSubscription();
        NewsletterCategoryDB::initializeNewsletterCategory();
        FieldDB::initializeField();

        switch ($command) {

            case 'newsletter_category':

                $newsletter_category_id = $command_data;
                $subscribers = SubscriberDB::selectActiveSubscriber(null, $newsletter_category_id, null, $user_id);

                $subscription_paid = false;
                $subscription_end_timestamp = 0;


                foreach($subscribers as $subscriber) {
                    if($subscriber['paid']) {
                        $subscription_paid = true;
                        $subscription_end_timestamp = $subscriber['end_timestamp'];
                    }
                }

                $newsletters = NewsletterDB::selectNewsletter(null, $newsletter_category_id);
                $flag = false;

                foreach ($newsletters as $newsletter) { 
                    if(time() < $newsletter['disabling_timestamp'] && time() > $newsletter['sending_timestamp']) {
                        $flag = true;

                        $images_dir_full_path = __DIR__.'/../images/';
                        $images_dir = '../images/';
                        $images = glob($images_dir_full_path.'newsletter_'.$newsletter['id'].'.*');

                        if(count($images)) {
                            // send photo
                            $result = Request::sendPhoto([
                                'chat_id' => $chat_id,
                                'photo'   => Request::encodeFile($images[0]),
                            ]);
                        }

                        if($subscription_paid) {
                            $text = 'Название: '.PHP_EOL.$newsletter['name'].PHP_EOL.$newsletter['description'];
                        } else {
                            $text = 'Название: '.PHP_EOL.$newsletter['name'];
                        }

                        Request::sendMessage([
                            'chat_id'      => $chat_id,
                            'text'         => $text
                        ]);
                    }
                }

                if(!$flag) {
                    Request::sendMessage([
                        'chat_id'      => $chat_id,
                        'text'         => 'В данный момент нет актуальных сообщений рассылки. Зайдите позже.'
                    ]);
                }

                if($subscription_paid) {
                    $inline_keyboard = new InlineKeyboard([
                        ['text' => "На главную", 'callback_data' => 'menu']
                    ]);

                    Request::sendMessage([
                        'chat_id'      => $chat_id,
                        'text'         => 'Вы подписаны на рассылку. Как только появятся новые сообщения, вы сразу их получите. '.PHP_EOL.'Ваша подписка истекает: '.date('Y-m-d H:i:s', $subscription_end_timestamp),
                        'reply_markup' => $inline_keyboard
                    ]);
                }
                else {

                    $inline_keyboard = new InlineKeyboard([
                        ['text' => "Приобрести подписку", 'callback_data' => 'subscription_buy '.$newsletter_category_id],
                    ],
                    [
                        ['text' => "На главную", 'callback_data' => 'menu']
                    ]);

                    Request::sendMessage([
                        'chat_id'      => $chat_id,
                        'text'         => 'Вы можете подписаться и в автоматическом режиме получать все самые сообщения этой рассылки.',
                        'reply_markup' => $inline_keyboard
                    ]);

                }

                break;
                
            case 'subscription_buy':
                $newsletter_category_id = $command_data;

                $subscriptions = SubscriptionDB::selectSubscription();

                foreach ($subscriptions as $subscription) {
                    $inline_keyboard = new InlineKeyboard([
                        ['text' => "Оплатить", 'url' => BOT_URL.'free-kassa-form.php?user_id='.$user_id.'&chat_id='.$chat_id.'&newsletter_category_id='.$newsletter_category_id.'&subscription_id='.$subscription['id']],
                    ]);

                    Request::sendMessage([
                        'chat_id'      => $chat_id,
                        'text'         => $subscription['name'].PHP_EOL.'Цена: '.$subscription['price'],
                        'reply_markup' => $inline_keyboard
                    ]);
                }

                $inline_keyboard = new InlineKeyboard([
                    ['text' => "Получить", 'callback_data' => 'subscription_trial'],
                ]);

                Request::sendMessage([
                    'chat_id'      => $chat_id,
                    'text'         => "Пробный период на ".SUBSCRIPTION_TRIAL_DAYS." дней",
                    'reply_markup' => $inline_keyboard
                ]);


                break;

            case 'subscription_trial':   
                break;

            case 'newsletter_categories':
                $newsletter_categories = NewsletterCategoryDB::selectNewsletterCategory();

                foreach ($newsletter_categories as $newsletter_category) {
                    $images_dir_full_path = __DIR__.'/../images/';
                    $images_dir = '../images/';
                    $images = glob($images_dir_full_path.$newsletter_category['id'].'.*');
                    
                    if(count($images)) {
                        // send newsletter_category photo
                        $result = Request::sendPhoto([
                            'chat_id' => $chat_id,
                            'photo'   => Request::encodeFile($images[0]),
                        ]);
                    }

                    $text = "Название: ".$newsletter_category['name'].PHP_EOL
                        ."Описание: ".$newsletter_category['description'];

                    $inline_keyboard = new InlineKeyboard([
                        ['text' => "Выбрать", 'callback_data' => 'newsletter_category '.$newsletter_category['id']]
                    ],
                    [
                        ['text' => "Назад", 'callback_data' => 'menu']
                    ]);
            
                    Request::sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $text,
                        'reply_markup' => $inline_keyboard
                    ]);
                }
                break;  

            case 'information':
            case 'statistics':
                $fields = FieldDB::selectField(null, null, null, $command);

                foreach ($fields as $field) {
                    if($field['value'] == 'image') {
                        // field is image
                        $images_dir_full_path = __DIR__.'/../images/';
                        $images_dir = '../images/';
                        $images = glob($images_dir_full_path.'field_'.$field['id'].'.*');

                        if(count($images)) {
                            // send photo
                            $result = Request::sendPhoto([
                                'chat_id' => $chat_id,
                                'photo'   => Request::encodeFile($images[0]),
                            ]);
                        }
                    } else {
                        // field is text
                        Request::sendMessage([
                            'chat_id' => $chat_id,
                            'text' => $field['value'],
                        ]);
                    }

                   
                }

                 $inline_keyboard = new InlineKeyboard([
                    ['text' => "На главную", 'callback_data' => 'menu']
                ]);

                Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => "---",
                    'reply_markup' => $inline_keyboard
                ]);
                break;     

            case 'menu':
                $text = "Добро пожаловать !";

                $inline_keyboard = new InlineKeyboard(
                    [ ['text' => "Информация", 'callback_data' => 'information'] ],
                    [ ['text' => "Статистика", 'callback_data' => 'statistics'] ],
                    [ ['text' => "Пробный период", 'callback_data' => 'trial'] ],
                    [ ['text' => "Рассылки", 'callback_data' => 'newsletter_categories'] ] 
                );

                Request::sendMessage([
                    'chat_id' => $chat_id,
                    'text' => $text,
                    'reply_markup' => $inline_keyboard
                ]);
                break;     
            
            default:
                # code...
                break;
        }

        return $result;
    }

   
}
