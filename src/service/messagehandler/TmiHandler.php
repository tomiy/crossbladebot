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

namespace crossbladebot\service\messagehandler;

use crossbladebot\chat\Message;
use crossbladebot\core\Client;
use crossbladebot\core\EventHandler;

/**
 * Provides function to handle a tmi message.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class TmiHandler extends AbstractMessageHandler
{
    /**
     * Initialize the callback map for handling tmi messages.
     *
     * @param EventHandler $eventHandler The handler holding the component events.
     * @param Client $client The client object.
     */
    public function __construct(EventHandler $eventHandler, Client $client)
    {
        parent::__construct($eventHandler, $client);

        $this->callbackMap = [
            '002' => null,
            '003' => null,
            '004' => null,
            '375' => null,
            '376' => null,
            'CAP' => null,
            '001' => 'setClientName',
            '372' => 'notifyConnected',
            'NOTICE' => 'notice',
            'USERNOTICE' => 'userNotice',
            'HOSTTARGET' => null,
            'CLEARCHAT' => null,
            'CLEARMSG' => null,
            'RECONNECT' => null,
            'USERSTATE' => 'userState',
            'GLOBALUSERSTATE' => null,
            'ROOMSTATE' => null,
            'SERVERCHANGE' => null
        ];
    }

    /**
     * Set the bot's name.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    protected function setClientName(Message $message): void
    {
        $this->client->setName($message->getParam(0));
    }

    /**
     * Trigger the event that we are connected to the irc.
     *
     * @return void
     */
    protected function notifyConnected(): void
    {
        $this->logger->debug('Client connected');
        $this->eventHandler->trigger('connect');
    }
    
    /**
     * Handles the notice messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    protected function notice(Message $message): void
    {
        foreach ( //TODO: rework
            [
                'Login unsuccessful',
                'Login authentication failed',
                'Error logging in',
                'Improperly formatted auth',
                'Invalid NICK'
            ] as $needle) {
                if (strpos($message->getMessage(), $needle) !== false) {
                    $this->logger->error('Potential auth failure: ' . $needle);
                    $this->client->disconnect();
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
    protected function userNotice(Message $message): void
    {
        switch ($message->getId()) {
            default:
                //TODO: handle
                break;
        }
    }
    
    /**
     * Handles the user state messages.
     *
     * @param Message $message The message to handle.
     *
     * @return void
     */
    protected function userState(Message $message): void
    {
        $channel = $this->client->getChannelByName($message->getParam(0));
        if ($channel->isParted() === true) {
            $this->client->removeChannel($channel);
            $this->logger->debug('Removed channel ' . $channel->getName() . ' from client');
            unset($channel);
            return;
        }
        $channel->userState($message);
    }
}
