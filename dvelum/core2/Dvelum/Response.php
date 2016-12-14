<?php
/**
 * Created by PhpStorm.
 * User: samuel
 * Date: 14.12.16
 * Time: 22:31
 */

namespace Dvelum;

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
        }
        $this->put($message);
        $this->send();
    }

    public function success(array $data = [], array $params = [])
    {
        switch ($this->format)
        {
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
}