<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__.'/NewsletterCategoryDB.php';
require_once __DIR__.'/UserDB.php';
require_once __DIR__.'/NewsletterDB.php';
require_once __DIR__.'/SubscriptionDB.php';
require_once __DIR__.'/SubscriberDB.php';
require_once __DIR__.'/NewsletterSendedDB.php';
use Longman\TelegramBot\Request;
use Longman\TelegramBot\DB;
NewsletterCategoryDB::initializeNewsletterCategory();
UserDB::initializeUser();
NewsletterDB::initializeNewsletter();
SubscriptionDB::initializeSubscription();
SubscriberDB::initializeSubscriber();
NewsletterSendedDB::initializeNewsletterSended();

if(!file_exists('config.php')) {
    die("Please rename example_config.php to config.php and try again. \n");
} else {
    require_once 'config.php';
}

try {
    // Create Telegram API object
    $telegram = new Longman\TelegramBot\Telegram(BOT_API_KEY, BOT_USERNAME);
    // Add commands paths containing your custom commands
    $telegram->addCommandsPaths(BOT_COMMANDS_PATH);
    $telegram->enableLimiter();
    // Enable MySQL
    $telegram->enableMySql(MYSQL_CREDENTIALS);

    if(!DB::isDbConnected()) {
    	print date('Y-m-d H:i:s', time()). " - Can't connect to mysql database. \n";
    }

    $subscriptions = SubscriptionDB::selectSubscription($_GET['subscription_id']);
    

	if(count($subscriptions)) {
		$subscription = $subscriptions[0];
		$newsletter_categories = NewsletterCategoryDB::selectNewsletterCategory($subscription['newsletter_category_id']);
		
		if(count($newsletter_categories)) {
			$newsletter_category = $newsletter_categories[0];
			$subscriber_id = SubscriberDB::insertSubscriber($newsletter_category['id'], $_GET['subscription_id'], $_GET['user_id'], $_GET['chat_id'], time(), time() + $subscription['duration'], 0);

			$hash = md5(MERCHANT_ID.":".$subscription['price'].":".MERCHANT_SECRET_FORM.":".$subscriber_id);
			
			print '
			<form method=GET action="http://www.free-kassa.ru/merchant/cash.php">
			    <input type="hidden" name="m" value="'.MERCHANT_ID.'">
			    <input type="hidden" name="oa" value="'.$subscription['price'].'">
			    <input type="hidden" name="s" value="'.$hash.'">
			    <input type="hidden" name="o" value="'.$subscriber_id.'">
			    <input type="submit" value="Оплатить">
			</form>
			';
		}

		
	} else {
		print 'Такой подписки не существует.';
	}
	

} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    echo $e->getMessage();
    // Log telegram errors
    Longman\TelegramBot\TelegramLog::error($e);
} catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
    // Catch log initialisation errors
    echo $e->getMessage();
}

?>
