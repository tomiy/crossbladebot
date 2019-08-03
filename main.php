<?php
include_once 'autoloader.php';

use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\Socket;
use CrossbladeBot\Core\Client;

$logger = new Logger();

$socket = new Socket($logger);
$client = new Client($logger, $socket);

$client->serve();