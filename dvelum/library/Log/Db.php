<?php
/**
 * Лог ошибок в базу данных
 * @author Kirill A Egorov 2014
 */
class Log_Db implements Log
{
    /**
     * Имя таблицы в бд
     * @var string
     */
    protected $_table;
    /**
     * Подключение к бд
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;
    /**
     * Имя лога
     * @var string
     */
    protected $_name;

    protected $_logFields = array(
    	'name'=>'name',
        'message'=>'message',
        'date'=>'date'
    );
    protected $_lastError = '';

    public function __construct($logName , Zend_Db_Adapter_Abstract $dbConnection , $tableName)
    {
        $this->_name = $logName;
        $this->_table = $tableName;
        $this->_db = $dbConnection;
    }
    /**
     * (non-PHPdoc)
     * @see Log::log()
     */
	public function log($message)
	{
		try{
			$result = $this->_db->insert(
				$this->_table,
				array(
					$this->_logFields['name'] => $this->_name,
					$this->_logFields['message'] => $message,
					$this->_logFields['date']=> date('Y-m-d H:i:s')
				)
			);

			if(!$result)
				throw new Exception('cannot save log to db ');

			return true;

		}catch (Exception $e){
		    $this->_lastError = $e->getMessage();
			return false;
		}
	}
    /**
     * Установить коннектор подключения к бд
     * @param Zend_Db_Adapter_Abstract $db
     */
	public function setDbConnection(Zend_Db_Adapter_Abstract $db)
	{
		$this->_db = $db;
	}
    /**
     * Получить последнюю внутреннюю ошибку
     * @return string
     */
	public function getLastError()
	{
	   return $this->_lastError;
	}
	/**
	 * Установить таблицу лога
	 * @param string $table
	 */
	public function setTable($table)
	{
		$this->_table = $table;
	}
}