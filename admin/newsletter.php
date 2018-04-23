<?php require_once __DIR__.'/header.php'; ?>

		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-10 col-sm-offset-1 main">
					<h1 class="page-header">Содержимое рассылки </h1>

					<?php
						$images_dir = '../images/';
						$images_dir_full_path = __DIR__."/../images/";

						require_once __DIR__ . '/../vendor/autoload.php';
						require_once __DIR__.'/../NewsletterCategoryDB.php';
						require_once __DIR__.'/../NewsletterDB.php';
						require_once __DIR__.'/../SubscriptionDB.php';

						use Longman\TelegramBot\DB;
						
						NewsletterCategoryDB::initializeNewsletterCategory();
						NewsletterDB::initializeNewsletter();
						SubscriptionDB::initializeSubscription();

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

    						if(isset($_GET['remove_newsletter_id'])) {
    							NewsletterDB::deleteNewsletter($_GET['remove_newsletter_id']);
    						}

    						@$newsletter_category_id = intval($_GET['newsletter_category_id']);

							$newsletter_categories = NewsletterCategoryDB::selectNewsletterCategory($newsletter_category_id);

							if(count($newsletter_categories)) {
								$newsletter_category = $newsletter_categories[0];

								$images = glob($images_dir.$newsletter_category['id'].'.*');
								$image_src = count($images) ? $images_dir.current($images).'?cache-off='.time() : $images_dir.'404.jpg';

								print '
								<div class="row placeholders">
									<div class="col-xs-12 col-sm-6 placeholder">
										<a href="newsletter-category-edit.php?id='.$newsletter_category['id'].'">
											<img src="'.$image_src.'" class="img-responsive" alt="Generic placeholder thumbnail">
										</a>
										<h4>'.$newsletter_category['name'].'</h4>
										<span class="text-muted">'.$newsletter_category['description'].'</span>
									</div>
								</div>
								';

								print '
								<h2 class="sub-header">Содержимое</h2>
								<a href="newsletter-edit.php?newsletter_category_id='.$newsletter_category['id'].'">Добавить</a>
								<div class="table-responsive">
									<table class="table table-striped">
										<thead>
											<tr>
												<th>#</th>
												<th>Название (видят все)</th>
												<th>Описание (видят только подписчики)</th>
												<th>Время отправки </th>
												<th>Время потери актуальности</th>
												<th>Редактировать</th>
											</tr>
										</thead>
										<tbody>
								';
								$newsletters = NewsletterDB::selectNewsletter(null, $newsletter_category_id);

								foreach ($newsletters as $newsletter) {
									$image_filename = current(glob($images_dir.'newsletter_'.$newsletter['id'].'.*'));
									$image_location = $images_dir.$image_filename;

									print '
										<tr>
											<td>'.$newsletter['id'].'</td>
											<td>
												<pre>'.$newsletter['name'].'</pre>
												'. ($image_filename ? '
													<button class="btn btn-default" data-toggle="collapse" data-target="#hide-newsletter-'.$newsletter['id'].'-image">Показать изображение</button>
													<div id="hide-newsletter-'.$newsletter['id'].'-image" class="collapse">
														<img src="'.$image_location.'" class="img-responsive" alt="Generic placeholder thumbnail">
													</div>
												' : '') .'
												
											</td>
											<td><pre>'.$newsletter['description'].'</pre></td>
											<td>'.date('Y-m-d H:i:s', $newsletter['sending_timestamp']).'</td>
											<td>'.date('Y-m-d H:i:s', $newsletter['disabling_timestamp']).'</td>
											<td>
												<a href="newsletter-edit.php?id='.$newsletter['id'].'&newsletter_category_id='.$newsletter_category['id'].'">Изменить</a> 
												<a href="newsletter.php?remove_newsletter_id='.$newsletter['id'].'&newsletter_category_id='.$newsletter_category['id'].'">Удалить</a> 
											</td>
										</tr>

										
									';
								}

								print '
								</div>	
								';

							} else {
								print 'Рассылка не найдена не найдена';
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