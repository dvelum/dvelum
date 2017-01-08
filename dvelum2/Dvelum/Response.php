<?php

namespace Dvelum;

use Dvelum\Config;

class Response
{
    const FORMAT_HTML = 'html';
    const FORMAT_JSON = 'json';

    protected $format = self::FORMAT_HTML;
    /**
     * @return Response
     */
    static public function factory()
    {
        static $instance = null;

        if(empty($instance)){
            $instance = new static();
        }

        return $instance;
    }

    public function redirect($location)
    {
        \Response::redirect($location);
    }

    public function put(string $string)
    {
        echo $string;
    }

    public function send()
    {
        exit();
    }

    public function error($message)
    {
        switch ($this->format){
            case self::FORMAT_JSON :
                $message = json_encode(['success'=>false,'msg'=>$message]);
                break;
            case self::FORMAT_HTML :
                $this->notFound();
        }
        $this->put($message);
        $this->send();
    }

    public function success(array $data = [], array $params = [])
    {
        $message  = '';
        switch ($this->format)
        {
            case self::FORMAT_HTML:

                if(Config::storage()->get('main.php')->get('development')){
                    $this->put('<pre>');
                    $this->put(var_export(array_merge(['data'=>$data],$params),true));
                }
                break;
            case self::FORMAT_JSON :
                $message = ['success'=>true];
                if(!empty($data)){
                    $message['data'] = $data;
                }
                if(!empty($params)){
                    $message = array_merge($message, $params);
                }
                $message = json_encode($message);
                break;
        }
        $this->put($message);
        $this->send();
    }

    public function setFormat(string $format)
    {
        $this->format = $format;
    }

    public function json(array $data = [])
    {
        $this->put(json_encode($data));
        $this->send();
    }

    /**
     * Send 404 Response code
     */
    public function notFound()
    {
        header($_SERVER["SERVER_PROTOCOL"]."/1.0 404 Not Found");
    }
}