<?php
class Task_Cronjob_Test extends Task_Cronjob_Abstract
{
    /**
     * (non-PHPdoc)
     * @see Bgtask_Abstract::getDescription()
     */
    public function getDescription()
    {
        return 'Testing cronjob task. Thread ' . $this->_config['thread'];
    }

    /**
     * (non-PHPdoc)
     * @see Bgtask_Abstract::run()
     */
    public function run()
    {
        $property1 = $this->_config['property_1'];
        $property2 = $this->_config['property_2'];

        $this->log('Property 1 = '. $property1);

        $this->setTotalCount(3);
        for($i = 0; $i < 3; $i++)
        {
            $this->doSomething($i);
            $this->incrementCompleted();
            $this->updateState();
            $this->processSignals();
        }
        $this->finish();
    }

    protected function doSomething($counter)
    {
        for($i=0;$i<5;$i++)
        {
          sleep(1);
          $this->updateState();
          $this->processSignals();
        }
    }
}