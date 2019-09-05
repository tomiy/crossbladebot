<?php
declare(strict_types=1);
/**
 * PHP version 7
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */

/**
 * The message queue is based on microtime which stops at 4 decimals by default.
 * We bump it up to 6 to have actual microseconds so we can process messages.
 */
ini_set('precision', '16');
/**
 * IRC only accepts \r\n (not \n) so on Windows systems you can't use PHP_EOL.
 * Use this constant instead to signify an EOL in messages. (handled by the socket)
 */
define('NL', "\r\n");

/**
 * Include the autoloader to be able to do oop PHP.
 */
require_once __DIR__ . '/vendor/autoload.php';

use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\Socket;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Component\Loader;

$logger = new Logger();

$socket = new Socket($logger);
$eventhandler = new EventHandler($logger);
$loader = new Loader($logger);
$client = new Client($logger, $socket, $eventhandler, $loader);

$client->serve();
