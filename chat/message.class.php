<?php

namespace CrossbladeBot\Chat;

class Message
{

    public $raw;
    public $tags;
    public $type;

    public $channel;
    public $message;
    public $command;
    public $user;

    public $from;
    public $params;
    public $id;

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

        if(!$match) return false;

        if($this->raw[0] === '@') {
            $rawtags = explode(';', $matches['tags']);

            for ($i = 0; $i < sizeof($rawtags); $i++) {
                $tag = $rawtags[$i];
                $pair = explode('=', $tag);
                $this->tags[$pair[0]] = $pair[1];
            }
        }

        $this->from = $matches['from'];
        $this->type = $matches['type'];

        if(!empty($matches['params'])) {
            $this->params = explode(' ', $matches['params']);
        }

        if(isset($matches['trailing'])) {
            $this->params[] = $matches['trailing'];
        }

        $this->channel = $this->params[0] ?? null;
        $this->message = $this->params[1] ?? null;
        $this->id = $this->tags['msg-id'] ?? null;
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
}
