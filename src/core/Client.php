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

namespace CrossbladeBot\Core;

use CrossbladeBot\Traits\RateLimit;
use CrossbladeBot\Traits\Configurable;
use CrossbladeBot\Service\Queue;
use CrossbladeBot\Service\Processor;
use CrossbladeBot\Debug\Logger;
use CrossbladeBot\Core\Socket;
use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Component\Loader;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Chat\Channel;

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
     * The processor object to process the messages.
     *
     * @var Processor
     */
    private $_processor;

    /**
     * The last unix time in seconds when the IRC sent a PING.
     *
     * @var int
     */
    private $_lastPing;
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
        Logger $logger, Socket $socket, EventHandler $eventHandler, Loader $loader
    ) {
        $this->loadConfig();
        $this->initRate(20, 30);

        $this->_logger = $logger;
        $this->_socket = $socket;
        $this->_eventHandler = $eventHandler;
        $this->_loader = $loader;

        $this->_processor = new Processor($logger, $eventHandler, $this);

        $this->_loader->register($eventHandler, $this);

        $this->_channels = [];
    }

    /**
     * Connect to the socket stream and to the irc and request capabilities.
     *
     * @return int
     */
    public function connect(): int
    {
        $this->_socket->connect();
        $this->setLastPing(time());

        $this->enqueue(
            [
            'CAP REQ :twitch.tv/tags twitch.tv/commands twitch.tv/membership',
            'PASS ' . $this->getConfig()->password,
            'NICK ' . $this->getConfig()->name,
            'JOIN #' . $this->getConfig()->channel
            ]
        );

        return $this->processQueue([$this, 'sendToSocket']);
    }

    /**
     * Connect to the irc and loop over the socket messages,
     * dispatching events and messages to channel as necessary.
     *
     * @return void
     */
    public function serve(): void
    {
        $processed = $this->connect();
        
        while ($this->_socket->isConnected()) {
            $message = null;
            
            $cost = microtime(true);
            while ((time() - $this->getLastPing()) > 300 || !$this->_socket->isConnected()) {
                $this->_logger->info('Restarting connection');
                $processed = $this->connect();
            }

            $data = $this->_socket->getNext();

            if ($data) {
                $message = new Message($data);
                $this->_processor->handle($message);
            }
            $this->_pollChannels();
            $processed += $this->processQueue([$this, 'sendToSocket']);
            if ($data || $processed > 0) {
                if ($processed > 0) {
                    $this->_logger->debug(
                        'Processed ' . $processed . ' messages from the client queue'
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
                'Pushing to stream: "' . trim($message) . '" at time ' . time()
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
                'Processed ' . $processed . ' messages from the channel queues'
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
     * Add a channel to the array.
     *
     * @param Channel $channel The channel to add.
     *
     * @return void
     */
    public function addChannel(Channel $channel): void
    {
        $this->_channels[$channel->getName()] = $channel;
    }

    /**
     * Remove a channel from the array.
     *
     * @param Channel $channel The channel to remove.
     *
     * @return void
     */
    public function removeChannel(Channel $channel): void
    {
        unset($this->_channels[$channel->getName()]);
    }
    /**
     * Check if the user is the client.
     *
     * @param string $user The username to check.
     *
     * @return boolean Whether the user is the client.
     */
    public function isMe(string $user): bool
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
    public function getChannel(string $name): Channel
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
     * Set the name of the client.
     *
     * @param string $name The name to set.
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->_name = $name;
    }

    /**
     * Get the last ping time.
     *
     * @return integer
     */
    public function getLastPing(): int
    {
        return $this->_lastPing;
    }

    /**
     * Set the last ping time.
     *
     * @param integer $lastPing The time to set.
     *
     * @return void
     */
    public function setLastPing(int $lastPing): void
    {
        $this->_lastPing = $lastPing;
    }
}
