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

    public function __construct($string)
    {
        $this->raw = trim($string);
        $this->parse();
        $this->badges();
        $this->badgeinfo();
        $this->emotes();
    }

    private function parse()
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
        } else if(isset($this->tags['display-name'])) {
            $this->user = strtolower($this->tags['display-name']);
        }
    }

    private function badges()
    {
        $this->parsetag('badges');
    }

    private function badgeinfo()
    {
        $this->parsetag('badge-info');
    }

    private function emotes()
    {
        $this->parsetag('emotes', '/', ':', ',');
    }

    private function parsetag($index, $delim1 = ',', $delim2 = '/', $delim3 = null)
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

    public function getId()
    {
        return $this->id;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getParam($key)
    {
        return $this->params[$key];
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getNick()
    {
        return $this->nick;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getCommand()
    {
        return $this->command;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getTag($key)
    {
        return $this->tags[$key];
    }

    public function getRaw()
    {
        return $this->raw;
    }

    public function setCommand($command)
    {
        $this->command = $command;
    }
}
