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

							if(isset($_GET['remove_newsletter_category_id'])) {
								NewsletterCategoryDB::deleteNewsletterCategory($_GET['remove_newsletter_category_id']);
							}
		
							print '<h1 class="page-header">Рассылки [<a href="newsletter-category-edit.php">+</a>]</h1>';
							print '<div class="row placeholders">';

							$newsletter_categories = NewsletterCategoryDB::selectNewsletterCategory();

							foreach ($newsletter_categories as $newsletter_category) {
								$images = glob($images_dir.$newsletter_category['id'].'.*');
								$image_src = count($images) ? $images_dir.current($images) : $images_dir.'404.jpg';
								
								print '
									<div class="col-xs-6 col-sm-3 placeholder">
										<a href="newsletter-category-edit.php?id='.$newsletter_category['id'].'">
											<img src="'.$image_src.'" class="img-responsive" alt="Generic placeholder thumbnail">
										</a>
										<h4>'.$newsletter_category['name'].'</h4>
										<span class="text-muted">'.$newsletter_category['description'].'</span><br>
										<span>
											<a href="newsletter-category-edit.php?id='.$newsletter_category['id'].'">Изменить</a> 
											<a href="index.php?remove_newsletter_category_id='.$newsletter_category['id'].'">Удалить</a> 
											<a href="newsletter.php?newsletter_category_id='.$newsletter_category['id'].'">Содержимое ('.count(NewsletterDB::selectNewsletter(null, $newsletter_category['id'])).')</a>
											<a href="subscriptions.php?newsletter_category_id='.$newsletter_category['id'].'">Тарифы ('.count(SubscriptionDB::selectSubscription(null, $newsletter_category['id'])).')</a> 
										</span>
									</div>
								';
							}

							print '</div>';

							print '<h2 class="sub-header">Список пользователей</h2>
								<div class="table-responsive">
									<table class="table table-striped">
										<thead>
											<tr>
												<th>#</th>
												<th>Имя</th>
												<th>Ник</th>
												<th>Первое посещение</th>
												<th>Последние посещение</th>
												<th>Подписки</th>
											</tr>
										</thead>
										<tbody>
							';


							$users = UserDB::selectUser();

							foreach ($users as $user) {
								// newsletter_category subscribers with user_id
								$subscribers = SubscriberDB::selectSubscriber(null, null, null, $user['id']);

								print '
											<tr>
												<td>'.$user['id'].'</td>
												<td>'.$user['first_name'].' '.$user['last_name'].' </td>
												<td>'.$user['username'].'</td>
												<td>'.$user['created_at'].'</td>
												<td>'.$user['updated_at'].'</td>
												<td>
													
													<button class="btn btn-default" data-toggle="collapse" data-target="#hide-subscriber-'.$user['id'].'">'.count($subscribers).' шт, показать</button>
													<div id="hide-subscriber-'.$user['id'].'" class="collapse">
								';

								if(count($subscribers)) {
									foreach ($subscribers as $subscriber) {
										$newsletter_categories = NewsletterCategoryDB::selectNewsletterCategory($subscriber['newsletter_category_id']);
										$subscriptions = SubscriptionDB::selectSubscription($subscriber['subscription_id']);

										print '(';
										
										if(count($newsletter_categories)) {
											$newsletter_category = $newsletter_categories[0];
											print $newsletter_category['name'].', ';
										}
										
										if(count($subscriptions)) {
											$subscription = $subscriptions[0];
											print $subscription['name'].', ';
										}

										print $subscriber['paid'] ? 'оплачена, ' : 'не оплачена, ';
										print $subscriber['end_timestamp'] > time() ? 'действительна, ' : 'окончилась, ';

										print ')';
										print '<br>';

									}
									
								} else {
									print 'Нет подписок';
								}
								

								print '						
													</div>
												</td>
											</tr>
								';
							}

							print '
										</tbody>
									</table>
								</div>
							';

							
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