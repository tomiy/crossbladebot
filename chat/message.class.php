<?php

namespace CrossbladeBot\Chat;

class Message
{

    private $raw;
    private $tags;
    private $type;

    private $channel;
    private $message;
    private $command;

    private $user;
    private $nick;
    private $host;

    private $from;
    private $params;
    private $id;

    public function __construct(string $string)
    {
        $this->raw = trim($string);
        $this->parse();
        $this->badges();
        $this->badgeinfo();
        $this->emotes();
    }

    private function parse(): bool
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

        $match = preg_match($regex, $this->raw, $matches);

        if (!$match) return false;

        if ($this->raw[0] === '@') {
            $rawtags = explode(';', $matches['tags']);

            for ($i = 0; $i < sizeof($rawtags); $i++) {
                $tag = $rawtags[$i];
                $pair = explode('=', $tag);
                $this->tags[$pair[0]] = $pair[1];
            }
        }

        $this->type = $matches['type'];

        if (!empty($matches['params'])) {
            $this->params = explode(' ', $matches['params']);
        }

        if (isset($matches['trailing'])) {
            $this->params[] = $matches['trailing'];
        }

        $this->from = $matches['from'] ?? null;
        $this->channel = $this->params[0][0] == '#' ? $this->params[0] : null;
        $this->message = $this->params[1] ?? null;
        $this->id = $this->tags['msg-id'] ?? null;

        $usermatch = preg_match('/(.*)!(.*)@(.*)/', $this->from, $user);

        if ($usermatch) {
            $this->nick = $user[0];
            $this->user = $user[1];
            $this->host = $user[2];
        } else if (isset($this->tags['display-name'])) {
            $this->user = strtolower($this->tags['display-name']);
        }

        return true;
    }

    private function badges(): void
    {
        $this->parsetag('badges');
    }

    private function badgeinfo(): void
    {
        $this->parsetag('badge-info');
    }

    private function emotes(): void
    {
        $this->parsetag('emotes', '/', ':', ',');
    }

    private function parsetag(string $index, string $delim1 = ',', string $delim2 = '/', string $delim3 = null): void
    {

        if (!isset($this->tags[$index])) {
            return;
        }

        $raw = $this->tags[$index];

        if ($raw === true) {
            $this->tags[$index] = null;
            return;
        }

        $this->tags[$index] = [];

        if (is_string($raw)) {
            $spl = explode($delim1, $raw);

            for ($i = 0; $i < sizeof($spl); $i++) {
                $parts = explode($delim2, $spl[$i]);
                if ($parts[0]) {
                    $val = $parts[1];
                    if ($delim3 && $val) {
                        $val = explode($delim3, $val);
                    }
                    $this->tags[$index][$parts[0]] = $val ?? null;
                }
            }
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getParam(int $key): string
    {
        return $this->params[$key];
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getNick(): string
    {
        return $this->nick;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function getTag(string $key): string
    {
        return $this->tags[$key];
    }

    public function getRaw(): string
    {
        return $this->raw;
    }

    public function setCommand(string $command): void
    {
        $this->command = $command;
    }
}
