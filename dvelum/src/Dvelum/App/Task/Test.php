<?php

namespace Dvelum\App\Task;

use Dvelum\BackgroundTask\AbstractTask;

/**
 * Background task
 * Test task
 * @author Kirill Egorov
 */
class Test extends AbstractTask
{
    /**
     * (non-PHPdoc)
     * @see Bgtask_Abstract::getDescription()
     */
    public function getDescription()
    {
        return 'Test task 1000 slow operations';
    }

    /**
     * (non-PHPdoc)
     * @see Bgtask_Abstract::run()
     */
    public function run()
    {
        $this->setTotalCount(100);

        for ($i = 0; $i < 100; $i++) {
            sleep(1);
            $this->incrementCompleted();
            $this->updateState();
            $this->processSignals();
        }

        $this->finish();
    }
}
