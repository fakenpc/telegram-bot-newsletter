<?php
// Add you bot's API key and name
$bot_api_key  = '';
$bot_username = '';

// Define all paths for your custom commands in this array (leave as empty array if not used)
$commands_paths = [
  __DIR__ . '/Commands/',
];

// Enter your MySQL database credentials
$mysql_credentials = [
    'host'     => 'localhost',
    'user'     => 'root',
    'password' => '',
    'database' => 'examplebot',
];

// Define the URL to your hook.php file
$hook_url = 'https://your-domain/path/to/hook.php';

// path to public key of self-signed certificate
$certificate_path = 'webhook_cert.pem';

// free-kassa
$merchant_id = '';
$merchant_secret_form = '';	// secret word 1
$merchant_secret_response = '';	// scret word 2

// bot location url
$bot_url = 'http://example.com/sport-predictions-telegram-bot/';

?>