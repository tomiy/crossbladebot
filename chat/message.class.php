<?php

namespace CrossbladeBot\Chat;

/**
 * The message object holding the parsed parts of an IRC message.
 */
class Message
{

    /**
     * The raw IRC message.
     *
     * @var string
     */
    private $_raw;
    /**
     * The tags array holding info such as the display name.
     *
     * @var array
     */
    private $_tags;
    /**
     * The message type, usually PRIVMSG.
     *
     * @var string
     */
    private $_type;

    /**
     * The channel the message is sent to, if applicable.
     *
     * @var string
     */
    private $_channel;
    /**
     * The message contents, if applicable.
     *
     * @var string
     */
    private $_message;
    /**
     * The command, if the message has a command.
     *
     * @var string
     */
    private $_command;

    /**
     * The user sending the message, if applicable.
     *
     * @var string
     */
    private $_user;
    /**
     * The nickname of the user.
     *
     * @var string
     */
    private $_nick;
    /**
     * The hostname of the user.
     *
     * @var string
     */
    private $_host;

    /**
     * The sender of the message.
     *
     * @var string
     */
    private $_from;
    /**
     * The array of parameters, holding anything that isn't tags or type.
     *
     * @var array
     */
    private $_params;
    /**
     * The message id, if applicable. Used for notices.
     *
     * @var string
     */
    private $_id;

    public function __construct(string $string)
    {
        $this->_raw = trim($string);
        $this->_parse();
        $this->_badges();
        $this->_badgeInfo();
        $this->_emotes();
    }

    /**
     * Parse a message string.
     *
     * @return boolean Whether the message was able to be parsed.
     */
    private function _parse(): bool
    {
        $regex = implode('', [
            'open' => '/^',
            'tags' => '(?:@(?P<tags>[^\r\n ]*) +|())',
            'from' => '(?::(?P<from>[^\r\n ]+) +|())',
            'type' => '(?P<type>[^\r\n ]+)',
            'params' => '(?: +(?P<params>[^:\r\n ]+[^\r\n ]*(?: +[^:\r\n ]+[^\r\n ]*)*)|())?',
            'trailing' => '(?: +:(?P<trailing>[^\r\n]*)| +())?[\r\n]*',
            'close' => '$/'
        ]);

        $match = preg_match($regex, $this->_raw, $matches);

        if (!$match) {
            return false;
        }

        if ($this->_raw[0] === '@') {
            $rawTags = explode(';', $matches['tags']);

            for ($i = 0; $i < sizeof($rawTags); $i++) {
                $tag = $rawTags[$i];
                $pair = explode('=', $tag);
                $this->_tags[$pair[0]] = $pair[1];
            }
        }

        $this->_type = $matches['type'];

        if (!empty($matches['params'])) {
            $this->_params = explode(' ', $matches['params']);
        }

        if (isset($matches['trailing'])) {
            $this->_params[] = $matches['trailing'];
        }

        $this->_from = $matches['from'] ?? null;
        $this->_channel = $this->_params[0][0] == '#' ? $this->_params[0] : null;
        $this->_message = $this->_params[1] ?? null;
        $this->_id = $this->_tags['msg-id'] ?? null;

        $userMatch = preg_match('/(.*)!(.*)@(.*)/', $this->_from, $user);

        if ($userMatch) {
            $this->_nick = $user[0];
            $this->_user = $user[1];
            $this->_host = $user[2];
        } elseif (isset($this->_tags['display-name'])) {
            $this->_user = strtolower($this->_tags['display-name']);
        }

        return true;
    }

    /**
     * Parse the badges tag.
     *
     * @return void
     */
    private function _badges(): void
    {
        $this->_parseTag('badges');
    }

    /**
     * Parse the badges-info tag.
     *
     * @return void
     */
    private function _badgeInfo(): void
    {
        $this->_parseTag('badge-info');
    }

    /**
     * Parse the emotes tag.
     *
     * @return void
     */
    private function _emotes(): void
    {
        $this->_parseTag('emotes', '/', ':', ',');
    }

    /**
     * Parse a tag with a set of delimiters.
     *
     * @param string $index The tag to parse.
     * @param string $delim1 The first delimiter to split the tag.
     * @param string $delim2 The second delimiter to split the tag.
     * @param string $delim3 (optional) The last delimiter if necessary.
     * @return void
     */
    private function _parseTag(string $index, string $delim1 = ',', string $delim2 = '/', string $delim3 = null): void
    {
        if (!isset($this->_tags[$index])) {
            return;
        }

        $raw = $this->_tags[$index];

        if ($raw === true) {
            $this->_tags[$index] = null;
            return;
        }

        $this->_tags[$index] = [];

        if (is_string($raw)) {
            $spl = explode($delim1, $raw);

            for ($i = 0; $i < sizeof($spl); $i++) {
                $parts = explode($delim2, $spl[$i]);
                if ($parts[0]) {
                    $val = $parts[1];
                    if ($delim3 && $val) {
                        $val = explode($delim3, $val);
                    }
                    $this->_tags[$index][$parts[0]] = $val ?? null;
                }
            }
        }
    }

    public function getId(): string
    {
        return $this->_id;
    }

    public function getParams(): array
    {
        return $this->_params;
    }

    public function getParam(int $key): string
    {
        return $this->_params[$key];
    }

    public function getFrom(): string
    {
        return $this->_from;
    }

    public function getHost(): string
    {
        return $this->_host;
    }

    public function getNick(): string
    {
        return $this->_nick;
    }

    public function getUser(): string
    {
        return $this->_user;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getMessage(): string
    {
        return $this->_message;
    }

    public function getChannel(): string
    {
        return $this->_channel;
    }

    public function getType(): string
    {
        return $this->_type;
    }

    public function getTags(): array
    {
        return $this->_tags;
    }

    public function getTag(string $key): string
    {
        return $this->_tags[$key];
    }

    public function getRaw(): string
    {
        return $this->_raw;
    }

    public function setCommand(string $command): void
    {
        $this->command = $command;
    }
}
