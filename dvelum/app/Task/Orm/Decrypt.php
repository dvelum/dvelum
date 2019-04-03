<?php
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Config;
use Dvelum\Store\Factory;

class Task_Orm_Decrypt extends Bgtask_Abstract
{
    protected $buckedSize = 20;

    /**
     * (non-PHPdoc)
     * @see Bgtask_Abstract::getDescription()
     */
    public function getDescription()
    {
        $lang = Lang::lang();
        return $lang->get('DECRYPT_DATA') . ': ' . $this->_config['object'];
    }

    public function goBackground()
    {
        ini_set('max_execution_time', 0);
        ini_set('ignore_user_abort', 'On');
        session_write_close();

        echo json_encode(array('success' => true));
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
        $object = $this->_config['object'];
        $container = $this->_config['session_container'];

        /*
         * Save task ID into session for UI
         */
        $session = Factory::get(Factory::SESSION);
        $session->set($container , $this->_pid);
        $this->goBackground();

        $objectConfig = Orm\Record\Config::factory($object);
        $ivField = $objectConfig->getIvField();
        $primaryKey = $objectConfig->getPrimaryKey();

        if(!$objectConfig->hasEncrypted())
            $this->finish();

        $filter = array(
            $ivField=> new \Dvelum\Db\Select\Filter($ivField , false , \Dvelum\Db\Select\Filter::NOT_NULL)
        );

        $model = Model::factory($object);
        $count = Model::factory($object)->query()->filters($filter)->getCount();

        $this->setTotalCount($count);

        if(!$count)
            $this->finish();

        $data = $model->getList(array('limit'=>$this->buckedSize) , $filter, array($primaryKey));

        $encryptedFields = $objectConfig->getEncryptedFields();

        while(!empty($data))
        {
            $ids = Utils::fetchCol($primaryKey , $data);

            $objectList = Orm\Record::factory($object , $ids);
            $count = 0;
            foreach($objectList as $dataObject)
            {
                $data = array();
                foreach($encryptedFields as $name){
                    $data[$name] = $dataObject->get($name);
                    $model->logError($dataObject->getId().' '.$name.': '.$data[$name]);
                }
                $data[$ivField] = null;
                try{
                    $model->getDbConnection()->update($model->table() , $data , $primaryKey.' = '.$dataObject->getId());
                    $count ++;
                }catch (Exception $e){
                    $errorText = 'Cannot decrypt '.$dataObject->getName() .' '.$dataObject->getId().' '.$e->getMessage();
                    $model->logError($errorText);
                    $this->error($errorText);
                }
            }
            /*
            * Update task status and check for signals
            */
            $this->incrementCompleted($count);
            $this->updateState();
            $this->processSignals();

            $data = $model->getList(array('limit'=>$this->buckedSize) , $filter , array($primaryKey));
        }
        $this->finish();
    }

}