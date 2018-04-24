<?php

set_time_limit(0);
ini_set('display_errors','on');
ignore_user_abort(true);

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
    require_once __DIR__.'/config.php';
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

    $number_sended_newsletters = 0;
    $subscribers = SubscriberDB::selectActiveSubscriber(null, null, null, null, null, null, 1);

    foreach ($subscribers as $subscriber) {
    	$newsletters = NewsletterDB::selectNewsletter(null, $subscriber['newsletter_category_id']);

    	foreach ($newsletters as $newsletter) {

    		if(time() < $newsletter['disabling_timestamp'] && time() > $newsletter['sending_timestamp']) {
    			$newsletter_sended = NewsletterSendedDB::selectNewsletterSended(null, $newsletter['id'], $subscriber['id']);

    			// if newsletter dont sended to current subscriber
    			if(!count($newsletter_sended)) {
                    $images_dir_full_path = __DIR__.'/images/';
                    $images_dir = 'images/';
                    $images = glob($images_dir_full_path.'newsletter_'.$newsletter['id'].'.*');

                    if(count($images)) {
                        // send photo
                        $result = Request::sendPhoto([
                            'chat_id' => $subscriber['chat_id'],
                            'photo'   => Request::encodeFile($images[0]),
                        ]);
                    }

                    // send
    				$text = $newsletter['name'].PHP_EOL.$newsletter['description'];
					Request::sendMessage([
					    'chat_id' => $subscriber['chat_id'],
					    'text' => $text
					]);

    				// mark sended
    				NewsletterSendedDB::insertNewsletterSended($newsletter['id'], $subscriber['id']);

    				$number_sended_newsletters++;
    			}
    			// NewsletterDB::updateNewsletter(['sended' => 1], ['id' => $newsletter['id']]);
    		}
    	}

    }
    
	print date('Y-m-d H:i:s', time()). " - Sended newsletter: $number_sended_newsletters \n";

} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    echo $e->getMessage();
    // Log telegram errors
    Longman\TelegramBot\TelegramLog::error($e);
} catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
    // Catch log initialisation errors
    echo $e->getMessage();
}

?>
