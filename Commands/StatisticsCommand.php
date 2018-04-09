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

require_once __DIR__.'/../SubscriberDB.php';
require_once __DIR__.'/../NewsletterCategoryDB.php';
require_once __DIR__.'/../FieldDB.php';

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use SubscriberDB;
use NewsletterCategoryDB;
use FieldDB;

/**
 * Statistics command
 *
 * Gets executed when a user first statistics using the bot.
 */
class StatisticsCommand extends SystemCommand
{
	/**
	 * @var string
	 */
	protected $name = 'statistics';

	/**
	 * @var string
	 */
	protected $description = 'Statistics command';

	/**
	 * @var string
	 */
	protected $usage = '/statistics';

	/**
	 * @var string
	 */
	protected $version = '1.0.0';

	/**
	 * @var bool
	 */
	protected $private_only = true;
	
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
		SubscriberDB::initializeSubscriber();
		NewsletterCategoryDB::initializeNewsletterCategory();
		FieldDB::initializeField();

		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();
		
		$fields = FieldDB::selectField(null, null, null, $this->name);

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

        /*$inline_keyboard = new InlineKeyboard([
            ['text' => "\xF0\x9F\x94\x99 На главную", 'callback_data' => 'menu']
        ]);*/

        Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => "---",
            //'reply_markup' => $inline_keyboard
        ]);

		return true;
	}
}
