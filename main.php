<?php
/**
 * The message queue is based on microtime which stops at 4 decimals with a default PHP install.
 * We bump it up to 6 to have actual microseconds so we can process messages as quickly as we can.
 */
ini_set('precision', 16);

/**
 * Include the autoloader to be able to do oop PHP.
 */
include_once 'autoloader.php';

use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\Socket;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Component\Loader;

$logger = new Logger();

$socket = new Socket($logger);
$eventhandler = new EventHandler($logger);
$loader = new Loader($logger);
$client = new Client($logger, $socket, $eventhandler, $loader);

$client->serve();
