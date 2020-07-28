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

use crossbladebot\basic\KeyValueArray;
use crossbladebot\debug\Logger;
use Exception;

/**
 * Registers and triggers callbacks for the defined events.
 *
 * @category PHP
 * @package  CrossbladeBot
 * @author   tomiy <tom@tomiy.me>
 * @license  https://github.com/tomiy/crossbladebot/blob/master/LICENSE GPL-3.0
 * @link     https://github.com/tomiy/crossbladebot
 */
class EventHandler
{
    /**
     * The event array.
     *
     * [
     * 'eventName1' => ['id1' => callback1, 'id2' => callback2],
     * 'eventName2' => ['id3' => callback3, 'id4' => callback4]
     * ]
     *
     * @var KeyValueArray
     */
    private KeyValueArray $_events;
    /**
     * The event ids.
     * Useful for clearing events instead of travelling the event array.
     * ['id1' => 'eventName1', 'id3' => 'eventName2']
     *
     * @var KeyValueArray
     */
    private KeyValueArray $_uids;
    /**
     * The logger object.
     *
     * @var Logger
     */
    private Logger $_logger;

    /**
     * Instantiate a new event handler.
     *
     */
    public function __construct()
    {
        $this->setEvents(new KeyValueArray([]));
        $this->setUids(new KeyValueArray([]));
        
        $this->_logger = Logger::getInstance();
    }

    /**
     * Registers an event into the pool.
     *
     * @param string $event The event name to register to.
     * @param callable $callback The callback to call on trigger.
     *
     * @return int The event id.
     * @throws Exception
     */
    public function register(string $event, callable $callback): int
    {
        $uid = random_int(intval(1E9), intval(1E10 - 1));

        if (is_null($this->getEvents()->get($event))) {
            $this->getEvents()->set($event, new KeyValueArray([]));
        }

        $this->getEvents()->get($event)->set($uid, $callback);
        $this->getUids()->set($uid, $event);

        $this->_logger->debug('Registered event ' . $uid);

        return $uid;
    }

    /**
     * Triggers an event and processes every attached callback.
     *
     * @param string $event The event name to trigger.
     * @param mixed ...$data The data to pass to the callbacks.
     *
     * @return void
     */
    public function trigger(string $event, ...$data): void
    {
        if (is_null($this->getEvents()->get($event))) {
            return;
        }

        foreach ($this->getEvents()->get($event)->getArray() as $uid => $callback) {
            $this->_logger->debug('Triggered event ' . $event . ' (uid ' . $uid . ')');
            call_user_func($callback, ...$data);
        }
    }

    /**
     * Removes an event from the pool.
     *
     * @param string $uid The event id to remove.
     *
     * @return void
     */
    public function clear(string $uid): void
    {
        if (is_null($this->getUids()->get($uid))) {
            return;
        }
        unset($this->getEvents()->get($this->getUids()->get($uid))[$uid]);
        unset($this->getUids()[$uid]);

        $this->_logger->debug('Cleared event ' . $uid);
    }
    /**
     * @return KeyValueArray
     */
    public function getEvents(): KeyValueArray
    {
        return $this->_events;
    }

    /**
     * @return KeyValueArray
     */
    public function getUids(): KeyValueArray
    {
        return $this->_uids;
    }

    /**
     * @param KeyValueArray $_events
     */
    public function setEvents(KeyValueArray $_events): void
    {
        $this->_events = $_events;
    }

    /**
     * @param KeyValueArray $_uids
     */
    public function setUids(KeyValueArray $_uids): void
    {
        $this->_uids = $_uids;
    }
}
