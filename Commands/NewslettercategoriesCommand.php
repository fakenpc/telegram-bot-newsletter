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
require_once __DIR__.'/../SubscriptionDB.php';

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use SubscriberDB;
use NewsletterCategoryDB;
use SubscriptionDB;

/**
 * Newslettercategories command
 *
 * Gets executed when a user first newslettercategories using the bot.
 */
class NewslettercategoriesCommand extends SystemCommand
{
	/**
	 * @var string
	 */
	protected $name = 'newslettercategories';

	/**
	 * @var string
	 */
	protected $description = 'Newslettercategories command';

	/**
	 * @var string
	 */
	protected $usage = '/newslettercategories';

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
		SubscriptionDB::initializeSubscription();
		NewsletterCategoryDB::initializeNewsletterCategory();
		
		$message = $this->getMessage();
		$user_id = $message->getFrom()->getId();
		$chat_id = $message->getChat()->getId();
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

			$buttons = [];
			$subscriptions = SubscriptionDB::selectSubscription(null, $newsletter_category['id']);
			
			foreach ($subscriptions as $subscription) {
				$buttons[] = [['text' => 'Подписаться ('.($subscription['duration'] / 60 / 60 / 24).' дней за '.$subscription['price'].' руб)', 
						'url' => BOT_URL.'free-kassa-form.php?user_id='.$user_id.'&chat_id='.$chat_id.'&subscription_id='.$subscription['id']]];
			}

			$buttons[] = [['text' => "Пробный период (".SUBSCRIPTION_TRIAL_DAYS." дней)", 'callback_data' => 'subscription_trial '.$newsletter_category['id']]];
			$buttons[] = [['text' => "Выбрать", 'callback_data' => 'newsletter_category '.$newsletter_category['id']]];

			$inline_keyboard = new InlineKeyboard(['inline_keyboard' => $buttons]);
	
			Request::sendMessage([
				'chat_id' => $chat_id,
				'text' => $text,
				'reply_markup' => $inline_keyboard
			]);
		}

		return true;
	}
}
