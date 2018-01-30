<?php
namespace Dvelum\App\Controller;

class EventManager
{
    protected $listeners = [];
    protected $error = '';

    const BEFORE_LIST = 'before_list';
    const AFTER_LIST = 'after_list';
    const BEFORE_LOAD = 'before_load';
    const BEFORE_LINKED_LIST = 'before_linked_list';
    const AFTER_LOAD = 'after_load';
    const AFTER_LINKED_LIST ='after_linked_list';

    const AFTER_UPDATE_BEFORE_COMMIT = 'after_update_before_commit';
    const AFTER_INSERT_BEFORE_COMMIT = 'after_insert_before_commit';

    /**
     * @param string $event
     * @param callable|array [obj,method] $handler
     */
    public function on(string $event, $handler)
    {
        if(!isset($this->listeners[$event])){
            $this->listeners[$event] = [];
        }

        $listener = new \stdClass();
        $listener->handler = $handler;

        $this->listeners[$event][] = $listener;
    }

    public function fireEvent($event, \stdClass $data) : bool
    {
        $this->error = '';

        if(!isset($this->listeners[$event])){
            return true;
        }

        $e = new Event();
        $e->setData($data);

        foreach ($this->listeners[$event] as $listener){
            if($e->isPropagationStopped()){
                return false;
            }

            if(is_callable($listener->handler)){
                ($listener->handler)($e);
            }else{
                call_user_func_array($listener->handler, $e);
            }

            if($e->hasError()){
                $this->error = $e->getError();
                return false;
            }
        }
        return true;
    }

    /**
     * Get event error message
     * @return string
     */
    public function getError() : string
    {
        return $this->error;
    }
}