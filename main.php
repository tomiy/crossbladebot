<?php
include_once 'autoloader.php';

use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\Socket;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Component\Loader;

$logger = new Logger();

$socket = new Socket($logger);
$eventhandler = new EventHandler($logger);
$loader = new Loader();
$client = new Client($logger, $socket, $eventhandler, $loader);

$client->serve();