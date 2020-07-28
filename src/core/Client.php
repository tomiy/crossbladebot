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

namespace crossbladebot\core;

use crossbladebot\basic\Configuration;
use crossbladebot\basic\KeyValueArray;
use crossbladebot\basic\RateLimit;
use crossbladebot\chat\Channel;
use crossbladebot\chat\Message;
use crossbladebot\component\Loader;
use crossbladebot\debug\Logger;
use crossbladebot\service\Processor;
use crossbladebot\service\Queue;
use Exception;
use ReflectionException;

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
    use RateLimit;

    /**
     * The logger object.
     *
     * @var Logger
     */
    private Logger $_logger;
    /**
     * The socket object handling the stream.
     *
     * @var Socket
     */
    private Socket $_socket;
    /**
     * The event handler.
     *
     * @var EventHandler
     */
    private EventHandler $_eventHandler;
    /**
     * The components' loader.
     *
     * @var Loader
     */
    private Loader $_loader;
    /**
     * The processor object to process the messages.
     *
     * @var Processor
     */
    private Processor $_processor;

    /**
     * The last unix time in seconds when the IRC sent a PING.
     *
     * @var int
     */
    private int $_lastPing;
    /**
     * The name of the bot. Retrieved during runtime.
     *
     * @var string
     */
    private string $_name;
    private string $_password;
    private string $_channel;
    /**
     * The channels array holding Channel objects.
     *
     * @var KeyValueArray
     */
    private KeyValueArray $_channels;

    private string $_prefix;
    
    /**
     * Instantiate a new client.
     *
     * @param Socket $socket The socket object holding the socket stream.
     * @param EventHandler $eventHandler The handler holding the component events.
     * @param Loader $loader The loader holding the components.
     * @throws ReflectionException
     */
    public function __construct(Socket $socket, EventHandler $eventHandler, Loader $loader)
    {
        $this->initRate(20, 30);
        
        $config = Configuration::load('Client.json');
        
        $this->setPrefix($config->get('prefix'));
        $this->setPassword($config->get('password'));
        $this->setName($config->get('name'));
        $this->setChannel($config->get('channel'));

        $this->_logger = Logger::getInstance();
        $this->setSocket($socket);
        $this->setEventHandler($eventHandler);
        $this->setLoader($loader);

        $this->setProcessor(new Processor($this->getEventHandler(), $this));

        $this->getLoader()->register($this->getEventHandler(), $this);

        $this->setChannels(new KeyValueArray([]));
    }

    /**
     * Connect to the irc and loop over the socket messages,
     * dispatching events and messages to channel as necessary.
     *
     * @return void
     * @throws Exception
     */
    public function serve(): void
    {
        $processed = $this->connect();

        while ($this->getSocket()->isConnected()) {
            $message = null;

            $cost = microtime(true);
            while ((time() - $this->getLastPing()) > 300 || !$this->getSocket()->isConnected()) {
                $this->_logger->info('Restarting connection');
                $processed = $this->connect();
            }

            $data = $this->getSocket()->getNext();

            if ($data) {
                $message = new Message($data);
                $this->getProcessor()->handle($message);
            }
            $this->_pollChannels();
            $processed += $this->processQueue([$this, 'sendToSocket']);
            if ($data || $processed > 0) {
                if ($processed > 0) {
                    $this->_logger->debug('Processed ' . $processed . ' messages from the client queue');
                    $processed = 0;
                }
                print_r(sprintf('Cost: %fms' . NL, (microtime(true) - $cost) * 1E3));
            }
        }
    }

    /**
     * Connect to the socket stream and to the irc and request capabilities.
     *
     * @return int
     * @throws Exception
     */
    public function connect(): int
    {   
        $this->getSocket()->connect();
        $this->setLastPing(time());

        $this->enqueue(
            [
                'CAP REQ :twitch.tv/tags twitch.tv/commands twitch.tv/membership',
                'PASS ' . $this->getPassword(),
                'NICK ' . $this->getName(),
                'JOIN #' . $this->getChannel()
            ]
        );

        return $this->processQueue([$this, 'sendToSocket']);
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

    /**
     * Process channel queues and queue the extracted messages into the client.
     *
     * @return int the number of processed messages
     */
    private function _pollChannels(): int
    {
        $processed = 0;
        foreach ($this->getChannels() as $channel) {
            $processed += $channel->processQueue([$this, 'enqueue']);
        }

        if ($processed > 0) {
            $this->_logger->debug('Processed ' . $processed . ' messages from the channel queues');
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
        $this->getChannels()->set($channel->getName(), $channel);
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
        unset($this->getChannels()[$channel->getName()]);
    }

    /**
     * Check if the user is the client.
     *
     * @param string $user The username to check.
     *
     * @return bool Whether the user is the client.
     */
    public function isMe(string $user): bool
    {
        return $user === $this->getName();
    }

    /**
     * @return \crossbladebot\core\Socket
     */
    public function getSocket()
    {
        return $this->_socket;
    }

    /**
     * @return \crossbladebot\core\EventHandler
     */
    public function getEventHandler()
    {
        return $this->_eventHandler;
    }

    /**
     * @return \crossbladebot\component\Loader
     */
    public function getLoader()
    {
        return $this->_loader;
    }

    /**
     * @return \crossbladebot\service\Processor
     */
    public function getProcessor()
    {
        return $this->_processor;
    }

    /**
     * @return KeyValueArray
     */
    public function getChannels()
    {
        return $this->_channels;
    }

    /**
     * @param \crossbladebot\core\Socket $_socket
     */
    public function setSocket($_socket)
    {
        $this->_socket = $_socket;
    }

    /**
     * @param \crossbladebot\core\EventHandler $_eventHandler
     */
    public function setEventHandler($_eventHandler)
    {
        $this->_eventHandler = $_eventHandler;
    }

    /**
     * @param \crossbladebot\component\Loader $_loader
     */
    public function setLoader($_loader)
    {
        $this->_loader = $_loader;
    }

    /**
     * @param \crossbladebot\service\Processor $_processor
     */
    public function setProcessor($_processor)
    {
        $this->_processor = $_processor;
    }

    /**
     * @param KeyValueArray $_channels
     */
    public function setChannels($_channels)
    {
        $this->_channels = $_channels;
    }

    /**
     * Get the channel object from the channel name.
     *
     * @param string $name The channel name.
     *
     * @return Channel The channel object.
     */
    public function getChannelByName(string $name): ?Channel
    {
        if (!is_null($this->getChannels()->get($name))) {
            return $this->getChannels()->get($name);
        }

        return null;
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

    public function setPrefix(string $prefix): void
    {
        $this->_prefix = $prefix;
    }
    
    public function getPrefix(): string
    {
        return $this->_prefix;
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
     * @return mixed
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->_channel;
    }

    /**
     * @param mixed $_password
     */
    public function setPassword($_password)
    {
        $this->_password = $_password;
    }

    /**
     * @param mixed $_channel
     */
    public function setChannel($_channel)
    {
        $this->_channel = $_channel;
    }

    public function disconnect(): void
    {
        $this->getSocket()->close();
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
            $this->_logger->debug('Pushing to stream: "' . trim($message) . '" at time ' . time());
            $this->getSocket()->send($message);
        }
    }
}
