<?php
/**
 * Простейший механизм локов для предотвращения
 * одновременного запуска нескольких задач планировщиком.
 * При запуске создает файл блокировки, который не позволяет запуститься еще одному процессу
 * блокировка считается валидной до истечения срока [время последнего обновления] + [время отведенное на ожидание до перехвата]
 * @author Kirill Egorov 2013
 */
class Cron_Lock
{
    /**
     * Конфигурация по умолчанию
     * @var array
     */
    protected $config = array(
        'time_limit' => false,
        'intercept_timeout'=>false,
        'locks_dir'=> './'
    );
    /**
     * Время запуска
     * @var integer
     */
    protected $startTime = 0;
    /**
     * Название приложения
     * @var unknown
     */
    protected $task= 'task';
    /**
     * Путь к файлу блокировки
     * @var string
     */
    protected $lockFile = false;
    /**
     * Адаптер логгирования
     * @var Log
     */
    protected $logsAdapter = false;

    /**
     * Приложение запущено
     * @var boolean
     */
    protected $started = false;
    protected $finished = false;

    /**
     * Контструктор, принимает настройки в виде массива имя=>значение
     *    time_limit - ограничение на время выполнения (секунд)
     *    intercept_limit - ожидание до перехвата, после обновления (секунд)
     *    locks_dir - путь к папке с файлами блокировки
     * @param array $config    (time_limit,intercept_limit,locks_dir)
     */
    public function __construct(array $config = array())
    {
        if(!empty($config))
          foreach ($config as $k=>$v)
            $this->config[$k] = $v;
    }
    /**
     * Установить лимит времени на выполнение
     * @param integer $limit
     */
    public function setTimeLimit($limit)
    {
      $this->config['time_limit'] = $limit;
    }
    /**
     * Указать папку для хранения файлов блокировок
     * @param string $path
     * @throws Exception
     */
    public function setLocksDir($path)
    {
      if(!is_dir($path) || !is_writable($path)){
        throw new Exception('Cannot write to locks dir '.$path);
      }
      $this->config['locks_dir'] = $path;
    }
    /**
     * Установить время ожидания до перехвата лока (секунд)
     * интервал ожидания после обновления лок файла, до принятия решения о том, что можно перехватить.
     * @param integer $limit
     */
    public function setInterceptTimeout($limit)
    {
      $this->config['intercept_timeout'] = $limit;
    }

    /**
     * Установить адаптер логов
     * @param Log $log
     */
    public function setLogsAdapter(Log $log)
    {
        $this->logsAdapter = $log;
    }
    /**
     * Запустить задачу.
     * Возвращает true если лок успешно выставлен и можно начинать обработку
     * Возвращает false если уже есть лок файл и его время еще актуально (уже запущен процесс)
     * @param string $taskName - имя задачи (используется для создания лок файла)
     * @return boolean
     */
    public function launch($taskName)
    {
        $this->task = $taskName;
        $this->startTime = time();
        $this->lockFile = $this->config['locks_dir'] . $this->task . '.lock';

        if($this->isLocked()){
            $this->log('Process already started, waiting...');
            return false;
        }

        if(!$this->setLock())
        {
            $this->log('Cannot create lock file, terminating...');
            return false;
        }

        $this->started = true;
        return true;
    }
    /**
     * Проверить наличие блоки
     * @return boolean
     */
    public function isLocked()
    {
        if(!file_exists($this->lockFile))
            return false;

        if(!is_writable($this->lockFile))
            return true;

        $lock = json_decode(@file_get_contents($this->lockFile) , true);

        if(!is_array($lock) || !isset($lock['updated_at']))
            return false;

        if(($lock['updated_at'] + $this->config['intercept_timeout']) > (time()))
            return true;
        else
            return false;
    }
    /**
     * Создать лок для текущей задачи
     * @return boolean
     */
    public function setLock()
    {
        return (boolean) @file_put_contents($this->lockFile, json_encode(array('started_at'=>$this->startTime,'updated_at'=>$this->startTime)));
    }
    /**
     * Обновить данные лока
     * @return boolean
     */
    public function sync()
    {
        if($this->finished)
            return true;

        return (boolean) @file_put_contents($this->lockFile, json_encode(array('started_at'=>$this->startTime,'updated_at'=>time())));
    }
    /**
     * Удалить файл блокировки
     * @return boolean
     */
    public function releaseLock()
    {
        $this->finished = true;
        $success = @unlink($this->lockFile);
        if(!$success)
            $this->log('Cannot release lock' . $this->lockFile);
        return $success;
    }
    /**
     * Получить имя задачи
     * @return string
     */
    public function getTaskName()
    {
      return $this->task . ' ' . date('d.m.Y H:i:s' , $this->startTime);
    }
    /**
     * Отправить сообщение в лог
     * @param string $message
     */
    public function log($message)
    {
        if($this->logsAdapter){
            $this->logsAdapter->log(get_called_class() .' ['.$this->getTaskName() .' ] ' . $message);
        }
    }
    /**
     * Получить лимит на время выполнения задачи
     * @return integer
     */
    public function getTimeLimit()
    {
        return $this->config['time_limit'];
    }
    /**
     * Получить время запуска задачи
     * @return integer
     */
    public function getStartTime()
    {
        return $this->startTime;
    }
    /**
     * Узнать, достигнут ли лимит на время выполнения
     * @return boolean
     */
    public function isTimeLimitReached()
    {
        if($this->config['time_limit'] && ( time() > ($this->startTime + $this->config['time_limit'])))
            return true;

        return false;
    }
    /**
     * Проверить лимит на время выполнения, завершить приложение в случае достижения
     * @param $returnMsg - сообщение на случай выхода
     */
    public function checkTimeLimit($returnMsg = false)
    {
      if($this->isTimeLimitReached()){
        $this->log('Time limit has been reached');
        if($returnMsg)
            $this->log($returnMsg);
        $this->finish();
      }else{
    	$this->sync();
      }
    }
    /**
     * Завершения работы задачи
     */
    public function finish()
    {
        if($this->started)
             $this->releaseLock();
       exit;
    }
}