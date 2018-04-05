<?php require_once __DIR__.'/header.php'; ?>

		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-10 col-sm-offset-1 main">
					<h1 class="page-header">Редактировать содержимое кнопки информация</h1>

					<?php

						$images_dir = '../images/';
						$images_dir_full_path = __DIR__."/../images/";

						require_once __DIR__ . '/../vendor/autoload.php';
						require_once __DIR__.'/../FieldDB.php';

						use Longman\TelegramBot\DB;
						
						FieldDB::initializeField();

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
								// delete fields
								if(isset($_POST['remove-field-ids'])) {
									foreach ($_POST['remove-field-ids'] as $field_id) {
										FieldDB::deleteField($field_id);
									}
								}

								foreach ($_POST['field-ids'] as $order => $field_id) {
									$field_value = $_POST['field-values'][$order];
									$field_type = 'information';
									$is_uploaded_image = !empty($_FILES['field-images']['tmp_name'][$order]);

									if ($field_id == '-1') {
										// new insert
										$field_id = FieldDB::insertField(null, $field_value, $field_type, $order);
									} else {
										// update
										FieldDB::updateField(['value' => $field_value, 'type' => $field_type, 'order' => $order], ['id' => $field_id]);
									}

									if($field_id) {
										print "<div class='alert alert-success' role='alert'>Успешно добавлено/обновлено !</div>";
									}

									if($is_uploaded_image) {
										$target_dir = $images_dir_full_path;
										$image_file_type = strtolower(pathinfo($_FILES['field-images']["name"][$order], PATHINFO_EXTENSION));
										$target_file = $target_dir . 'field_'. $field_id . '.png';
										
										// Check if image file is a actual image or fake image
										@$check = getimagesize($_FILES['field-images']["tmp_name"][$order]);

										if($check !== false) {
											//print "<div class='alert alert-success' role='alert'>File is an image - " . $check["mime"] . ". </div>";
											$upload_ok = 1;
										} else {
											print "<div class='alert alert-danger' role='alert'>File is not an image.</div>";
											$upload_ok = 0;
										}

										// Check file size
										if ($_FILES['field-images']["size"][$order] > 10000000) {
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
											// print "<div class='alert alert-danger' role='alert'>Добавлено/изменено без изображения.</div>";
										// if everything is ok, try to upload file
										} else {
											// delete previous images
											foreach (glob($target_dir . 'field_'. $field_id . '.*') as $filename) {
												unlink($filename);
											}

											// resize image and convert to png
											$max_dim = 200;
									        $filename = $_FILES['field-images']['tmp_name'][$order];
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
													print "<div class='alert alert-success' role='alert'>The file ". basename( $_FILES['field-images']["name"][$order]). " has been uploaded.</div>";
												} else {
													print "<div class='alert alert-danger' role='alert'>Sorry, there was an error uploading your file.</div>";
												}

									            imagedestroy( $dst );
									        } else {
									        	print "<div class='alert alert-danger' role='alert'>Изображение слишком маленькое ($width x $height). Его размеры должны превышать $max_dim x $max_dim </div>";
									        }

											/*if (move_uploaded_file($_FILES['field-images']["tmp_name"][$order], $target_file)) {
												print "<div class='alert alert-success' role='alert'>The file ". basename( $_FILES['field-images']["name"][$order]). " has been uploaded.</div>";
											} else {
												print "<div class='alert alert-danger' role='alert'>Sorry, there was an error uploading your file.</div>";
											}*/
										}

									}

								}

							}
							
							print '
								<div class="row">
									<div class="col-xs-12 col-sm-6">
										<form enctype="multipart/form-data" action="?" method="POST">
											<div id="sortable">
							';

							$fields = FieldDB::selectField(null, null, null, 'information');

							foreach ($fields as $field) {
								if($field['value'] == 'image') {
									print '					
												<div class="form-group movable-element" data-field-id="'.$field['id'].'">
													<label>
														<span class="glyphicon glyphicon-move" aria-hidden="true"></span> Изображение
													</label>
													<span class="glyphicon glyphicon-remove pull-right remove-element"></span>

													<input type="hidden" name="field-ids[]" value="'.$field['id'].'">
													<input type="hidden" name="field-values[]" value="image">
													<input type="file" class="form-control" name="field-images[]"/>
													<br><img class="img-responsive" src="'.$images_dir.current(glob($images_dir.'field_'.$field['id'].'.*')).'">
												</div>
											
									';
								} else {
									print '				
												<div class="form-group movable-element" data-field-id="'.$field['id'].'">
													<label>
														<span class="glyphicon glyphicon-move" aria-hidden="true"></span> 
														Текст 
													</label>
													<span class="glyphicon glyphicon-remove pull-right remove-element"></span>
													<input type="hidden" name="field-ids[]" value="'.$field['id'].'">
													<textarea class="form-control" rows="5" name="field-values[]" placeholder="Текст">'.$field['value'].'</textarea>
													<!-- do not remove block below -->
													<input class="hidden" type="file" name="field-images[]"/> 
												</div>
									';
								}
							}

							print '
											</div>
											<button type="submit" name="submit" class="btn btn-primary">Сохранить</button>
											<button type="button" class="btn btn-default add-element" data-source="#text-element-source" data-destination="#sortable">
												<span class="glyphicon glyphicon-text-size" aria-hidden="true"></span> Добавить текст
											</button>
											<button type="button" class="btn btn-default add-element" data-source="#image-element-source" data-destination="#sortable">
												<span class="glyphicon glyphicon-picture" aria-hidden="true"></span> Добавить изображение
											</button>
										</form>
									</div>
								</div>
								
								<!-- шаблон блока текста-->
								<div class="form-group hidden movable-element" id="text-element-source" data-field-id="-1">
									<label><span class="glyphicon glyphicon-move" aria-hidden="true"></span> Текст</label>
									<span class="glyphicon glyphicon-remove pull-right remove-element"></span>
									<input type="hidden" name="field-ids[]" value="-1">
									<textarea class="form-control" rows="5" name="field-values[]" placeholder="Текст"></textarea>
									<!-- do not remove block below -->
									<input class="hidden" type="file" name="field-images[]"/> 
								</div>

								<!-- шаблон блока изображения-->
								<div class="form-group hidden movable-element" id="image-element-source"  data-field-id="-1">
									<label><span class="glyphicon glyphicon-move" aria-hidden="true"></span> Изображение</label>
									<span class="glyphicon glyphicon-remove pull-right remove-element"></span>
									<input type="hidden" name="field-ids[]" value="-1">
									<input type="hidden" name="field-values[]" value="image">
									<input type="file" name="field-images[]"/>
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