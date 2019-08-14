<?php

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

class Client extends Queue
{
    use Configurable;
    use RateLimit;

    private $logger;
    private $socket;
    private $eventhandler;
    private $loader;

    private $name;
    private $channels;

    public function __construct(Logger $logger, Socket $socket, EventHandler $eventhandler, Loader $loader)
    {
        $this->loadConfig();
        $this->initRate(20, 30);

        $this->logger = $logger;
        $this->socket = $socket;
        $this->eventhandler = $eventhandler;
        $this->loader = $loader;

        $this->loader->register($eventhandler, $this);

        $this->channels = [];
    }

    public function connect(): void
    {
        $this->socket->connect();

        $this->enqueue([
            'CAP REQ :twitch.tv/tags twitch.tv/commands twitch.tv/membership',
            'PASS ' . $this->config->password,
            'NICK ' . $this->config->name,
            'JOIN #' . $this->config->channel
        ]);
    }

    public function serve(): void
    {
        $this->connect();
        $this->processqueue([$this, 'sendtosocket']);

        $connected = true;

        $prefix = $this->config->prefix;
        $prefixlen = strlen($prefix);

        $lastping = time();

        while ($connected) {
            $cost = microtime(true);
            if ((time() - $lastping) > 300 or $this->socket === false) {
                $this->logger->info('Restarting connection');
                $this->connect();
                $lastping = time();
            }

            $data = $this->socket->getNext();

            if ($data) {
                $message = new Message($data);

                if (empty($message->getFrom())) {
                    switch ($message->getType()) {
                        case 'PING':
                            $lastping = time();
                            $this->enqueue(['PONG :' . $message->getParam(0)]);

                            //pong event
                            $this->eventhandler->trigger('pong');
                            break;
                        case 'PONG':
                            $latency = time() - $lastping;
                            $this->logger->info('Current latency: ' . $latency);
                            break;
                        default:
                            $this->logger->warning('Could not parse message: ' . $message->getRaw());
                            break;
                    }
                } else if ($message->getFrom() === 'tmi.twitch.tv') {
                    switch ($message->getType()) {
                        case '002':
                        case '003':
                        case '004':
                        case '375':
                        case '376':
                        case 'CAP':
                            break;
                        case '001':
                            $this->name = $message->getParam(0);
                            break;
                        case '372':
                            $this->logger->info('Client connected');
                            //connect event
                            $this->eventhandler->trigger('connect');
                            break;
                        case 'NOTICE':
                            foreach ([
                                'Login unsuccessful',
                                'Login authentication failed',
                                'Error logging in',
                                'Improperly formatted auth',
                                'Invalid NICK'
                            ] as $needle) {
                                if (strpos($message->getMessage(), $needle) !== false) {
                                    $this->logger->error('Potential auth failure');
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
                            $channel = $this->getChannel($message->getParam(0));
                            if ($channel) {
                                $channel->userstate($message);
                            }
                            break;
                        case 'GLOBALUSERSTATE':
                            break;
                        case 'ROOMSTATE':
                            break;
                        case 'SERVERCHANGE':
                            break;
                        default:
                            $this->logger->warning('Could not parse message: ' . $message->getRaw());
                            break;
                    }
                } else if ($message->getFrom() === 'jtv') {
                    switch ($message->getType()) {
                        case 'MODE':
                            break;
                        default:
                            $this->logger->warning('Could not parse message: ' . $message->getRaw());
                            break;
                    }
                } else {
                    switch ($message->getType()) {
                        case '353':
                            break;
                        case '366':
                            break;
                        case 'JOIN':
                            if ($this->isme($message->getUser())) {
                                $channel = new Channel($this->logger, $message);
                                $this->channels[$channel->getName()] = $channel;
                                $this->eventhandler->trigger('join', $channel);
                            } else {
                                //another user joined
                            }
                            break;
                        case 'PART':
                            break;
                        case 'WHISPER':
                            break;
                        case 'PRIVMSG':
                            if ($this->isme($message->getUser())) break;
                            $channel = $this->getChannel($message->getChannel());
                            if (substr($message->getMessage(), 0, $prefixlen) === $prefix) {
                                $messagearr = explode(' ', $message->getMessage());
                                $message->setCommand(substr(array_shift($messagearr), 1));
                                $this->eventhandler->trigger('command', $message, $channel);
                            } else {
                                $this->eventhandler->trigger('message', $message, $channel);
                            }
                            break;
                        default:
                            $this->logger->warning('Could not parse message: ' . $message->getRaw());
                            break;
                    }
                }
            }
            $this->pollchannels();
            $this->processqueue([$this, 'sendtosocket']);
            if ($data) {
                print_r(sprintf('Cost: %fms' . NL, (microtime(true) - $cost) * 1E3));
            }
        }
    }

    protected function sendtosocket(array $message): void
    {
        $this->logger->info('Sending message: "' . trim($message[0]) . '" at time ' . time());
        $this->socket->send($message[0]);
    }

    private function pollchannels(): void
    {
        foreach ($this->channels as $channel) {
            $channel->processqueue([$this, 'enqueue']);
        }
    }

    public function send(string $message): void
    {
        $this->enqueue([$message]);
    }

    private function isme(string $user): bool
    {
        return $user === $this->name;
    }

    private function getChannel(string $name): Channel
    {
        if (isset($this->channels[$name])) {
            return $this->channels[$name];
        }
        $this->logger->warning('Call to a nonexistant channel');
        return false;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getChannels(): array
    {
        return $this->channels;
    }
}
