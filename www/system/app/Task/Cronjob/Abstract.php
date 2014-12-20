<?php
abstract class Task_Cronjob_Abstract extends Bgtask_Abstract
{
    const RESULT_OK =1;
    const RESULT_NO_DATA =2;
    const RESULT_ERROR = 3;

    /**
     * @var Cron_Lock
     */
    protected $lock;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
      $this->lock = $config['lock'];
      parent::__construct($config);
    }

    /**
     * (non-PHPdoc)
     * @see Bglock_Abstract::terminate()
     */
    public function terminate()
    {
        $this->lock->releaseLock();
        parent::terminate();
    }

    /**
     * (non-PHPdoc)
     * @see Bglock_Abstract::finish()
     */
    public function finish()
    {
        $this->lock->releaseLock();
        parent::finish();
    }

    /**
     * (non-PHPdoc)
     * @see Bglock_Abstract::error()
     */
    public function error($message = '')
    {
        $this->lock->releaseLock();
        parent::error($this->lock->getlockName().' '.$message);
    }

    /**
     * (non-PHPdoc)
     * @see Bglock_Abstract::stop()
     */
    public function stop()
    {
        $this->lock->releaseLock();
        parent::stop();
    }

    /**
     * (non-PHPdoc)
     * @see Bglock_Abstract::updateState()
     */
    public function updateState()
    {
      $this->lock->sync();
      parent::updateState();
    }

    /**
     * (non-PHPdoc)
     * @see Bglock_Abstract::processSignals()
     */
    public function processSignals()
    {
        if($this->lock->isTimeLimitReached())
        {
          $this->log('Time limit has been reached');
          $this->finish();
        }
        parent::processSignals();
    }

    /**
     * Record the message to the lock log
     * @param string $message
     */
    public function log($message)
    {
       parent::log($this->lock->getTaskName().' '.$message);
    }

}