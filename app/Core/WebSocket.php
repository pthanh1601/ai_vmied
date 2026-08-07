<?php
namespace Neo\Core;

use WebSocket\Client;

class WebSocket
{
    protected $client;
    protected $url;
    protected $error;

    public function __construct($url)
    {
        $this->url = $url;
        try {
            // Textalk/websocket Client
            $this->client = new Client($url);
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function send($message)
    {
        if ($this->error)
            return false;
        try {
            $this->client->text($message);
            return true;
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function receive()
    {
        if ($this->error)
            return false;
        try {
            return $this->client->receive();
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function close()
    {
        if ($this->client)
            $this->client->close();
    }

    public function getError()
    {
        return $this->error;
    }
}
