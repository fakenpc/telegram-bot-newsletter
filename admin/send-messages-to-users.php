<?php require_once __DIR__.'/header.php'; ?>

		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-10 col-sm-offset-1 main">
					<h1 class="page-header">Рассылка всем пользователям</h1>

					<?php
						$images_dir = '../images/';
						$images_dir_full_path = __DIR__."/../images/";

						require_once __DIR__ . '/../vendor/autoload.php';
						require_once __DIR__.'/../ChatDB.php';

						use Longman\TelegramBot\Request;
						use Longman\TelegramBot\DB;
						
						ChatDB::initializeChat();

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

							if(isset($_POST['submit'])) {
								$chats = ChatDB::selectChat();

								foreach($chats as $chat) {
									Request::sendMessage([
									    'chat_id' => $chat['id'],
									    'text' => $_POST['message']
									]);
									
								}

								
								print "<div class='alert alert-success' role='alert'>Разосланно в ".count($chats)." чатов !</div>";
								
							}

							print '
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<a href="index.php">Назад</a>
										<form enctype="multipart/form-data" action="?" method="POST">
											<div class="form-group">
												<label for="name">Сообщение</label>
												<textarea class="form-control" rows="5" name="message" placeholder="Сообщение"></textarea>
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