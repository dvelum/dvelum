<?php

namespace Dvelum\App\Task\Orm;

use Dvelum\BackgroundTask\AbstractTask;

use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Store\Factory;
use Dvelum\Lang;

/**
 * Class Encrypt
 * @package Dvelum\App\Task\Orm
 */
class Encrypt extends AbstractTask
{
    protected $buckedSize = 20;

    /**
     * (non-PHPdoc)
     * @see Bgtask_Abstract::getDescription()
     */
    public function getDescription()
    {
        $lang = Lang::lang();
        return $lang->get('ENCRYPT_DATA') . ': ' . $this->config['object'];
    }

    public function goBackground()
    {
        ini_set('max_execution_time', 0);
        ini_set('ignore_user_abort', 'On');
        session_write_close();

        echo json_encode(['success' => true]);
        echo ob_get_clean();
        flush();
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        ob_start();
    }

    /**
     * (non-PHPdoc)
     * @see Bgtask_Abstract::run()
     */
    public function run()
    {
        $object = $this->config['object'];
        $container = $this->config['session_container'];

        /*
         * Save task ID into session for UI
         */
        $session = Factory::get(Factory::SESSION);
        $session->set($container, $this->pid);
        $this->goBackground();

        $objectConfig = Orm\Record\Config::factory($object);
        $ivField = $objectConfig->getIvField();
        $primaryKey = $objectConfig->getPrimaryKey();

        $model = Model::factory($object);
        $count = Model::factory($object)->query()->filters([$ivField => null])->getCount();
        $this->setTotalCount($count);

        if (!$count) {
            $this->finish();
        }

        $ignore = [];
        $data = $model->query()->params(['limit' => $this->buckedSize])->filters([$ivField => null])->fields(
            [$primaryKey]
        )->fetchAll();

        while (!empty($data)) {
            $ids = \Dvelum\Utils::fetchCol($primaryKey, $data);

            $objectList = Orm\Record::factory($object, $ids);
            $count = 0;
            foreach ($objectList as $dataObject) {
                if (!$dataObject->save()) {
                    $ignore[] = $dataObject->getId();
                    $this->log('Cannot encrypt ' . $dataObject->getName() . ' ' . $dataObject->getId());
                } else {
                    $count++;
                }
            }
            /*
            * Update task status and check for signals
            */
            $this->incrementCompleted($count);
            $this->updateState();
            $this->processSignals();

            if (!empty($ignore)) {
                $filters = array(
                    $ivField => null,
                    $primaryKey => new \Dvelum\Db\Select\Filter($primaryKey, $ignore, \Dvelum\Db\Select\Filter::NOT_IN)
                );
            } else {
                $filters = array(
                    $ivField => null
                );
            }
            $data = $model->query()->params(['limit' => $this->buckedSize])->filters($filters)->fields([$primaryKey]
            )->fetchAll();
        }
        $this->finish();
    }
}