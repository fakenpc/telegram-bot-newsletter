<?php require_once __DIR__.'/header.php'; ?>

		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-10 col-sm-offset-1 main">

					<?php
						$images_dir = '../images/';

						require_once __DIR__ . '/../vendor/autoload.php';
						require_once __DIR__.'/../NewsletterCategoryDB.php';
						require_once __DIR__.'/../UserDB.php';
						require_once __DIR__.'/../NewsletterDB.php';
						require_once __DIR__.'/../SubscriptionDB.php';
						require_once __DIR__.'/../SubscriberDB.php';

						use Longman\TelegramBot\DB;
						
						NewsletterCategoryDB::initializeNewsletterCategory();
						UserDB::initializeUser();
						NewsletterDB::initializeNewsletter();
						SubscriptionDB::initializeSubscription();
						SubscriberDB::initializeSubscriber();

						if(!file_exists(__DIR__.'/../config.php')) {
							die("Please rename example_config.php to config.php and try again. \n");
						} else {
							require_once __DIR__.'/../config.php';
						}

						try {
							// Create Telegram API object
							$telegram = new Longman\TelegramBot\Telegram(BOT_API_KEY, BOT_USERNAME);

							// Enable MySQL
							$telegram->enableMySql(MYSQL_CREDENTIALS);

							if(isset($_GET['remove_subscription_id'])) {
								SubscriptionDB::deleteSubscription($_GET['remove_subscription_id']);
							}

							if(isset($_GET['newsletter_category_id'])) {
								$newsletter_categories = NewsletterCategoryDB::selectNewsletterCategory($_GET['newsletter_category_id']);

								if(count($newsletter_categories)) {
									$newsletter_category = $newsletter_categories[0];

									print '
										<h2 class="sub-header">Тарифы рассылки '.$newsletter_category['name'].' [<a href="subscription-edit.php?newsletter_category_id='.$newsletter_category['id'].'">+</a>]</h2>
										<div class="table-responsive">
											<table class="table table-striped">
												<thead>
													<tr>
														<th>#</th>
														<th>Название</th>
														<th>Длителньость</th>
														<th>Цена</th>
														<th>Редактировать</th>
													</tr>
												</thead>
												<tbody>
									';


									$subscriptions = SubscriptionDB::selectSubscription(null, $newsletter_category['id']);

									foreach ($subscriptions as $subscription) {

										print '
													<tr>
														<td>'.$subscription['id'].'</td>
														<td>'.$subscription['name'].' </td>
														<td>'.(int)($subscription['duration'] / 60 / 60 / 24).' д. </td>
														<td>'.$subscription['price'].' руб.</td>
														<td>
															<a href="subscription-edit.php?newsletter_category_id='.$newsletter_category['id'].'&id='.$subscription['id'].'">Изменить</a> 
															<a href="subscriptions.php?newsletter_category_id='.$newsletter_category['id'].'&remove_subscription_id='.$subscription['id'].'">Удалить</a> 
														</td>
													</tr>
										';
									}

									print '
												</tbody>
											</table>
										</div>
									';
								}
								
								
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

					
					
				</div>
			</div>
		</div>

<?php require_once __DIR__.'/footer.php'; ?>		