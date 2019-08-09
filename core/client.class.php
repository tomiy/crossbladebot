<?php

namespace CrossbladeBot\Core;

use CrossbladeBot\Traits\Configurable;
use CrossbladeBot\Chat\Message;
use CrossbladeBot\Chat\Channel;

class Client extends Configurable
{

    private $logger;
    private $socket;
    private $eventhandler;
    private $loader;

    private $name;
    private $channels;

    public function __construct($logger, $socket, $eventhandler, $loader)
    {
        parent::__construct();

        $this->logger = $logger;
        $this->socket = $socket;
        $this->eventhandler = $eventhandler;
        $this->loader = $loader;

        $this->loader->register($eventhandler);
    }

    public function connect()
    {
        $this->socket->connect();

        $this->socket->send('CAP REQ :twitch.tv/tags twitch.tv/commands twitch.tv/membership');
        $this->socket->send('PASS ' . $this->config->password);
        $this->socket->send('NICK ' . $this->config->name);
        $this->socket->send('JOIN #' . $this->config->channel);
    }

    public function serve()
    {
        $this->connect();

        $connected = true;

        $prefix = $this->config->prefix;
        $prefixlen = strlen($prefix);

        $lastping = time();

        while ($connected) {
            if ((time() - $lastping) > 300 or $this->socket === false) {
                $this->logger->info('Restarting connection');
                $this->connect();
                $lastping = time();
            }

            $data = $this->socket->getNext();

            if ($data) {
                $cost = microtime(true);
                $message = new Message($data);

                if (empty($message->from)) {
                    switch ($message->type) {
                        case 'PING':
                            $lastping = time();
                            $this->socket->send('PONG :' . $message->params[0]);

                            //pong event
                            $this->eventhandler->trigger('pong');
                            break;
                        case 'PONG':
                            $latency = time() - $lastping;
                            $this->logger->info('Current latency: ' . $latency);
                            break;
                        default:
                            $this->logger->warning('Could not parse message: ' . $message->raw);
                            break;
                    }
                } else if ($message->from === 'tmi.twitch.tv') {
                    switch ($message->type) {
                        case '002':
                        case '003':
                        case '004':
                        case '375':
                        case '376':
                        case 'CAP':
                            break;
                        case '001':
                            $this->name = $message->params[0];
                            break;
                        case '372':
                            $this->logger->info('Client connected');
                            //connect event
                            $this->eventhandler->trigger('connect');
                            break;
                        case 'NOTICE':
                            switch ($message->id) {
                                case "subs_on":
                                    break;
                                case "subs_off":
                                    break;
                                case "emote_only_on":
                                    break;
                                case "emote_only_off":
                                    break;
                                case "slow_on":
                                case "slow_off":
                                    break;
                                case "followers_on_zero":
                                case "followers_on":
                                case "followers_off":
                                    break;
                                case "r9k_on":
                                    break;
                                case "r9k_off":
                                    break;
                                case "room_mods":
                                    break;
                                case "no_mods":
                                    break;
                                case "vips_success":
                                    break;
                                case "no_vips":
                                    break;
                                case "already_banned":
                                case "bad_ban_admin":
                                case "bad_ban_broadcaster":
                                case "bad_ban_global_mod":
                                case "bad_ban_self":
                                case "bad_ban_staff":
                                case "usage_ban":
                                    break;
                                case "ban_success":
                                    break;
                                case "usage_clear":
                                    break;
                                case "usage_mods":
                                    break;
                                case "mod_success":
                                    break;
                                case "usage_vips":
                                    break;
                                case "usage_vip":
                                case "bad_vip_grantee_banned":
                                case "bad_vip_grantee_already_vip":
                                    break;
                                case "vip_success":
                                    break;
                                case "usage_mod":
                                case "bad_mod_banned":
                                case "bad_mod_mod":
                                    break;
                                case "unmod_success":
                                    break;
                                case "unvip_success":
                                    break;
                                case "usage_unmod":
                                case "bad_unmod_mod":
                                    break;
                                case "usage_unvip":
                                case "bad_unvip_grantee_not_vip":
                                    break;
                                case "color_changed":
                                    break;
                                case "usage_color":
                                case "turbo_only_color":
                                    break;
                                case "commercial_success":
                                    break;
                                case "usage_commercial":
                                case "bad_commercial_error":
                                    break;
                                case "hosts_remaining":
                                    break;
                                case "bad_host_hosting":
                                case "bad_host_rate_exceeded":
                                case "bad_host_error":
                                case "usage_host":
                                    break;
                                case "already_r9k_on":
                                case "usage_r9k_on":
                                    break;
                                case "already_r9k_off":
                                case "usage_r9k_off":
                                    break;
                                case "timeout_success":
                                    break;
                                case "delete_message_success":
                                case "already_subs_off":
                                case "usage_subs_off":
                                    break;
                                case "already_subs_on":
                                case "usage_subs_on":
                                    break;
                                case "already_emote_only_off":
                                case "usage_emote_only_off":
                                    break;
                                case "already_emote_only_on":
                                case "usage_emote_only_on":
                                    break;
                                case "usage_slow_on":
                                    break;
                                case "usage_slow_off":
                                    break;
                                case "usage_timeout":
                                case "bad_timeout_admin":
                                case "bad_timeout_broadcaster":
                                case "bad_timeout_duration":
                                case "bad_timeout_global_mod":
                                case "bad_timeout_self":
                                case "bad_timeout_staff":
                                    break;
                                case "untimeout_success":
                                case "unban_success":
                                    break;
                                case "usage_unban":
                                case "bad_unban_no_ban":
                                    break;
                                case "usage_delete":
                                case "bad_delete_message_error":
                                case "bad_delete_message_broadcaster":
                                case "bad_delete_message_mod":
                                    break;
                                case "usage_unhost":
                                case "not_hosting":
                                    break;
                                case "whisper_invalid_login":
                                case "whisper_invalid_self":
                                case "whisper_limit_per_min":
                                case "whisper_limit_per_sec":
                                case "whisper_restricted_recipient":
                                    break;
                                case "no_permission":
                                case "msg_banned":
                                case "msg_room_not_found":
                                case "msg_channel_suspended":
                                case "tos_ban":
                                    break;
                                case "msg_rejected":
                                case "msg_rejected_mandatory":
                                    break;
                                case "unrecognized_cmd":
                                    break;
                                case "cmds_available":
                                case "host_target_went_offline":
                                case "msg_censored_broadcaster":
                                case "msg_duplicate":
                                case "msg_emoteonly":
                                case "msg_verified_email":
                                case "msg_ratelimit":
                                case "msg_subsonly":
                                case "msg_timedout":
                                case "msg_bad_characters":
                                case "msg_channel_blocked":
                                case "msg_facebook":
                                case "msg_followersonly":
                                case "msg_followersonly_followed":
                                case "msg_followersonly_zero":
                                case "msg_slowmode":
                                case "msg_suspended":
                                case "no_help":
                                case "usage_disconnect":
                                case "usage_help":
                                case "usage_me":
                                    break;
                                case "host_on":
                                case "host_off":
                                    break;
                                default:
                                    $this->logger->warning('Potential auth failure');
                                    break;
                            }
                            break;
                        case 'USERNOTICE':
                            switch ($message->id) {
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
                            $channel = $this->getChannel($message->params[0]);
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
                            $this->logger->warning('Could not parse message: ' . $message->raw);
                            break;
                    }
                } else if ($message->from === 'jtv') {
                    switch ($message->type) {
                        case 'MODE':
                            break;
                        default:
                            $this->logger->warning('Could not parse message: ' . $message->raw);
                            break;
                    }
                } else {
                    $message->user = substr($message->from, 0, strpos($message->from, '!'));

                    switch ($message->type) {
                        case '353':
                            break;
                        case '366':
                            break;
                        case 'JOIN':
                            if ($this->isme($message->user)) {
                                $channel = new Channel($this->logger, $this->socket, $message);
                                $this->channels[$message->params[0]] = $channel;
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
                            if ($this->isme($message->user)) break;
                            $channel = $this->getChannel($message->channel);
                            if (substr($message->message, 0, $prefixlen) === $prefix) {
                                $messagearr = explode(' ', $message->message);
                                $message->command = substr(array_shift($messagearr), 1);
                                $this->eventhandler->trigger('command', $message, $channel);
                            } else {
                                $this->eventhandler->trigger('message', $message, $channel);
                            }
                            break;
                        default:
                            $this->logger->warning('Could not parse message: ' . $message->raw);
                            break;
                    }
                }
                print_r(sprintf('Cost: %fms' . NL, (microtime(true) - $cost) * 1E4));
            }
        }
    }

    private function isme($user)
    {
        return $user === $this->name;
    }

    private function getChannel($name)
    {
        if (isset($this->channels[$name])) {
            return $this->channels[$name];
        }
        $this->logger->warning('Call to a nonexistant channel');
        return false;
    }
}
