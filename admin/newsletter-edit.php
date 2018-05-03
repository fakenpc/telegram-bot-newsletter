<?php require_once __DIR__.'/header.php'; ?>

		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-10 col-sm-offset-1 main">
					<h1 class="page-header"><?=isset($_GET['id']) ? 'Изменить' : 'Добавить'?> содержимое</h1>

					<?php
						$images_dir = '../images/';
						$images_dir_full_path = __DIR__."/../images/";

						require_once __DIR__ . '/../vendor/autoload.php';
						require_once __DIR__.'/../NewsletterCategoryDB.php';
						require_once __DIR__.'/../NewsletterDB.php';

						use Longman\TelegramBot\DB;
						
						NewsletterCategoryDB::initializeNewsletterCategory();
						NewsletterDB::initializeNewsletter();

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

							if(isset($_POST['submit']))
							{

								// if edit newsletter
								if(isset($_GET['id'])) {
									$newsletter_id = intval($_GET['id']);
									$result = NewsletterDB::updateNewsletter(['newsletter_category_id' => $_GET['newsletter_category_id'], 'name' => $_POST['name'], 'description' => $_POST['description'], 'sending_timestamp' => strtotime($_POST['sending_timestamp']), 'disabling_timestamp' => strtotime($_POST['disabling_timestamp'])], ['id' => $newsletter_id]);
								// if add newsletter
								} else {
									$newsletter_id = NewsletterDB::insertNewsletter($_GET['newsletter_category_id'], $_POST['name'], $_POST['description'], strtotime($_POST['sending_timestamp']), strtotime($_POST['disabling_timestamp']), 0);
								}

								if($newsletter_id) {
									print "<div class='alert alert-success' role='alert'>Содержимое прогноза успешно добавлено/обновлено !</div>";
								}
								
								// upload image
								if(count($_FILES)) {
									$target_dir = $images_dir_full_path;
									$image_file_type = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
									$target_file = $target_dir . 'newsletter_'. $newsletter_id . '.png';
									
									// Check if image file is a actual image or fake image
									@$check = getimagesize($_FILES["image"]["tmp_name"]);

									if($check !== false) {
										print "<div class='alert alert-success' role='alert'>File is an image - " . $check["mime"] . ". </div>";
										$upload_ok = 1;
									} else {
										print "<div class='alert alert-danger' role='alert'>File is not an image.</div>";
										$upload_ok = 0;
									}

									// Check file size
									if ($_FILES["image"]["size"] > 10000000) {
										print "<div class='alert alert-danger' role='alert'>Sorry, your file is too large.</div>";
										$upload_ok = 0;
									}

									// Allow certain file formats
									if($image_file_type != "jpg" && $image_file_type != "png" && $image_file_type != "jpeg" && $image_file_type != "gif" ) {
										print "<div class='alert alert-danger' role='alert'>Sorry, only JPG, JPEG, PNG & GIF files are allowed.</div>";
										$upload_ok = 0;
									}

									// Check if $upload_ok is set to 0 by an error
									if ($upload_ok == 0) {
										print "<div class='alert alert-danger' role='alert'>добавлено/изменено без изображения.</div>";
									// if everything is ok, try to upload file
									} else {
										// delete previous images
										foreach (glob($target_dir . 'newsletter_'. $newsletter_id.'.*') as $filename) {
											unlink($filename);
										}

										// resize image and convert to png
										$max_dim = 200;
								        $filename = $_FILES['image']['tmp_name'];
								        list($width, $height, $type, $attr) = getimagesize( $filename );
								        
								        if ( $width > $max_dim || $height > $max_dim ) {
								            $ratio = $width/$height;
								            if( $ratio > 1) {
								                $new_width = $max_dim;
								                $new_height = $max_dim/$ratio;
								            } else {
								                $new_width = $max_dim*$ratio;
								                $new_height = $max_dim;
								            }
								            $src = imagecreatefromstring( file_get_contents( $filename ) );
								            
								            if($src === false) {
								            	print "<div class='alert alert-danger' role='alert'>Sorry, there was an error uploading your file.</div>";
								            }

								            $dst = imagecreatetruecolor( $new_width, $new_height );
								            imagecopyresampled( $dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
								            imagedestroy( $src );
								            
								            if (imagepng( $dst, $target_file )) {
												print "<div class='alert alert-success' role='alert'>The file ". basename( $_FILES["image"]["name"]). " has been uploaded.</div>";
											} else {
												print "<div class='alert alert-danger' role='alert'>Sorry, there was an error uploading your file.</div>";
											}

								            imagedestroy( $dst );
								        }

										/*if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
											print "<div class='alert alert-success' role='alert'>The file ". basename( $_FILES["image"]["name"]). " has been uploaded.</div>";
										} else {
											print "<div class='alert alert-danger' role='alert'>Sorry, there was an error uploading your file.</div>";
										}*/
									}
								}
							}
						
							$id = '';
							$name = '';
							$description = '';
							$sending_timestamp = 0;
							$disabling_timestamp = 0;


							// edit newsletter_category
							if(isset($_GET['id'])) {
								$newsletters = NewsletterDB::selectNewsletter($_GET['id']);

								if(count($newsletters)) {
									$newsletter = $newsletters[0];
									$id = $newsletter['id'];
									$name = $newsletter['name'];;
									$description = $newsletter['description'];;
									$sending_timestamp = $newsletter['sending_timestamp'];;
									$disabling_timestamp = $newsletter['disabling_timestamp'];;
								}
								
							
							}

							print '
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<a href="newsletter.php?newsletter_category_id='.$_GET['newsletter_category_id'].'">Назад</a>
										<form enctype="multipart/form-data" action="?newsletter_category_id='.$_GET['newsletter_category_id'].'&'.($id ? 'id='.$id : '').'" method="POST">
											<div class="form-group">
												<label for="name">Название (видят все)</label>
												<textarea class="form-control" rows="5" name="name" placeholder="Название">'.$name.'</textarea>
											</div>
											<div class="form-group">
												<label for="description">Описание (видят только подписчики)</label>
												<textarea class="form-control" rows="5" name="description" placeholder="Описание">'.$description.'</textarea>
											</div>
											<div class="form-group">
												<label for="image">Изображение</label>
												<input type="file" name="image"/>
											</div>
											<div class="form-group">
												<label for="sending_timestamp">Время отправки</label>
												<input type="datetime" class="form-control" name="sending_timestamp" placeholder="Время отправки" value="'.($sending_timestamp ? date('Y-m-d H:i:s', $sending_timestamp) : date('Y-m-d H:i:s')).'">
											</div>
											<div class="form-group">
												<label for="sending_timestamp">Время потери актуальности</label>
												<input type="datetime" class="form-control" name="disabling_timestamp" placeholder="Время потери актуальности" value="'.($disabling_timestamp ? date('Y-m-d H:i:s', $disabling_timestamp) : date('Y-m-d H:i:s')).'">
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