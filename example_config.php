<?php
// Add you bot's API key and name
const BOT_API_KEY  = '';
const BOT_USERNAME = '';

// Define all paths for your custom commands in this array (leave as empty array if not used)
const BOT_COMMANDS_PATH = [
  __DIR__ . '/Commands/',
];

// Enter your MySQL database credentials
const MYSQL_CREDENTIALS = [
    'host'     => 'localhost',
    'user'     => 'root',
    'password' => '',
    'database' => 'examplebot',
];

// Define the URL to your hook.php file
const BOT_HOOK_URL = 'https://your-domain/path/to/hook.php';

// path to public key of self-signed certificate
const CERTIFICATE_PATH = 'webhook_cert.pem';

// free-kassa
const MERCHANT_ID = '';
const MERCHANT_SECRET_FORM = '';	// secret word 1
const MERCHANT_SECRET_RESPONSE = '';	// scret word 2

// bot location url
const BOT_URL = 'http://example.com/sport-predictions-telegram-bot/';

?>