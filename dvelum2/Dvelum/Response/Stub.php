<?php
namespace Dvelum\Response;

use Dvelum\Response;

class Stub extends Response
{
    protected $headers = [];

    static public function factory()
    {
        return new static();
    }

    public function send() : void
    {
        $this->sent = true;
    }

    /**
     * Send 404 Response header
     * @return void
     */
    public function notFound() : void
    {
        if(isset($_SERVER["SERVER_PROTOCOL"])){
            $this->header($_SERVER["SERVER_PROTOCOL"]."/1.0 404 Not Found");
        }
    }

    /**
     * Send response header
     * @param string $string
     * @return void
     */
    public function header(string $string) : void {
        $this->headers[] = $string;
    }
}