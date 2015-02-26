<?php
/**
 * Extended Misqli adapter
 * Error handler implementation, performance patch, Db_Select , Light Version
 * @author Kirill A Egorov
 * @package DB
 * @uses Zend_Db_Adapter_Mysqli
 */
class Db_Adapter_Mysqli extends Zend_Db_Adapter_Mysqli
{
	protected $_connectionErrorHandler = null;

	 /**
     * Constructor.
     *
     * $config is an array of key/value pairs
     * containing configuration options.  These options are common to most adapters:
     *
     * dbname         => (string) The name of the database to user
     * username       => (string) Connect to the database as this username.
     * password       => (string) Password associated with the username.
     * host           => (string) What host to connect to, defaults to localhost
     *
     * Some options are used on a case-by-case basis by adapters:
     *
     * port           => (string) The port of the database
     * persistent     => (boolean) Whether to use a persistent connection or not, defaults to false
     * protocol       => (string) The network protocol, defaults to TCPIP
     * caseFolding    => (int) style of case-alteration used for identifiers
     *
     * @param  array $config An array having configuration data
     * @throws Zend_Db_Adapter_Exception
     */
	public function __construct($config)
	{
		/*
		 * Verify that adapter parameters are in an array.
		*/
		if (!is_array($config))
			throw new Zend_Db_Adapter_Exception('Adapter parameters must be in an array or a Zend_Config object');

		$this->_checkRequiredOptions($config);

		$options = array(
				Zend_Db::CASE_FOLDING           => $this->_caseFolding,
				Zend_Db::AUTO_QUOTE_IDENTIFIERS => $this->_autoQuoteIdentifiers,
				Zend_Db::FETCH_MODE             => $this->_fetchMode,
		);

		$driverOptions = array();

		if (isset($config['driver_options']))
		{
			if (!empty($config['driver_options']))
			{
				// can't use array_merge() because keys might be integers
				foreach ((array) $config['driver_options'] as $key => $value)
				{
					$driverOptions[$key] = $value;
				}
			}
		}

		if (!isset($config['charset']))
			$config['charset'] = null;

		if (!isset($config['persistent']))
			$config['persistent'] = false;

		$this->_config = array_merge($this->_config, $config);
		$this->_config['options'] = $options;
		$this->_config['driver_options'] = $driverOptions;
		$this->setProfiler(false);
	}

	public function setConnectionErrorHandler($function)
	{
		if(!is_callable($function))
			throw new Exception('Zend_Db_Adapter_Mysqli_Extended::setConnection passed argument is not callable');
		$this->_connectionErrorHandler = $function;
	}

	protected function _connect()
	{
		if ($this->_connection)
            return;

		try{
			parent::_connect();
		}
		catch (Zend_Db_Adapter_Mysqli_Exception $e)
		{
			if(!is_null($this->_connectionErrorHandler)){
				$f = $this->_connectionErrorHandler;
				$f($e);
			}
			throw $e;
		}
	}

 	/**
 	 * Performance patch
 	 *
     * Quote an identifier.
     *
     * @param  string $value The identifier or expression.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier and alias.
     */
    protected function _quoteIdentifier($value, $auto=false)
    {
        if ($auto === false || $this->_autoQuoteIdentifiers === true) {
            return ('`' . str_replace('`', '', $value) . '`');
        }
        return $value;
    }

	/**
	 * Performance patch
	 *
     * Quote an identifier and an optional alias.
     *
     * @param string|array|Zend_Db_Expr $ident The identifier or expression.
     * @param string $alias An optional alias.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @param string $as The string to add between the identifier/expression and the alias.
     * @return string The quoted identifier and alias.
     */
    protected function _quoteIdentifierAs($ident, $alias = null, $auto = false, $as = ' AS ')
    {
        if($ident instanceof Zend_Db_Expr)
        {
            $quoted = $ident->__toString();
        }
        elseif($ident instanceof Db_Select)
        {
            $quoted = '(' . $ident->assemble() . ')';
        }
        elseif(is_string($ident))
        {
        	$quoted = '`'.str_replace(array('`','.'), array('','`.`'), $ident).'`';
        }
        elseif(is_array($ident))
        {
           $segments = array();
           foreach ($ident as $segment)
           {
              if($segment instanceof Zend_Db_Expr)
              {
                 $segments[] = $segment->__toString();
              }
              else
              {
                 $segments[] = $this->_quoteIdentifier($segment, $auto);
              }
            }

            if($alias !== null && end($ident) == $alias)
            {
               $alias = null;
            }

            $quoted = implode('.', $segments);
        }
        else
        {
                $quoted = $this->_quoteIdentifier($ident, $auto);
        }


        if($alias !== null)
        {
            $quoted.= $as . $this->_quoteIdentifier($alias, $auto);
        }

        return $quoted;
    }
    /**
     * @return Db_Select
     */
    public function select()
    {
    	return new Db_Select();
    }

   /**
    * (non-PHPdoc)
    * @see Zend_Db_Adapter_Abstract::_checkRequiredOptions()
    */
    protected function _checkRequiredOptions(array $config)
    {
    	if (!isset($config['dbname']))
    		throw new Zend_Db_Adapter_Exception("Configuration array must have a key for 'dbname' that names the database instance");

    	if (!isset($config['password']))
    		throw new Zend_Db_Adapter_Exception("Configuration array must have a key for 'password' for login credentials");

    	if (!isset($config['username']))
    		throw new Zend_Db_Adapter_Exception("Configuration array must have a key for 'username' for login credentials");
    }
    /**
     * Disable special profilers
     * (non-PHPdoc)
     * @see Zend_Db_Adapter_Abstract::setProfiler()
     */
    public function setProfiler($profiler)
    {
        $this->_profiler = new $this->_defaultProfilerClass();
        $this->_profiler->setEnabled(false);
        return $this->_profiler;
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Db_Adapter_Abstract::fetchAll()
     * @todo fix, may cause losing data type
     *
    public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
    	if ($fetchMode === null) {
    		$fetchMode = $this->_fetchMode;
    	}

    	$fastModes = array(Zend_Db::FETCH_ASSOC);

    	// fast query hack
    	if(empty($bind) && in_array($this->_fetchMode, $fastModes , true))
    	{
    	    $mysqli = $this->getConnection();

    	    if(!$result = $mysqli->query($sql)){
    	    	throw new Exception($mysqli->error);
    	    }

            switch ($this->_fetchMode)
            {
            	case Zend_Db::FETCH_ASSOC :
                	   $data = $result->fetch_all(MYSQLI_ASSOC);
            	       break;

                default: throw new Exception('Db_Adapter_Mysqli::fetchAll undefined fetch mode '.$this->_fetchMode);
            }
            // free result set
            $result->free();
            return $data;
    	}
    	else
    	{
    	    $stmt = $this->query($sql, $bind);
    	    $result = $stmt->fetchAll($fetchMode);
    	}
    }
    */
}