<?php
/**
 * PHP version 7
 * 
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */

namespace CrossbladeBot\Core;

use CrossbladeBot\Traits\Configurable;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Chat\Channel;
use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\Socket;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Component\Loader;
use CrossbladeBot\Service\Queue;
use CrossbladeBot\Traits\RateLimit;

/**
 * The bot client.
 * Handles the dispatching of events and messages to channels,
 * and sends and recieves data to and from the socket.
 * 
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class Client extends Queue
{
    use Configurable;
    use RateLimit;

    /**
     * The logger object.
     *
     * @var Logger
     */
    private $_logger;
    /**
     * The socket object handling the stream.
     *
     * @var Socket
     */
    private $_socket;
    /**
     * The event handler.
     *
     * @var EventHandler
     */
    private $_eventHandler;
    /**
     * The components' loader.
     *
     * @var Loader
     */
    private $_loader;

    /**
     * The name of the bot. Retrieved during runtime.
     *
     * @var string
     */
    private $_name;
    /**
     * The channels array holding Channel objects.
     *
     * @var array
     */
    private $_channels;

    /**
     * Instantiate a new client.
     *
     * @param Logger       $logger       The logger object.
     * @param Socket       $socket       The socket object holding the socket stream.
     * @param EventHandler $eventHandler The handler holding the component events.
     * @param Loader       $loader       The loader holding the components.
     */
    public function __construct(
        Logger $logger,
        Socket $socket,
        EventHandler $eventHandler,
        Loader $loader
    ) {
        $this->loadConfig();
        $this->initRate(20, 30);

        $this->_logger = $logger;
        $this->_socket = $socket;
        $this->_eventHandler = $eventHandler;
        $this->_loader = $loader;

        $this->_loader->register($eventHandler, $this);

        $this->_channels = [];
    }

    /**
     * Connect to the socket stream and to the irc and request capabilities.
     *
     * @return void
     */
    public function connect(): void
    {
        $this->_socket->connect();

        $this->enqueue(
            [
            'CAP REQ :twitch.tv/tags twitch.tv/commands twitch.tv/membership',
            'PASS ' . $this->_config->password,
            'NICK ' . $this->_config->name,
            'JOIN #' . $this->_config->channel
            ]
        );
    }

    /**
     * Connect to the irc and loop over the socket messages,
     * dispatching events and messages to channel as necessary.
     *
     * @return void
     */
    public function serve(): void
    {
        $this->connect();
        $processed = $this->processQueue([$this, 'sendToSocket']);

        $connected = true;

        $prefix = $this->_config->prefix;
        $prefixLen = strlen($prefix);

        $lastPing = time();

        while ($connected) {
            $message = $channel = null;

            $cost = microtime(true);
            if ((time() - $lastPing) > 300 or $this->_socket === false) {
                $this->_logger->info('Restarting connection');
                $this->connect();
                $lastPing = time();
            }

            $data = $this->_socket->getNext();

            if ($data) {
                $message = new Message($data);

                if (empty($message->getFrom())) {
                    switch ($message->getType()) {
                    case 'PING':
                            $lastPing = time();
                            $this->enqueue(['PONG :' . $message->getParam(0)]);
                            $this->_eventHandler->trigger('pong');
                        break;
                    case 'PONG':
                            $latency = time() - $lastPing;
                            $this->_logger->info('Current latency: ' . $latency);
                        break;
                    default:
                            $this->_logger->warning(
                                'Could not parse message: ' .
                                $message->getRaw()
                            );
                        break;
                    }
                } elseif ($message->getFrom() === 'tmi.twitch.tv') {
                    switch ($message->getType()) {
                    case '002':
                    case '003':
                    case '004':
                    case '375':
                    case '376':
                    case 'CAP':
                        break;
                    case '001':
                            $this->_name = $message->getParam(0);
                        break;
                    case '372':
                            $this->_logger->debug('Client connected');
                            $this->_eventHandler->trigger('connect');
                        break;
                    case 'NOTICE':
                        foreach (
                            [
                            'Login unsuccessful',
                            'Login authentication failed',
                            'Error logging in',
                            'Improperly formatted auth',
                            'Invalid NICK'
                            ] as $needle) {
                            if (strpos($message->getMessage(), $needle) !== false) {
                                $this->_logger->error('Potential auth failure');
                                $connected = false;
                                break;
                            }
                        }
                        break;
                    case 'USERNOTICE':
                        switch ($message->getId()) {
                        case "resub":
                            break;
                        case "sub":
                            break;
                        case "subgift":
                            break;
                        case "anonsubgift":
                            break;
                        case "submysterygift":
                            break;
                        case "anonsubmysterygift":
                            break;
                        case "primepaidupgrade":
                            break;
                        case "giftpaidupgrade":
                            break;
                        case "anongiftpaidupgrade":
                            break;
                        case "raid":
                            break;
                        }
                        break;
                    case 'HOSTTARGET':
                        break;
                    case 'CLEARCHAT':
                        break;
                    case 'CLEARMSG':
                        break;
                    case 'RECONNECT':
                        break;
                    case 'USERSTATE':
                        $channel = $this->_getChannel($message->getParam(0));
                        if ($channel->isParted() === true) {
                            unset($this->_channels[$channel->getName()]);
                            $this->_logger->debug(
                                'Removed channel ' .
                                $channel->getName() .
                                ' from client'
                            );
                        } else {
                            $channel->userState($message);
                        }
                        break;
                    case 'GLOBALUSERSTATE':
                        break;
                    case 'ROOMSTATE':
                        break;
                    case 'SERVERCHANGE':
                        break;
                    default:
                        $this->_logger->warning(
                            'Could not parse message: ' .
                            $message->getRaw()
                        );
                        break;
                    }
                } elseif ($message->getFrom() === 'jtv') {
                    switch ($message->getType()) {
                    case 'MODE':
                        break;
                    default:
                        $this->_logger->warning(
                            'Could not parse message: ' .
                            $message->getRaw()
                        );
                        break;
                    }
                } else {
                    switch ($message->getType()) {
                    case '353':
                        break;
                    case '366':
                        break;
                    case 'JOIN':
                        if ($this->_isMe($message->getUser())) {
                            $channel = new Channel($this->_logger, $message);
                            $this->_channels[$channel->getName()] = $channel;
                            $this->_logger->debug(
                                'Added channel ' .
                                $channel->getName() .
                                ' to client'
                            );
                            $this->_eventHandler->trigger('join', $channel);
                        } else {
                            //another user joined
                        }
                        break;
                    case 'PART':
                            $this->_getChannel($message->getChannel())->part();
                            $this->_eventHandler->trigger('part', $channel);
                        break;
                    case 'WHISPER':
                        break;
                    case 'PRIVMSG':
                        if ($this->_isMe($message->getUser())) {
                            break;
                        }
                        $channel = $this->_getChannel($message->getChannel());
                        if (substr(
                            $message->getMessage(), 0, $prefixLen
                        ) === $prefix
                        ) {
                            $messagearr = explode(' ', $message->getMessage());
                            $message->setCommand(
                                substr(array_shift($messagearr), 1)
                            );
                            $this->_eventHandler->trigger(
                                'command',
                                $message,
                                $channel
                            );
                            break;
                        }
                        $this->_eventHandler->trigger(
                            'message',
                            $message,
                            $channel
                        );
                        break;
                    default:
                        $this->_logger->warning(
                            'Could not parse message: ' .
                            $message->getRaw()
                        );
                        break;
                    }
                }
            }
            $this->_pollChannels();
            $processed += $this->processQueue([$this, 'sendToSocket']);
            if ($data || $processed > 0) {
                if ($processed > 0) {
                    $this->_logger->debug(
                        'Processed ' .
                        $processed .
                        ' messages from the client queue'
                    );
                    $processed = 0;
                }
                print_r(sprintf('Cost: %fms' . NL, (microtime(true) - $cost) * 1E3));
            }
        }
    }

    /**
     * Send the messages extracted from the queue to the socket.
     *
     * @param array $messages The messages extracted from the queue
     * 
     * @return void
     */
    protected function sendToSocket(array $messages): void
    {
        foreach ($messages as $message) {
            $this->_logger->debug(
                'Sending message: "' .
                trim($message) .
                '" at time ' .
                time()
            );
            $this->_socket->send($message);
        }
    }

    /**
     * Process channel queues and queue the extracted messages into the client.
     *
     * @return int the number of processed messages
     */
    private function _pollChannels(): int
    {
        $processed = 0;
        foreach ($this->_channels as $channel) {
            $processed += $channel->processQueue([$this, 'enqueue']);
        }

        if ($processed > 0) {
            $this->_logger->debug(
                'Processed ' .
                $processed .
                ' messages from the channel queues'
            );
        }
        return $processed;
    }

    /**
     * Queue a message into the socket.
     *
     * @param string $message The message to queue.
     * 
     * @return void
     */
    public function send(string $message): void
    {
        $this->enqueue([$message]);
    }

    /**
     * Check if the user is the client.
     *
     * @param string $user The username to check.
     * 
     * @return boolean Whether the user is the client.
     */
    private function _isMe(string $user): bool
    {
        return $user === $this->_name;
    }

    /**
     * Get the channel object from the channel name.
     *
     * @param string $name The channel name.
     * 
     * @return Channel The channel object.
     */
    private function _getChannel(string $name): Channel
    {
        if (isset($this->_channels[$name])) {
            return $this->_channels[$name];
        }
    }

    /**
     * Get the name of the client.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->_name;
    }

    /**
     * Get the channel array.
     *
     * @return array
     */
    public function getChannels(): array
    {
        return $this->_channels;
    }
}
