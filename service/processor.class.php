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

namespace CrossbladeBot\Service;

use CrossbladeBot\Core\EventHandler;
use CrossbladeBot\Core\Client;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Chat\Channel;
use CrossbladeBot\Debug\Logger;

/**
 * Provides function to process IRC messages for the client.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class Processor
{
    /**
     * The logger object.
     *
     * @var Logger
     */
    private $_logger;
    /**
     * The event handler holding the component events.
     *
     * @var EventHandler;
     */
    private $_eventHandler;
    /**
     * The client object.
     *
     * @var Client
     */
    private $_client;

    /**
     * The command prefix.
     *
     * @var string
     */
    private $_prefix;
    /**
     * The length of the prefix.
     *
     * @var int
     */
    private $_prefixLen;

    /**
     * Instantiate a new processor.
     *
     * @param Logger       $logger       The logger object.
     * @param EventHandler $eventHandler The handler holding the component events.
     * @param Client       $client       The client object.
     */
    public function __construct(
        Logger $logger,
        EventHandler $eventHandler,
        Client $client
    ) {
        $this->_logger = $logger;
        $this->_eventHandler = $eventHandler;
        $this->_client = $client;

        $this->_prefix = $client->getConfig()->prefix;
        $this->_prefixLen = strlen($this->_prefix);
    }

    /**
     * Handle ping messages (coming directly from the stream)
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    public function handlePing(Message $message): void
    {
        switch ($message->getType()) {
        case 'PING':
                $this->_client->setLastPing(time());
                $this->_client->send('PONG :' . $message->getParam(0));
                $this->_eventHandler->trigger('pong');
            break;
        case 'PONG':
                $latency = time() - $this->_client->getLastPing();
                $this->_logger->info('Current latency: ' . $latency);
            break;
        default:
                $this->_cantParse($message);
            break;
        }
    }

    /**
     * Handle messages coming from tmi.twitch.tv.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    public function handleTmi(Message $message): void
    {
        switch ($message->getType()) {
        case '002':
        case '003':
        case '004':
        case '375':
        case '376':
        case 'CAP':
            break;
        case '001':
                $this->_client->setName($message->getParam(0));
            break;
        case '372':
                $this->_logger->debug('Client connected');
                $this->_eventHandler->trigger('connect');
            break;
        case 'NOTICE':
            $this->_notice($message);
            break;
        case 'USERNOTICE':
            $this->_userNotice($message);
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
            $this->_userState($message);
            break;
        case 'GLOBALUSERSTATE':
            break;
        case 'ROOMSTATE':
            break;
        case 'SERVERCHANGE':
            break;
        default:
            $this->_cantParse($message);
            break;
        }
    }

    /**
     * Handle messages coming from jtv.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    public function handleJtv(Message $message): void
    {
        switch ($message->getType()) {
        case 'MODE':
            break;
        default:
            $this->_cantParse($message);
            break;
        }
    }

    /**
     * Handle messages coming from users (<user>!<user>@<user>.tmi.twitch.tv)
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    public function handleUserMessage(Message $message): void
    {
        switch ($message->getType()) {
        case '353':
            break;
        case '366':
            break;
        case 'JOIN':
            $this->_join($message);
            break;
        case 'PART':
            $this->_part($message);
            break;
        case 'WHISPER':
            break;
        case 'PRIVMSG':
            $this->_privMsg($message);
            break;
        default:
            $this->_cantParse($message);
            break;
        }
    }

    /**
     * Handles the part messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    private function _part(Message $message): void
    {
        $channel = $this->_client->getChannel($message->getChannel());
        $channel->part();
        $this->_eventHandler->trigger('part', $channel);
    }

    /**
     * Handles the join messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    private function _join(Message $message): void
    {
        if ($this->_client->isMe($message->getUser())) {
            $channel = new Channel($this->_logger, $message);
            $this->_client->addChannel($channel);
            $this->_logger->debug(
                'Added channel ' . $channel->getName() . ' to client'
            );
            $this->_eventHandler->trigger('join', $channel);
            return;
        }
        //TODO: another user joined
    }

    /**
     * Handles the chat messages & commands.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    private function _privMsg(Message $message): void
    {
        if ($this->_client->isMe($message->getUser())) {
            return;
        }
        $channel = $this->_client->getChannel($message->getChannel());
        if (substr(
            $message->getMessage(),
            0,
            $this->_prefixLen
        ) === $this->_prefix
        ) {
            $args = explode(' ', $message->getMessage());
            $message->setCommand(
                substr(array_shift($args), $this->_prefixLen)
            );
            if (!empty($args)) {
                $this->_eventHandler->trigger('command', $message, $channel, $args);
                return;
            }
            $this->_eventHandler->trigger('command', $message, $channel);
            return;
        }
        $this->_eventHandler->trigger('message', $message, $channel);
    }

    /**
     * Handles the user state messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    private function _userState(Message $message): void
    {
        $channel = $this->_client->getChannel($message->getParam(0));
        if ($channel->isParted() === true) {
            $this->_client->removeChannel($channel);
            $this->_logger->debug(
                'Removed channel ' . $channel->getName() . ' from client'
            );
            unset($channel);
            return;
        }
        $channel->userState($message);
    }

    /**
     * Handles the notice messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    private function _notice(Message $message): void
    {
        foreach (
            [
            'Login unsuccessful',
            'Login authentication failed',
            'Error logging in',
            'Improperly formatted auth',
            'Invalid NICK'
            ] as $needle) {
            if (strpos($message->getMessage(), $needle) !== false) {
                $this->_logger->error('Potential auth failure: ' . $needle);
                $this->_client->setConnected(false);
                break;
            }
        }
    }

    /**
     * Handles the user notice messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    private function _userNotice(Message $message): void
    {
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
    }

    /**
     * Log the message that can't be parsed at warning level.
     *
     * @param Message $message The message to log.
     *
     * @return void
     */
    private function _cantParse(Message $message): void
    {
        $this->_logger->warning(
            'Could not parse message: ' . $message->getRaw()
        );
    }
}
