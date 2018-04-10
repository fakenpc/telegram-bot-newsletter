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

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use SubscriberDB;
use NewsletterCategoryDB;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class StartCommand extends SystemCommand
{
	/**
	 * @var string
	 */
	protected $name = 'start';

	/**
	 * @var string
	 */
	protected $description = 'Start command';

	/**
	 * @var string
	 */
	protected $usage = '/start';

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

		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();

        $text = "Доброго времени суток !";
		
        $keyboard = new Keyboard([
            'keyboard' => [ 
                [ ['text' => "\xE2\x9D\x94 Информация"], ['text' => "\xE2\x9D\x95 Статистика"] ], 
                ['text' => "\xE2\x9C\x89 Рассылки"], 
            ],
            'resize_keyboard' => true
        ]);

        Request::sendMessage([
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => $keyboard
        ]);

		return true;
	}
}
