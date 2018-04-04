<?php

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
    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);
    // Add commands paths containing your custom commands
    $telegram->addCommandsPaths($commands_paths);
    $telegram->enableLimiter();
    // Enable MySQL
    $telegram->enableMySql($mysql_credentials);

    if(!DB::isDbConnected()) {
    	print date('Y-m-d H:i:s', time()). " - Can't connect to mysql database. \n";
    }

	
	if (!in_array(getIP(), array('136.243.38.147', '136.243.38.149', '136.243.38.150', '136.243.38.151', '136.243.38.189', '88.198.88.98'))) {
		die("hacking attempt!");
	}

	$sign = md5($merchant_id.':'.$_REQUEST['AMOUNT'].':'.$merchant_secret_response.':'.$_REQUEST['MERCHANT_ORDER_ID']);

	if ($sign != $_REQUEST['SIGN']) {
		die('wrong sign');
	}

	//Так же, рекомендуется добавить проверку на сумму платежа и не была ли эта заявка уже оплачена или отменена
	//Оплата прошла успешно, можно проводить операцию.

	$subscribers = SubscriberDB::selectSubscriber($_REQUEST['MERCHANT_ORDER_ID']);

	if(count($subscribers)) {
		$subscriber = $subscribers[0];
		SubscriberDB::updateSubscriber(['paid' => 1], ['id' => $_REQUEST['MERCHANT_ORDER_ID']]);
		Request::sendMessage([
            'chat_id'      => $subscriber['chat_id'],
            'text'         => 'Вы успешно приобрели подписку! '
        ]);
		print 'YES'; 
	} else {
		print 'wrong subscriber_id';
	}

	


} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    echo $e->getMessage();
    // Log telegram errors
    Longman\TelegramBot\TelegramLog::error($e);
} catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
    // Catch log initialisation errors
    echo $e->getMessage();
}

function getIP() {
	if(isset($_SERVER['HTTP_X_REAL_IP'])) return $_SERVER['HTTP_X_REAL_IP'];
	return $_SERVER['REMOTE_ADDR'];
}

?>
