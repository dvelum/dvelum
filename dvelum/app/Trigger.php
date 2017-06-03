<?php
use Dvelum\Config;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\App\Session\User;

/**
 * Default Trigger
 * Handle Db_Object Events
 */
class Trigger
{
    /**
     * @var Config\ConfigInterface $ormConfig
     */
    protected $ormConfig;

    /**
     * @var Config\ConfigInterface $appConfig
     */
    protected $appConfig;

    public function __construct()
    {
        $this->appConfig = Config::storage()->get('main.php');
        $this->ormConfig = Config::storage()->get('orm.php');
    }

    /**
	 * @var Cache_Abstract | false
	 */
	protected $_cache = false;

	public function setCache(Cache_Abstract $cache)
	{
		$this->_cache = $cache;
	}

	protected function _getItemCacheKey(Orm\ObjectInterface $object)
	{
		$objectModel = \Model::factory($object->getName());
		return $objectModel->getCacheKey(array('item',$object->getId()));
	}

	public function onBeforeAdd(Orm\ObjectInterface $object){}

	public function onBeforeUpdate(Orm\ObjectInterface $object){}

	public function onBeforeDelete(Orm\ObjectInterface $object){}

	public function onAfterAdd(Orm\ObjectInterface $object)
	{
        $config = $object->getConfig();
        $logObject = $this->ormConfig->get('history_object');

        if($config->hasHistory())
        {
            if($config->hasExtendedHistory()){
                Model::factory($logObject)->saveState(
                    Model_Historylog::Create,
                    $object->getName() ,
                    $object->getId() ,
                    User::getInstance()->id,
                    date('Y-m-d H:i:s'),
                    null ,
                    json_encode($object->getData())
                );
            }else{
                Model::factory($logObject)->log(
                    User::getInstance()->id,
                    $object->getId() ,
                    Model_Historylog::Create,
                    $object->getName()
                );
            }
        }

		if(!$this->_cache)
			return;

		$this->_cache->remove($this->_getItemCacheKey($object));
	}

	public function onAfterUpdate(Orm\ObjectInterface$object)
	{
		if(!$this->_cache)
			return;

		$this->_cache->remove($this->_getItemCacheKey($object));
	}

	public function onAfterDelete(Orm\ObjectInterface $object)
	{
        $config = $object->getConfig();
        $logObject = $this->ormConfig->get('history_object');

        if($object->getConfig()->hasHistory())
        {
            if($config->hasExtendedHistory()){
                Model::factory($logObject)->saveState(
                    Model_Historylog::Delete,
                    $object->getName() ,
                    $object->getId() ,
                    User::getInstance()->id,
                    date('Y-m-d H:i:s'),
                    json_encode($object->getData()),
                    null
                );
            }else{
                Model::factory($logObject)->log(
                    User::getInstance()->id,
                    $object->getId() ,
                    Model_Historylog::Delete,
                    $object->getName()
                );
            }
        }

		if(!$this->_cache)
			return;

		$this->_cache->remove($this->_getItemCacheKey($object));
	}

	public function onAfterUpdateBeforeCommit(Orm\ObjectInterface $object)
	{
        $config = $object->getConfig();
        $logObject = $this->ormConfig->get('history_object');

        if($object->getConfig()->hasHistory() && $object->hasUpdates())
        {
            $before = $object->getData(false);
            $after = $object->getUpdates();

            foreach($before as $field=>$value)
            {
                if(!array_key_exists($field ,$after)){
                    unset($before[$field]);
                }
            }

            if($config->hasExtendedHistory()){
                Model::factory($logObject)->saveState(
                    Model_Historylog::Update,
                    $object->getName() ,
                    $object->getId() ,
                    User::getInstance()->id,
                    date('Y-m-d H:i:s'),
                    json_encode($before),
                    json_encode($after)
                );
            }else{
                Model::factory($logObject)->log(
                    User::getInstance()->id,
                    $object->getId() ,
                    Model_Historylog::Update,
                    $object->getName()
                );
            }
        }
	}

    public function onAfterPublish(Orm\ObjectInterface $object)
    {
        $config = $object->getConfig();
        $logObject = $this->ormConfig->get('history_object');

        if($object->getConfig()->hasHistory())
        {
                Model::factory($logObject)->log(
                    User::getInstance()->id,
                    $object->getId() ,
                    Model_Historylog::Publish,
                    $object->getName()
                );
        }
    }

    public function  onAfterUnpublish(Orm\ObjectInterface $object)
    {
        if(!$object->getConfig()->hasHistory()) {
            return;
        }

        $config = $object->getConfig();
        $logObject = $this->ormConfig->get('history_object');

        Model::factory($logObject)->log(
            User::getInstance()->getId(),
            $object->getId() ,
            Model_Historylog::Unpublish,
            $object->getName()
        );
    }

    public function onAfterAddVersion(Orm\ObjectInterface $object)
    {
        if(!$object->getConfig()->hasHistory()) {
            return;
        }

        $config = $object->getConfig();
        $logObject = $this->ormConfig->get('history_object');

        Model::factory($logObject)->log(
            User::getInstance()->getId() ,
            $object->getId() ,
            Model_Historylog::NewVersion ,
            $object->getName()
        );

    }

    public function onAfterInsertBeforeCommit(Orm\ObjectInterface $object){}

    public function onAfterDeleteBeforeCommit(Orm\ObjectInterface $object){}
}