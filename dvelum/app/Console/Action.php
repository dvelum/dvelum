<?php
abstract class Console_Action
{
    /**
     * Main application config
     * @var Config_Abstract
     */
    protected $appConfig;
    protected $stat = [];
    /**
     * Action params
     * @var array
     */
    protected $params =[];

    /**
     * @param Config_Abstract $appConfig
     * @param array $params
     */
    public function init(Config_Abstract $appConfig, array $params = [])
    {
        $this->appConfig = $appConfig;
        $this->params = $params;
        $time = microtime(true);
        $this->run();
        $this->stat['time'] = number_format((microtime(true)-$time) , 5).'s.';
        echo get_called_class().': '.$this->getStatString()."\n";
        Application::close();
    }

    /**
     * Get job statistics as string
     * (useful for logs)
     * @return string
     */
    public function getStatString()
    {
        $s = '';
        foreach ($this->stat as $k=>$v)
            $s.= $k .' : '.$v.'; ';

        return $s;
    }

    /**
     * @return void
     */
    abstract public function run();
}