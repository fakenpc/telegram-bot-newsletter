<?php require_once __DIR__.'/header.php'; ?>

		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-10 col-sm-offset-1 main">
					<h1 class="page-header"><?=isset($_GET['id']) ? 'Изменить' : 'Добавить'?> подписку</h1>

					<?php
						$images_dir = '../images/';
						$images_dir_full_path = __DIR__."/../images/";

						require_once __DIR__ . '/../vendor/autoload.php';
						require_once __DIR__.'/../NewsletterCategoryDB.php';
						require_once __DIR__.'/../SubscriptionDB.php';

						use Longman\TelegramBot\DB;
						
						NewsletterCategoryDB::initializeNewsletterCategory();
						SubscriptionDB::initializeSubscription();

						if(!file_exists(__DIR__.'/../config.php')) {
						    die("Please rename example_config.php to config.php and try again. \n");
						} else {
						    require_once __DIR__.'/../config.php';
						}

						try {
						    // Create Telegram API object
						    $telegram = new Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

						    // Enable MySQL
    						$telegram->enableMySql($mysql_credentials);

							if(isset($_POST['submit']))
							{

								// if edit subscription
								if(isset($_GET['id'])) {
									$subscription_id = intval($_GET['id']);
									$result = SubscriptionDB::updateSubscription(['name' => $_POST['name'], 'duration' => ($_POST['duration'] * 60 * 60 * 24), 'price' => $_POST['price'] ], ['id' => $subscription_id]);
								// if add subscription
								} else {
									$result = SubscriptionDB::insertSubscription($_POST['name'], ($_POST['duration'] * 60 * 60 * 24), $_POST['price']);
								}

								if($result) {
									print "<div class='alert alert-success' role='alert'>Подписка успешно добавлена/обновлена !</div>";
								}
								

							}
						
							$id = '';
							$name = '';
							$duration = '';
							$price = 0;


							// edit newsletter_category
							if(isset($_GET['id'])) {
								$subscriptions = SubscriptionDB::selectSubscription($_GET['id']);

								if(count($subscriptions)) {
									$subscription = $subscriptions[0];
									$id = $subscription['id'];
									$name = $subscription['name'];
									$duration = $subscription['duration'];;
									$price = $subscription['price'];;
								}
								
							
							}

							print '
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<a href="index.php">Назад</a>
										<form enctype="multipart/form-data" action="?'.($id ? 'id='.$id : '').'" method="POST">
											<div class="form-group">
												<label for="name">Название</label>
												<textarea class="form-control" rows="5" name="name" placeholder="Имя">'.$name.'</textarea>
											</div>
											<div class="form-group">
												<label for="duration">Длительность (дней)</label>
												<textarea class="form-control" rows="5" name="duration" placeholder="Длительность">'.(int)($duration / 60 / 60 / 24).'</textarea>
											</div>
											<div class="form-group">
												<label for="price">Цена</label>
												<input type="datetime" class="form-control" name="price" placeholder="Цена" value="'.$price.'">
											</div>
											<button type="submit" name="submit" class="btn btn-primary">Submit</button>
										</form>
									</div>
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