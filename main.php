<?php
include_once 'autoloader.php';

use CrossbladeBotV2\Debug\Logger;
use CrossbladeBotV2\Core\Socket;
use CrossbladeBot\Core\Client;

Logger::init();

$socket = new Socket();
$client = new Client($socket);

$client->serve();